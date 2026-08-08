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

            // 1. Primary Categories & Subcategories
            $categories = UserCategory::whereNull('parent_id')->with('subcategories')->get();

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
                    ->get(['id', 'title']);
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
            if ($driverId && Schema::hasTable('driver_service_pricing')) {
                $pricingRow = DB::table('driver_service_pricing')
                    ->where('driver_id', $driverId)
                    ->orderByDesc('updated_at')
                    ->first();

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

            return response()->json([
                'success' => 'success',
                'data' => [
                    'onboarding_completed' => $onboardingCompleted,
                    'categories' => $enrichedCategories,
                    'vehicle_mappings' => $vehicleMappings,
                    'vehicles' => array_values($structuredVehicles),
                    'transport_delivery_map' => $transportDeliveryMap,
                    'admin_docs' => $adminDocs,
                    'zones' => $zones,
                    'service_pricing' => $servicePricing,
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
            $requiresManualApproval = $topLevelCategory && trim($topLevelCategory->libelle) === '🚕 Transport & Mobility';

            if ($mode !== 'edit_category') {
                $driverUpdateData = [
                    'is_verified' => $requiresManualApproval ? 0 : 1,
                    'statut' => $requiresManualApproval ? 'no' : 'yes',
                    'bank_name' => $request->input('bank_name'),
                    'account_no' => $request->input('account_no'),
                    'ifsc_code' => $request->input('ifsc_code'),
                    'zone_id' => $request->input('zone_id')
                ];
                DB::table('tj_conducteur')->where('id', $driverId)->update($driverUpdateData);
            }

            if ($mode !== 'edit_category') {
                $isFleet = $primaryCategory->libelle === 'Fleet Owner';
                
                if (empty($vehicles)) {
                } else if ($isFleet) {
                    foreach ($vehicles as $veh) {
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
                            'passenger' => $veh['passenger'] ?? '4',
                            'statut' => 'yes',
                            'creer' => now(),
                            'updated_at' => now()
                        ]);
                    }
                } else {
                    $firstVeh = $vehicles[0];
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
                        'passenger' => $firstVeh['passenger'] ?? '4',
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
                if ($visitingCharge === null || $visitingCharge === '') {
                    return response()->json([
                        'success' => 'Failed',
                        'error' => 'Visiting charge is required for home-visit service providers',
                    ]);
                }
            }

            $this->saveDriverServicePricing($driverId, (int) $primaryCategoryId, $primaryCategory, $request);

            if ($mode !== 'edit_category') {
                $targetDir = public_path('assets/images/driver/documents');
                if (!file_exists($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }

                $adminDocs = DB::table('admin_documents')->where('is_enabled', 'Yes')->get();
                foreach ($adminDocs as $doc) {
                    $inputName = 'doc_' . $doc->id;
                    
                    if ($request->hasFile($inputName)) {
                        $file = $request->file($inputName);
                        $extension = strtolower($file->getClientOriginalExtension());
                        $filename = str_replace(' ', '_', $doc->title) . '_' . time() . '_' . rand(100, 999) . '.' . $extension;

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
                            ->where('document_id', $doc->id)
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
                                'document_id' => $doc->id,
                                'document_path' => $filename,
                                'document_status' => 'Pending',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
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

        $parentLabel = is_array($parent) ? ($parent['libelle'] ?? '') : ($parent->libelle ?? '');
        $excludedTerms = [
            'Transport', 'Delivery', 'Mobility', 'Logistics',
            'Online Seller', 'Retail Shop', 'Restaurant', 'Hotel', 'Manufacturing',
        ];

        foreach ($excludedTerms as $term) {
            if (stripos($parentLabel, $term) !== false) {
                return false;
            }
        }

        return true;
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

        if (!$topLevel) {
            return false;
        }

        if (Schema::hasTable('tj_category_user_vehicle_type')) {
            $requiresVehicle = DB::table('tj_category_user_vehicle_type')
                ->where('category_user_id', $category->id)
                ->exists();
            if ($requiresVehicle) {
                return false;
            }
        }

        return self::subcategoryRequiresHomeVisit($topLevel, false);
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
        if ($visitingCharge === null || $visitingCharge === '') {
            return;
        }

        $serviceItems = json_decode($request->input('service_items', '[]'), true);
        if (!is_array($serviceItems)) {
            $serviceItems = [];
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
}
