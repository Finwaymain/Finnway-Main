<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DriversDocuments;
use App\Models\Driver;
use App\Models\VehicleType;
use App\Models\UserCategory;
use App\Models\CarModel;
use App\Helpers\Helper;

class OnboardingController extends Controller
{
    public function getData(Request $request)
    {
        $type = $request->input('type') ?: $request->query('type');
        if (!empty($type)) {
            $query = DB::table('on_boardings');
            if ($type) {
                $query->where('type', $type);
            }
            $items = $query->get();

            $data = $items->map(function ($item) {
                $img = $item->image ?? '';
                if (!empty($img) && !str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
                    $img = asset('assets/images/onboarding/' . $img);
                }
                return [
                    'id' => (int) $item->id,
                    'type' => $item->type ?? '',
                    'title' => $item->title ?? '',
                    'description' => $item->description ?? '',
                    'image' => $img,
                ];
            });

            return response()->json([
                'success' => 'success',
                'error' => null,
                'message' => 'Successfully',
                'data' => $data,
            ]);
        }

        return $this->init($request);
    }

    public function init(Request $request)
    {
        try {
            $accessToken = $request->header('accesstoken');
            $driverId = $request->input('driver_id') ?: $request->query('driver_id');
            $onboardingCompleted = false;

            if ($accessToken && Schema::hasTable('users_access')) {
                $userAccess = DB::table('users_access')
                    ->where('accesstoken', $accessToken)
                    ->first();
                if ($userAccess) {
                    $driverId = $userAccess->user_id;
                }
            }

            if ($driverId && Schema::hasTable('tj_conducteur_categories')) {
                $onboardingCompleted = DB::table('tj_conducteur_categories')
                    ->where('driver_id', $driverId)
                    ->exists();
            }

            // 1. Provider signup categories only (exclude consumer_service catalog rows)
            $categories = UserCategory::query()
                ->whereNull('parent_id')
                ->where(function ($q) {
                    $q->whereNull('type')
                        ->orWhere('type', '!=', 'consumer_service');
                })
                ->with(['subcategories' => function ($q) {
                    $q->where(function ($inner) {
                        $inner->whereNull('type')
                            ->orWhere('type', '!=', 'consumer_service');
                    });
                }])
                ->get();

            // 2. Vehicle Mapping
            $vehicleMappings = collect();
            if (Schema::hasTable('tj_category_user_vehicle_type') && Schema::hasTable('tj_type_vehicule')) {
                $vehicleMappings = DB::table('tj_category_user_vehicle_type')
                    ->join('tj_type_vehicule', 'tj_category_user_vehicle_type.vehicle_type_id', '=', 'tj_type_vehicule.id')
                    ->select('category_user_id', 'tj_type_vehicule.id as vehicle_type_id', 'tj_type_vehicule.libelle')
                    ->get()
                    ->groupBy('category_user_id');
            }

            $subcategoriesRequiringVehicle = $vehicleMappings->keys()->map(fn($id) => (int)$id)->toArray();

            $enrichedCategories = $categories->map(function ($parent) use ($subcategoriesRequiringVehicle) {
                $enrichedSubs = $parent->subcategories->map(function ($sub) use ($subcategoriesRequiringVehicle, $parent) {
                    $requiresVehicle = in_array((int)$sub->id, $subcategoriesRequiringVehicle);

                    return array_merge($sub->toArray(), [
                        'requires_vehicle' => $requiresVehicle,
                        'requires_home_visit' => self::subcategoryRequiresHomeVisit($parent, $requiresVehicle),
                    ]);
                });
                $parentRequiresVehicle = $enrichedSubs->contains('requires_vehicle', true);
                return array_merge($parent->toArray(), [
                    'requires_vehicle' => $parentRequiresVehicle,
                    'subcategories' => $enrichedSubs->values()
                ]);
            });

            // 3. Vehicles by Type
            $vehicleTypes = collect();
            if (Schema::hasTable('tj_type_vehicule')) {
                $vehicleTypes = DB::table('tj_type_vehicule')
                    ->where('status', 'Yes')
                    ->select('id', 'libelle', 'image')
                    ->get();
            }

            $modelsByType = collect();
            if (Schema::hasTable('car_model') && Schema::hasTable('brands')) {
                $modelsByType = DB::table('car_model')
                    ->join('brands', 'car_model.brand_id', '=', 'brands.id')
                    ->select('car_model.vehicle_type_id', 'brands.name as brand', 'car_model.name as model')
                    ->get()
                    ->groupBy('vehicle_type_id');
            }

            $structuredVehicles = [];
            $seenNames = [];
            foreach ($vehicleTypes as $type) {
                $key = strtolower(trim($type->libelle));
                if ($key === '' || isset($seenNames[$key])) {
                    continue;
                }
                $seenNames[$key] = true;

                $brands = [];
                foreach ($modelsByType->get($type->id, []) as $row) {
                    $brands[$row->brand][] = $row->model;
                }
                $structuredVehicles[] = [
                    'id' => $type->id,
                    'name' => $type->libelle,
                    'image' => $type->image,
                    'brands' => $brands,
                ];
            }

            // 4. Secondary Services Mapping
            $transportDeliveryMap = [
                'Cab Driver' => ['Parcel Delivery', 'Pickup & Drop (Personal runner)'],
                'Bike Rider' => ['Food Delivery', 'Parcel Delivery', 'Pickup & Drop (Personal runner)'],
                'Auto Driver' => ['Parcel Delivery', 'Pickup & Drop (Personal runner)'],
                'E-Rickshaw' => ['Parcel Delivery', 'Pickup & Drop (Personal runner)'],
                'Pickup' => ['Parcel Delivery', 'Logistics Partner'],
                'Fleet Owner' => ['Food Delivery', 'Parcel Delivery', 'Pickup & Drop (Personal runner)', 'Logistics Partner', 'Packers & Movers'],
                'Truck Owner' => ['Logistics Partner', 'Packers & Movers'],
            ];

            // 5. Admin Documents
            $adminDocs = collect();
            if (Schema::hasTable('admin_documents')) {
                $adminDocs = DB::table('admin_documents')
                    ->where('is_enabled', 'Yes')
                    ->where('id', '!=', 6)
                    ->orderBy('id')
                    ->get(['id', 'title'])
                    ->map(function ($doc) {
                        $title = strtolower($doc->title);
                        $isVehicleOnly = (
                            str_contains($title, 'licence') ||
                            str_contains($title, 'license') ||
                            str_contains($title, 'driving') ||
                            str_contains($title, 'rc') ||
                            str_contains($title, 'insurance') ||
                            str_contains($title, 'pollution') ||
                            str_contains($title, 'vehicle')
                        );
                        return array_merge((array)$doc, [
                            'requires_vehicle_only' => $isVehicleOnly
                        ]);
                    });
            }

            // 6. Active Zones
            $zones = collect();
            if (Schema::hasTable('zones')) {
                $zones = DB::table('zones')
                    ->where('status', 'yes')
                    ->orderBy('id')
                    ->get(['id', 'name']);
            }

            $servicePricing = null;
            $selectedSkills = [];
            $existingSelection = null;
            if ($driverId) {
                $existingSelection = $this->buildExistingDriverSelection((int) $driverId);
            }

            $pricingCategoryId = $existingSelection['business_subcategory_id'] ?? null;
            if ($driverId && Schema::hasTable('driver_service_pricing')) {
                $pricingQuery = DB::table('driver_service_pricing')
                    ->where('driver_id', $driverId);

                if ($pricingCategoryId) {
                    $pricingQuery->where('category_id', $pricingCategoryId);
                }

                $pricingRow = $pricingQuery->orderByDesc('updated_at')->first();

                if ($pricingRow) {
                    $items = [];
                    if (Schema::hasTable('driver_service_items')) {
                        $items = DB::table('driver_service_items')
                            ->where('driver_id', $driverId)
                            ->where('category_id', $pricingRow->category_id)
                            ->orderBy('sort_order')
                            ->get(['service_name', 'price'])
                            ->map(fn($row) => [
                                'name' => $row->service_name,
                                'price' => (string) $row->price,
                            ])
                            ->values()
                            ->toArray();
                    }

                    $servicePricing = [
                        'category_id' => (int) $pricingRow->category_id,
                        'visiting_charge' => (string) $pricingRow->visiting_charge,
                        'service_items' => $items,
                    ];
                }
            }

            if ($driverId && Schema::hasTable('driver_service_skills')) {
                $selectedSkills = DB::table('driver_service_skills')
                    ->where('driver_id', $driverId)
                    ->pluck('skill_id')
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->toArray();
            }

            $adminCommission = null;
            if (Schema::hasTable('tj_commission')) {
                $commissionRow = DB::table('tj_commission')->where('statut', 'yes')->first();
                if ($commissionRow) {
                    $adminCommission = [
                        'value' => $commissionRow->value,
                        'type' => $commissionRow->type,
                    ];
                }
            }

            $mode = $request->input('mode') ?: $request->query('mode');
            $allowEdit = in_array($mode, ['edit_profile', 'edit_category'], true)
                || (string) $request->input('edit') === '1'
                || (string) $request->query('edit') === '1';

            return response()->json([
                'success' => 'success',
                'data' => [
                    'onboarding_completed' => $onboardingCompleted,
                    'allow_edit' => $allowEdit,
                    'categories' => $enrichedCategories,
                    'vehicle_mappings' => $vehicleMappings,
                    'vehicles' => array_values($structuredVehicles),
                    'transport_delivery_map' => $transportDeliveryMap,
                    'admin_docs' => $adminDocs,
                    'zones' => $zones,
                    'service_pricing' => $servicePricing,
                    'home_service_catalog' => $this->buildHomeServiceSkillsCatalog(),
                    'home_service_groups' => $this->buildHomeServiceProfessionGroups(),
                    'selected_skills' => $selectedSkills,
                    'admin_commission' => $adminCommission,
                    'existing_selection' => $existingSelection,
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('Onboarding init failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => 'success',
                'data' => [
                    'onboarding_completed' => false,
                    'categories' => [],
                    'vehicle_mappings' => [],
                    'vehicles' => [],
                    'transport_delivery_map' => [],
                    'admin_docs' => [],
                    'zones' => []
                ]
            ]);
        }
    }

    public function submit(Request $request)
    {
        try {
            $driverId = $request->input('driver_id');
            $primaryCategoryId = $request->input('primary_category_id');
            $secondaryTypes = json_decode($request->input('secondary_types', '[]'), true);
            $vehicles = json_decode($request->input('vehicles', '[]'), true);
            
            if (empty($driverId) || $driverId == 0) {
                return response()->json(['success' => 'Failed', 'error' => 'driver_id is required']);
            }

            $primaryCategory = UserCategory::find($primaryCategoryId);
            if (!$primaryCategory) {
                return response()->json(['success' => 'Failed', 'error' => 'Invalid primary category selected']);
            }

            $mode = $request->input('mode');

            $topLevelCategory = $primaryCategory;
            $depth = 0;
            while ($topLevelCategory && $topLevelCategory->parent_id && $depth < 5) {
                $topLevelCategory = UserCategory::find($topLevelCategory->parent_id);
                $depth++;
            }
            $requiresManualApproval = !self::isHomeServicesProviderCategory($topLevelCategory);

            $bankName = $request->input('bank_name');
            $accountNo = $request->input('account_no');
            $ifscCode = $request->input('ifsc_code');

            if (!empty($bankName) || !empty($accountNo) || !empty($ifscCode)) {
                $driverRow = DB::table('tj_conducteur')->where('id', $driverId)->first();
                $driverPhone = $driverRow ? ($driverRow->phone ?? '') : null;
                $valRes = \App\Helpers\BankValidationHelper::validateBankDetails(
                    $bankName,
                    $accountNo,
                    $ifscCode,
                    $driverPhone,
                    $driverId,
                    'driver'
                );
                if (!$valRes['valid']) {
                    return response()->json([
                        'success' => 'Failed',
                        'error'   => $valRes['error']
                    ]);
                }
            }

            if ($mode === 'edit_profile') {
                $driverUpdateData = [
                    'bank_name' => trim((string)$bankName),
                    'account_no' => trim((string)$accountNo),
                    'ifsc_code' => strtoupper(trim((string)$ifscCode)),
                    'zone_id' => $request->input('zone_id'),
                ];

                if (Schema::hasColumn('tj_conducteur', 'business_name')) {
                    $hasRegisteredShop = strtolower(trim((string) $request->input('has_registered_shop', '')));
                    if ($hasRegisteredShop === 'yes') {
                        $driverUpdateData['business_name'] = 'Registered Shop';
                    } elseif ($request->input('service_declaration_accepted') === '1') {
                        $driverUpdateData['business_name'] = 'Home Service Provider';
                    }
                }

                DB::table('tj_conducteur')->where('id', $driverId)->update($driverUpdateData);
            } elseif (!in_array($mode, ['edit_category', 'edit_profile'], true)) {
                $driverUpdateData = [
                    'is_verified' => $requiresManualApproval ? 0 : 1,
                    'statut' => $requiresManualApproval ? 'no' : 'yes',
                    'bank_name' => trim((string)$bankName),
                    'account_no' => trim((string)$accountNo),
                    'ifsc_code' => strtoupper(trim((string)$ifscCode)),
                    'zone_id' => $request->input('zone_id')
                ];

                if (Schema::hasColumn('tj_conducteur', 'business_name')) {
                    $hasRegisteredShop = strtolower(trim((string) $request->input('has_registered_shop', '')));
                    if ($hasRegisteredShop === 'yes') {
                        $driverUpdateData['business_name'] = 'Registered Shop';
                    } elseif ($request->input('service_declaration_accepted') === '1') {
                        $driverUpdateData['business_name'] = 'Home Service Provider';
                    }
                }

                DB::table('tj_conducteur')->where('id', $driverId)->update($driverUpdateData);
            }

            if (!in_array($mode, ['edit_category', 'edit_profile'], true)) {
                $isFleet = $primaryCategory->libelle === 'Fleet Owner';
                
                $isBike = false;
                if (stripos($primaryCategory->libelle, 'bike') !== false || stripos($primaryCategory->libelle, 'motorcycle') !== false || stripos($primaryCategory->libelle, 'scooter') !== false || stripos($primaryCategory->libelle, '2-wheeler') !== false) {
                    $isBike = true;
                }

                if (empty($vehicles)) {
                } else if ($isFleet) {
                    foreach ($vehicles as $veh) {
                        $vehIsBike = $isBike || (isset($veh['brand']) && (stripos($veh['brand'], 'bike') !== false || stripos($veh['brand'], 'scooter') !== false));
                        $passengerVal = $veh['passenger'] ?? ($vehIsBike ? '1' : '4');
                        if ($vehIsBike && (int)$passengerVal > 2) {
                            $passengerVal = '1';
                        }
                        DB::table('tj_vehicule')->insert([
                            'brand' => $veh['brand'],
                            'model' => $veh['model'],
                            'numberplate' => $veh['number_plate'],
                            'id_type_vehicule' => $veh['type_id'],
                            'id_conducteur' => $driverId,
                            'car_make' => $veh['car_make'] ?? $veh['brand'],
                            'milage' => $veh['milage'] ?? '0',
                            'km' => $veh['km'] ?? '0',
                            'color' => $veh['color'] ?? 'N/A',
                            'passenger' => $passengerVal,
                            'statut' => 'yes',
                            'creer' => now(),
                            'updated_at' => now()
                        ]);
                    }
                } else {
                    $firstVeh = $vehicles[0];
                    $vehIsBike = $isBike || (isset($firstVeh['brand']) && (stripos($firstVeh['brand'], 'bike') !== false || stripos($firstVeh['brand'], 'scooter') !== false));
                    $passengerVal = $firstVeh['passenger'] ?? ($vehIsBike ? '1' : '4');
                    if ($vehIsBike && (int)$passengerVal > 2) {
                        $passengerVal = '1';
                    }
                    DB::table('tj_vehicule')->insert([
                        'brand' => $firstVeh['brand'],
                        'model' => $firstVeh['model'],
                        'numberplate' => $firstVeh['number_plate'],
                        'id_type_vehicule' => $firstVeh['type_id'] ?? null,
                        'id_conducteur' => $driverId,
                        'car_make' => $firstVeh['car_make'] ?? $firstVeh['brand'],
                        'milage' => $firstVeh['milage'] ?? '0',
                        'km' => $firstVeh['km'] ?? '0',
                        'color' => $firstVeh['color'] ?? 'N/A',
                        'passenger' => $passengerVal,
                        'statut' => 'yes',
                        'creer' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::table('tj_conducteur_categories')->where('driver_id', $driverId)->delete();
            
            $sellerCategory = DB::table('tj_categorie_user')->where('libelle', 'Online Seller')->first();
            if ($sellerCategory) {
                DB::table('tj_conducteur_categories')->insert([
                    'driver_id' => $driverId,
                    'category_id' => $sellerCategory->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::table('tj_conducteur_categories')->insert([
                'driver_id' => $driverId,
                'category_id' => $primaryCategoryId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (!empty($secondaryTypes)) {
                $secondaryIds = DB::table('tj_categorie_user')->whereIn('libelle', $secondaryTypes)->pluck('id');
                foreach ($secondaryIds as $sId) {
                    DB::table('tj_conducteur_categories')->insert([
                        'driver_id' => $driverId,
                        'category_id' => $sId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            if (self::categoryRequiresHomeVisitPricing($primaryCategory)) {
                $visitingCharge = $request->input('visiting_charge');
                $selectedSkills = json_decode($request->input('selected_skills', '[]'), true);
                if (!is_array($selectedSkills) || count($selectedSkills) === 0) {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'Please select at least one home service skill',
                    ]);
                }

                $subLabel = $request->input('business_subcategory_label', '');
                $serviceItems = json_decode($request->input('service_items', '[]'), true);
                $hasInlinePrices = is_array($serviceItems) && collect($serviceItems)->contains(function ($item) {
                    $name = trim((string) ($item['name'] ?? $item['service_name'] ?? ''));
                    $price = $item['price'] ?? null;
                    return $name !== '' && $price !== null && $price !== '';
                });

                if (self::usesInlineSkillPricing($subLabel)) {
                    if (!$hasInlinePrices) {
                        return response()->json([
                            'success' => 'Failed',
                            'error' => 'Please add a price for each selected service',
                        ]);
                    }
                } elseif (!self::isHealthcarePackagePricingFlow($subLabel, $selectedSkills) && ($visitingCharge === null || $visitingCharge === '')) {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'Visiting charge is required for home-visit service providers',
                    ]);
                }
            }

            $this->saveDriverServicePricing($driverId, (int) $primaryCategoryId, $primaryCategory, $request);
            if (self::categoryRequiresHomeVisitPricing($primaryCategory)) {
                $this->saveDriverServiceSkills($driverId, (int) $primaryCategoryId, $request);
            }

            if ($mode !== 'edit_category') {
                $targetDir = public_path('assets/images/driver/documents');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }

                $adminDocs = DB::table('admin_documents')->where('is_enabled', 'Yes')->get();
                foreach ($adminDocs as $doc) {
                    $inputName = 'doc_' . $doc->id;
                    
                    if ($request->hasFile($inputName)) {
                        $this->storeDriverDocumentUpload($request->file($inputName), $driverId, (int) $doc->id, $doc->title);
                    }
                }

                $this->saveHomeProviderDocuments($request, $driverId, $topLevelCategory);
            }

            return response()->json([
                'success' => 'success',
                'message' => 'Onboarding data submitted successfully.',
                'error' => null
            ]);
        } catch (\Throwable $e) {
            \Log::error('Onboarding submit error: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => $e->getMessage(),
                'message' => 'An error occurred during onboarding submission.'
            ]);
        }
    }

    private function uploadToImageKit($file, $folder = '/driver/documents')
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'doc_' . time() . '_' . uniqid() . '.' . $extension;

        $privateKey = config('imagekit.private_key');
        if (empty($privateKey)) {
            throw new \Exception('IMAGEKIT_PRIVATE_KEY is not configured on the server.');
        }

        $url = "https://upload.imagekit.io/api/v1/files/upload";

        $postData = [
            'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $filename),
            'fileName' => $filename,
            'folder' => $folder,
            'useUniqueFileName' => 'true'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 200) {
            $json = json_decode($response, true);
            if (isset($json['url'])) {
                return $json['url'];
            }
        }

        throw new \Exception("ImageKit error ({$statusCode})");
    }

    private static function subcategoryRequiresHomeVisit($parent, bool $requiresVehicle): bool
    {
        if ($requiresVehicle) {
            return false;
        }

        return self::isHomeServicesProviderCategory($parent);
    }

    private static function isHomeServicesProviderCategory($category): bool
    {
        if (!$category) {
            return false;
        }

        $label = is_array($category) ? ($category['libelle'] ?? '') : ($category->libelle ?? '');
        $normalized = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $label)));

        return str_contains($normalized, 'home services');
    }

    private static function categoryRequiresHomeVisitPricing(?UserCategory $category): bool
    {
        if (!$category) {
            return false;
        }

        $topLevel = $category;
        $depth = 0;
        while ($topLevel && $topLevel->parent_id && $depth < 5) {
            $topLevel = UserCategory::find($topLevel->parent_id);
            $depth++;
        }

        return self::isHomeServicesProviderCategory($topLevel);
    }

    private function buildHomeServiceProfessionGroups(): array
    {
        $path = database_path('data/home_services_professions.php');

        return is_file($path) ? require $path : [];
    }

    private function buildHomeServiceSkillsCatalog(): array
    {
        if (!Schema::hasTable('tj_categorie_user')) {
            return [];
        }

        $roots = DB::table('tj_categorie_user')
            ->where('type', 'consumer_service')
            ->whereNull('parent_id')
            ->where('libelle', '!=', 'Home Services')
            ->orderBy('libelle')
            ->get(['id', 'libelle', 'image']);

        return $roots->map(function ($root) {
            $children = $this->loadSkillNodes((int) $root->id);

            return [
                'id' => (int) $root->id,
                'libelle' => $root->libelle,
                'image' => $root->image,
                'children' => $children,
                'has_children' => count($children) > 0,
            ];
        })->values()->toArray();
    }

    private function loadSkillNodes(int $parentId): array
    {
        $nodes = DB::table('tj_categorie_user')
            ->where('parent_id', $parentId)
            ->where('type', 'consumer_service')
            ->where(function ($q) {
                if (Schema::hasColumn('tj_categorie_user', 'statut')) {
                    $q->where('statut', true)->orWhereNull('statut');
                }
            })
            ->orderBy('libelle')
            ->get(['id', 'libelle', 'image']);

        return $nodes->map(function ($node) {
            $children = $this->loadSkillNodes((int) $node->id);

            return [
                'id' => (int) $node->id,
                'libelle' => $node->libelle,
                'image' => $node->image,
                'children' => $children,
                'has_children' => count($children) > 0,
            ];
        })->values()->toArray();
    }

    private function saveDriverServiceSkills(int $driverId, int $providerCategoryId, Request $request): void
    {
        if (!Schema::hasTable('driver_service_skills')) {
            return;
        }

        $raw = json_decode($request->input('selected_skills', '[]'), true);
        if (!is_array($raw)) {
            $raw = [];
        }

        $skillIds = collect($raw)
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        DB::table('driver_service_skills')
            ->where('driver_id', $driverId)
            ->where('provider_category_id', $providerCategoryId)
            ->delete();

        foreach ($skillIds as $skillId) {
            $exists = DB::table('tj_categorie_user')
                ->where('id', $skillId)
                ->where('type', 'consumer_service')
                ->exists();

            if (!$exists) {
                continue;
            }

            DB::table('driver_service_skills')->insert([
                'driver_id' => $driverId,
                'provider_category_id' => $providerCategoryId,
                'skill_id' => $skillId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private static function usesInlineSkillPricing(string $subLabel): bool
    {
        $normalized = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $subLabel)));
        $inlineRoles = [
            'lab technician',
            'nurse',
            'nursing care',
            'home tutor',
            'music teacher',
            'dance teacher',
            'yoga trainer',
            'gym trainer',
            'language tutor',
        ];

        foreach ($inlineRoles as $role) {
            if ($normalized === $role || str_contains($normalized, $role)) {
                return true;
            }
        }

        return false;
    }

    private static function isHealthcarePackagePricingFlow(string $subLabel, array $selectedSkillIds): bool
    {
        $normalized = strtolower(trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $subLabel)));
        $healthcareRoles = ['doctor home visit', 'physiotherapist'];
        foreach ($healthcareRoles as $role) {
            if ($normalized === $role || str_contains($normalized, $role)) {
                return true;
            }
        }

        if (empty($selectedSkillIds) || !Schema::hasTable('tj_categorie_user')) {
            return false;
        }

        $labels = DB::table('tj_categorie_user')
            ->whereIn('id', $selectedSkillIds)
            ->pluck('libelle')
            ->map(fn($l) => strtolower(trim((string) $l)))
            ->toArray();

        foreach ($labels as $label) {
            if (str_contains($label, 'package') || str_contains($label, 'nursing') || str_contains($label, 'physio')) {
                return true;
            }
        }

        return false;
    }

    private function saveDriverServicePricing(int $driverId, int $categoryId, UserCategory $category, Request $request): void
    {
        if (!self::categoryRequiresHomeVisitPricing($category)) {
            return;
        }

        if (!Schema::hasTable('driver_service_pricing')) {
            return;
        }

        $visitingCharge = $request->input('visiting_charge');
        $serviceItems = json_decode($request->input('service_items', '[]'), true);
        if (!is_array($serviceItems)) {
            $serviceItems = [];
        }

        $hasItems = collect($serviceItems)->contains(function ($item) {
            $name = trim((string) ($item['name'] ?? $item['service_name'] ?? ''));
            $price = $item['price'] ?? null;
            return $name !== '' && $price !== null && $price !== '';
        });

        if (($visitingCharge === null || $visitingCharge === '') && !$hasItems) {
            return;
        }

        if ($visitingCharge === null || $visitingCharge === '') {
            $visitingCharge = 0;
        }

        $existing = DB::table('driver_service_pricing')
            ->where('driver_id', $driverId)
            ->where('category_id', $categoryId)
            ->first();

        if ($existing) {
            DB::table('driver_service_pricing')
                ->where('id', $existing->id)
                ->update([
                    'visiting_charge' => (float) $visitingCharge,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('driver_service_pricing')->insert([
                'driver_id' => $driverId,
                'category_id' => $categoryId,
                'visiting_charge' => (float) $visitingCharge,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('driver_service_items')) {
            return;
        }

        DB::table('driver_service_items')
            ->where('driver_id', $driverId)
            ->where('category_id', $categoryId)
            ->delete();

        $order = 0;
        foreach ($serviceItems as $item) {
            $name = trim((string) ($item['name'] ?? $item['service_name'] ?? ''));
            $price = $item['price'] ?? null;
            if ($name === '' || $price === null || $price === '') {
                continue;
            }

            DB::table('driver_service_items')->insert([
                'driver_id' => $driverId,
                'category_id' => $categoryId,
                'service_name' => $name,
                'price' => (float) $price,
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function isTransportOrDeliveryCategory(?UserCategory $topLevelCategory): bool
    {
        return !self::isHomeServicesProviderCategory($topLevelCategory);
    }

    private function saveHomeProviderDocuments(Request $request, int $driverId, ?UserCategory $topLevelCategory): void
    {
        if ($this->isTransportOrDeliveryCategory($topLevelCategory)) {
            return;
        }

        $map = [
            'home_selfie' => 'Profile Photo',
            'home_aadhaar_front' => 'Aadhaar Card Front',
            'home_aadhaar_back' => 'Aadhaar Card Back',
            'home_shop_photo' => 'Shop Photo',
        ];

        foreach ($map as $inputName => $title) {
            if ($request->hasFile($inputName)) {
                $documentId = $this->resolveAdminDocumentId($title);
                if ($documentId) {
                    $this->storeDriverDocumentUpload($request->file($inputName), $driverId, $documentId, $title);
                }
            }
        }
    }

  /**
   * Resolve admin_documents.id for a title without relying on broken AUTO_INCREMENT.
   */
    private function resolveAdminDocumentId(string $title): ?int
    {
        if (!Schema::hasTable('admin_documents')) {
            return null;
        }

        $title = trim($title);
        if ($title === '') {
            return null;
        }

        $normalized = strtolower($title);
        $aliasMap = [
            'profile photo' => ['profile photo', 'selfie', 'driver photo', 'photo'],
            'aadhaar card front' => ['aadhaar card front', 'aadhar card front', 'aadhaar front', 'aadhar front'],
            'aadhaar card back' => ['aadhaar card back', 'aadhar card back', 'aadhaar back', 'aadhar back'],
            'shop photo' => ['shop photo', 'registered shop photo', 'business shop photo'],
        ];

        $candidates = $aliasMap[$normalized] ?? [$normalized];

        $existingId = DB::table('admin_documents')
            ->where(function ($query) use ($candidates) {
                foreach ($candidates as $candidate) {
                    $query->orWhereRaw('LOWER(TRIM(title)) = ?', [$candidate]);
                }
            })
            ->orderBy('id')
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        try {
            $nextId = max(((int) DB::table('admin_documents')->max('id')) + 1, 1);

            DB::table('admin_documents')->insert([
                'id' => $nextId,
                'title' => $title,
                'is_enabled' => 'Yes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $nextId;
        } catch (\Throwable $e) {
            $existingId = DB::table('admin_documents')
                ->whereRaw('LOWER(TRIM(title)) = ?', [$normalized])
                ->value('id');

            if ($existingId) {
                return (int) $existingId;
            }

            \Log::error('resolveAdminDocumentId failed for "' . $title . '": ' . $e->getMessage());
            return null;
        }
    }

    private function storeDriverDocumentUpload($file, int $driverId, ?int $documentId = null, ?string $title = null): void
    {
        if (!$file) {
            return;
        }

        $targetDir = public_path('assets/images/driver/documents');
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        if (!$documentId && $title) {
            $documentId = $this->resolveAdminDocumentId($title);
        }

        if (!$documentId || $documentId <= 0) {
            \Log::warning('storeDriverDocumentUpload skipped: missing admin document id for "' . ($title ?? 'unknown') . '"');
            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $safeTitle = str_replace(' ', '_', $title ?: 'Document');
        $filename = $safeTitle . '_' . time() . '_' . rand(100, 999) . '.' . $extension;

        $uploadedUrl = null;
        if (!empty(config('imagekit.private_key'))) {
            try {
                $uploadedUrl = $this->uploadToImageKit($file, '/driver/documents');
            } catch (\Throwable $e) {
                \Log::warning('ImageKit upload failed: ' . $e->getMessage());
            }
        }

        if ($uploadedUrl) {
            $filename = $uploadedUrl;
        } else {
            $file->move($targetDir, $filename);
        }

        $existingDoc = DB::table('driver_document')
            ->where('document_id', $documentId)
            ->where('driver_id', $driverId)
            ->first();

        if ($existingDoc) {
            DB::table('driver_document')->where('id', $existingDoc->id)->update([
                'document_path' => $filename,
                'document_status' => 'Pending',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('driver_document')->insert([
                'driver_id' => $driverId,
                'document_id' => $documentId,
                'document_path' => $filename,
                'document_status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function buildExistingDriverSelection(int $driverId): ?array
    {
        if (!Schema::hasTable('tj_conducteur_categories')) {
            return null;
        }

        $rows = DB::table('tj_conducteur_categories')
            ->where('driver_id', $driverId)
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $sellerCategoryId = DB::table('tj_categorie_user')
            ->where('libelle', 'Online Seller')
            ->value('id');

        $mainCategoryId = null;
        foreach ($rows as $row) {
            $candidateId = (int) ($row->subcategory_id ?: $row->category_id);
            if ($candidateId <= 0) {
                continue;
            }
            if ($sellerCategoryId && $candidateId === (int) $sellerCategoryId) {
                continue;
            }
            $mainCategoryId = $candidateId;
            break;
        }

        if (!$mainCategoryId) {
            return null;
        }

        $businessCategory = UserCategory::find($mainCategoryId);
        if (!$businessCategory) {
            return null;
        }

        $topLevelCategory = $businessCategory;
        $depth = 0;
        while ($topLevelCategory && $topLevelCategory->parent_id && $depth < 6) {
            $topLevelCategory = UserCategory::find($topLevelCategory->parent_id);
            $depth++;
        }

        $driver = Schema::hasTable('tj_conducteur')
            ? DB::table('tj_conducteur')->where('id', $driverId)->first()
            : null;

        return [
            'primary_category_id' => (int) ($topLevelCategory?->id ?? $businessCategory->parent_id ?? $mainCategoryId),
            'business_subcategory_id' => $mainCategoryId,
            'business_subcategory_label' => (string) $businessCategory->libelle,
            'bank_name' => (string) ($driver->bank_name ?? ''),
            'account_no' => (string) ($driver->account_no ?? ''),
            'ifsc_code' => (string) ($driver->ifsc_code ?? ''),
            'zone_id' => isset($driver->zone_id) ? (string) $driver->zone_id : '',
        ];
    }
}
