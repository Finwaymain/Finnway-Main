<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UserPurgeService
{
    /**
     * Completely delete a driver and all related table data and files.
     *
     * @param int|string $driverId Driver primary key (tj_conducteur.id)
     * @param string|null $phone Optional phone number to match/cleanup
     * @return bool True if purge succeeded
     */
    public static function purgeDriver(int|string $driverId, ?string $phone = null): bool
    {
        $id = (int)$driverId;

        // Find driver record to extract phone and file paths
        $driver = null;
        if ($id > 0) {
            $driver = DB::table('tj_conducteur')->where('id', $id)->first();
        }

        $phoneToUse = $phone ?? ($driver ? $driver->phone : null);
        $altPhone = $driver ? ($driver->alternate_phone ?? null) : null;
        $phones = self::buildPhoneVariants($phoneToUse, $altPhone);

        // If no driver found by ID but phone is provided, attempt lookup by phone
        if (!$driver && !empty($phones)) {
            $driver = DB::table('tj_conducteur')->whereIn('phone', $phones)->first();
            if ($driver) {
                $id = (int)$driver->id;
            }
        }

        try {
            DB::beginTransaction();

            // 1. Delete category mappings, service pricing, skills, items
            if ($id > 0) {
                if (Schema::hasTable('tj_conducteur_categories')) {
                    DB::table('tj_conducteur_categories')->where('driver_id', $id)->delete();
                }
                if (Schema::hasTable('driver_service_pricing')) {
                    DB::table('driver_service_pricing')->where('driver_id', $id)->delete();
                }
                if (Schema::hasTable('driver_service_skills')) {
                    DB::table('driver_service_skills')->where('driver_id', $id)->delete();
                }
                if (Schema::hasTable('driver_service_items')) {
                    DB::table('driver_service_items')->where('driver_id', $id)->delete();
                }
            }

            // 2. Delete driver documents and physical files
            if ($id > 0 && Schema::hasTable('driver_document')) {
                $docs = DB::table('driver_document')->where('driver_id', $id)->get();
                foreach ($docs as $doc) {
                    if (!empty($doc->document_path)) {
                        self::deleteFile(public_path('assets/images/driver/documents/' . $doc->document_path));
                        self::deleteFile(public_path('assets/images/driver/' . $doc->document_path));
                    }
                }
                DB::table('driver_document')->where('driver_id', $id)->delete();
            }

            // 3. Delete vehicles, vehicle images, and service book
            if ($id > 0) {
                $vehicleIds = [];
                if (Schema::hasTable('tj_vehicule')) {
                    $vehicleIds = DB::table('tj_vehicule')->where('id_conducteur', $id)->pluck('id')->toArray();
                }

                if (Schema::hasTable('tj_vehicle_images')) {
                    $images = DB::table('tj_vehicle_images')
                        ->where('id_driver', $id)
                        ->orWhereIn('id_vehicle', $vehicleIds)
                        ->get();
                    foreach ($images as $img) {
                        if (!empty($img->image_path)) {
                            self::deleteFile(public_path('assets/images/vehicle/' . $img->image_path));
                        }
                    }
                    DB::table('tj_vehicle_images')
                        ->where('id_driver', $id)
                        ->orWhereIn('id_vehicle', $vehicleIds)
                        ->delete();
                }

                if (Schema::hasTable('tj_vehicule_service_book')) {
                    $books = DB::table('tj_vehicule_service_book')->where('id_conducteur', $id)->get();
                    foreach ($books as $b) {
                        if (!empty($b->photo_car_service_book_path)) {
                            self::deleteFile(public_path('assets/images/vehicle/' . $b->photo_car_service_book_path));
                        }
                    }
                    DB::table('tj_vehicule_service_book')->where('id_conducteur', $id)->delete();
                }

                if (Schema::hasTable('tj_vehicule')) {
                    DB::table('tj_vehicule')->where('id_conducteur', $id)->delete();
                }
            }

            // 4. Delete kit orders
            if (Schema::hasTable('driver_kit_orders')) {
                $q = DB::table('driver_kit_orders');
                if ($id > 0) {
                    $q->where('driver_id', $id);
                }
                if (!empty($phones)) {
                    if ($id > 0) {
                        $q->orWhereIn('receiver_phone', $phones);
                    } else {
                        $q->whereIn('receiver_phone', $phones);
                    }
                }
                $q->delete();
            }

            // 5. Delete financial & wallet records
            if ($id > 0) {
                if (Schema::hasTable('tj_conducteur_transaction')) {
                    DB::table('tj_conducteur_transaction')
                        ->where('id_conducteur', $id)
                        ->orWhere(function ($q) use ($id) {
                            $q->where('receiver_user_id', $id)->where('user_type', 'driver');
                        })
                        ->delete();
                }
                if (Schema::hasTable('withdrawals')) {
                    DB::table('withdrawals')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('subscription_history')) {
                    DB::table('subscription_history')->where('user_id', $id)->delete();
                }
            }

            // 6. Delete tokens, common user base, referrals, and OTP records
            if ($id > 0) {
                if (Schema::hasTable('users_access')) {
                    DB::table('users_access')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('common_user_base')) {
                    DB::table('common_user_base')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('referral')) {
                    DB::table('referral')->where('user_id', $id)->where('user_type', 'driver')->delete();
                    DB::table('referral')->where('referral_by_id', $id)->where('referral_by_type', 'driver')->update([
                        'referral_by_id'   => null,
                        'referral_by_type' => null,
                        'referral_by_code' => '',
                    ]);
                }
            }

            if (!empty($phones) && Schema::hasTable('auth_otp_temp')) {
                DB::table('auth_otp_temp')->whereIn('phone', $phones)->where('user_cat', 'driver')->delete();
            }

            // 7. Delete rides, bookings & orders
            if ($id > 0) {
                if (Schema::hasTable('tj_requete')) {
                    DB::table('tj_requete')
                        ->where('id_conducteur', $id)
                        ->orWhere('id_conducteur_accepter', $id)
                        ->delete();
                }
                if (Schema::hasTable('tj_requete_book')) {
                    DB::table('tj_requete_book')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('service_requests')) {
                    DB::table('service_requests')->where('driver_id', $id)->delete();
                }
                if (Schema::hasTable('parcel_orders')) {
                    DB::table('parcel_orders')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('dispatcher_booking')) {
                    DB::table('dispatcher_booking')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('tj_recu')) {
                    DB::table('tj_recu')->where('id_conducteur', $id)->delete();
                }
            }

            // 8. Delete communications, notes, and complaints
            if ($id > 0) {
                if (Schema::hasTable('tj_message')) {
                    DB::table('tj_message')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('tj_note')) {
                    DB::table('tj_note')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('tj_user_note')) {
                    DB::table('tj_user_note')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('tj_complaints')) {
                    DB::table('tj_complaints')->where('id_conducteur', $id)->delete();
                }
                if (Schema::hasTable('support_tickets')) {
                    DB::table('support_tickets')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('admin_notification')) {
                    DB::table('admin_notification')->where('user_id', $id)->delete();
                }
            }

            // 9. Delete medical & marketplace records
            if ($id > 0) {
                if (Schema::hasTable('tj_medical_cards')) {
                    DB::table('tj_medical_cards')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('tj_medical_claims')) {
                    DB::table('tj_medical_claims')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('tj_medical_expenses')) {
                    DB::table('tj_medical_expenses')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('marketplace_products')) {
                    DB::table('marketplace_products')->where('user_id', $id)->where('user_type', 'driver')->delete();
                }
                if (Schema::hasTable('marketplace_orders')) {
                    DB::table('marketplace_orders')->where('seller_id', $id)->where('seller_type', 'driver')->delete();
                }
            }

            // 10. Delete driver photo files and main tj_conducteur record
            if ($driver) {
                if (!empty($driver->photo_path)) {
                    self::deleteFile(public_path('assets/images/driver/' . $driver->photo_path));
                }
                if (!empty($driver->photo_licence_path)) {
                    self::deleteFile(public_path('assets/images/driver/' . $driver->photo_licence_path));
                }
                if (!empty($driver->photo_nic_path)) {
                    self::deleteFile(public_path('assets/images/driver/' . $driver->photo_nic_path));
                }
                if (!empty($driver->photo_car_service_book_path)) {
                    self::deleteFile(public_path('assets/images/driver/' . $driver->photo_car_service_book_path));
                }
            }

            if ($id > 0) {
                DB::table('tj_conducteur')->where('id', $id)->delete();
            }
            if (!empty($phones)) {
                DB::table('tj_conducteur')->whereIn('phone', $phones)->delete();
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('UserPurgeService::purgeDriver failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Completely delete a customer (user_app) and all related table data and files.
     *
     * @param int|string $userId Customer primary key (tj_user_app.id)
     * @param string|null $phone Optional phone number to match/cleanup
     * @return bool True if purge succeeded
     */
    public static function purgeCustomer(int|string $userId, ?string $phone = null): bool
    {
        $id = (int)$userId;

        $user = null;
        if ($id > 0) {
            $user = DB::table('tj_user_app')->where('id', $id)->first();
        }

        $phoneToUse = $phone ?? ($user ? $user->phone : null);
        $altPhone = $user ? ($user->alternate_phone ?? null) : null;
        $phones = self::buildPhoneVariants($phoneToUse, $altPhone);

        if (!$user && !empty($phones)) {
            $user = DB::table('tj_user_app')->whereIn('phone', $phones)->first();
            if ($user) {
                $id = (int)$user->id;
            }
        }

        try {
            DB::beginTransaction();

            // 1. Delete tokens, common user base, referrals, and OTP records
            if ($id > 0) {
                if (Schema::hasTable('users_access')) {
                    DB::table('users_access')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('common_user_base')) {
                    DB::table('common_user_base')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('referral')) {
                    DB::table('referral')->where('user_id', $id)->where('user_type', 'customer')->delete();
                    DB::table('referral')->where('referral_by_id', $id)->where('referral_by_type', 'customer')->update([
                        'referral_by_id'   => null,
                        'referral_by_type' => null,
                        'referral_by_code' => '',
                    ]);
                }
                if (Schema::hasTable('subscription_history')) {
                    DB::table('subscription_history')->where('user_id', $id)->delete();
                }
            }

            if (!empty($phones) && Schema::hasTable('auth_otp_temp')) {
                DB::table('auth_otp_temp')->whereIn('phone', $phones)->where('user_cat', 'customer')->delete();
            }

            // 2. Delete wallet / transaction records
            if ($id > 0 && Schema::hasTable('tj_transaction')) {
                DB::table('tj_transaction')->where('id_user_app', $id)->delete();
            }

            // 3. Delete rides, bookings & orders
            if ($id > 0) {
                if (Schema::hasTable('tj_favorite_ride')) {
                    DB::table('tj_favorite_ride')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_location_vehicule')) {
                    DB::table('tj_location_vehicule')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_requete')) {
                    DB::table('tj_requete')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_requete_book')) {
                    DB::table('tj_requete_book')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('service_requests')) {
                    DB::table('service_requests')->where('user_id', $id)->delete();
                }
                if (Schema::hasTable('parcel_orders')) {
                    DB::table('parcel_orders')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('dispatcher_booking')) {
                    DB::table('dispatcher_booking')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_recu')) {
                    DB::table('tj_recu')->where('id_user_app', $id)->delete();
                }
            }

            // 4. Delete communications, notes, complaints
            if ($id > 0) {
                if (Schema::hasTable('tj_message')) {
                    DB::table('tj_message')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_note')) {
                    DB::table('tj_note')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_user_note')) {
                    DB::table('tj_user_note')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('tj_complaints')) {
                    DB::table('tj_complaints')->where('id_user_app', $id)->delete();
                }
                if (Schema::hasTable('support_tickets')) {
                    DB::table('support_tickets')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('admin_notification')) {
                    DB::table('admin_notification')->where('user_id', $id)->delete();
                }
            }

            // 5. Delete medical & marketplace
            if ($id > 0) {
                if (Schema::hasTable('tj_medical_cards')) {
                    DB::table('tj_medical_cards')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('tj_medical_claims')) {
                    DB::table('tj_medical_claims')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('tj_medical_expenses')) {
                    DB::table('tj_medical_expenses')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('marketplace_products')) {
                    DB::table('marketplace_products')->where('user_id', $id)->where('user_type', 'customer')->delete();
                }
                if (Schema::hasTable('marketplace_orders')) {
                    DB::table('marketplace_orders')->where('user_id', $id)->delete();
                }
            }

            // 6. Delete user photo files and main tj_user_app record
            if ($user) {
                if (!empty($user->photo_path)) {
                    self::deleteFile(public_path('assets/images/users/' . $user->photo_path));
                }
                if (!empty($user->photo_nic_path)) {
                    self::deleteFile(public_path('assets/images/users/' . $user->photo_nic_path));
                }
            }

            if ($id > 0) {
                DB::table('tj_user_app')->where('id', $id)->delete();
            }
            if (!empty($phones)) {
                DB::table('tj_user_app')->whereIn('phone', $phones)->delete();
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('UserPurgeService::purgeCustomer failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unified dispatcher: automatically determines whether to purge driver, customer, or both.
     */
    public static function purgeUser(int|string $id, ?string $userCat = null, ?string $phone = null): bool
    {
        $normalizedCat = strtolower(trim((string)$userCat));

        if ($normalizedCat === 'driver' || $normalizedCat === 'conducteur') {
            return self::purgeDriver($id, $phone);
        }

        if ($normalizedCat === 'customer' || $normalizedCat === 'user' || $normalizedCat === 'consumer') {
            return self::purgeCustomer($id, $phone);
        }

        // If category not specified, check where the user exists
        $isDriver = false;
        $isCustomer = false;

        $intId = (int)$id;
        if ($intId > 0) {
            $isDriver = DB::table('tj_conducteur')->where('id', $intId)->exists();
            $isCustomer = DB::table('tj_user_app')->where('id', $intId)->exists();
        }

        if (!$isDriver && !$isCustomer && !empty($phone)) {
            $phones = self::buildPhoneVariants($phone);
            $isDriver = DB::table('tj_conducteur')->whereIn('phone', $phones)->exists();
            $isCustomer = DB::table('tj_user_app')->whereIn('phone', $phones)->exists();
        }

        $success = false;
        if ($isDriver) {
            $success = self::purgeDriver($id, $phone) || $success;
        }
        if ($isCustomer) {
            $success = self::purgeCustomer($id, $phone) || $success;
        }

        return $success || ($isDriver || $isCustomer);
    }

    /**
     * Purge all records across both driver and customer tables for a specific phone number.
     */
    public static function purgeByPhone(string $phone, ?string $userCat = null): bool
    {
        $normalizedCat = strtolower(trim((string)$userCat));
        $success = true;

        if ($normalizedCat === 'driver' || $normalizedCat === 'conducteur' || empty($normalizedCat)) {
            $drivers = DB::table('tj_conducteur')
                ->whereIn('phone', self::buildPhoneVariants($phone))
                ->pluck('id');
            foreach ($drivers as $dId) {
                $success = self::purgeDriver($dId, $phone) && $success;
            }
            if ($drivers->isEmpty()) {
                self::purgeDriver(0, $phone);
            }
        }

        if ($normalizedCat === 'customer' || $normalizedCat === 'user' || $normalizedCat === 'consumer' || empty($normalizedCat)) {
            $users = DB::table('tj_user_app')
                ->whereIn('phone', self::buildPhoneVariants($phone))
                ->pluck('id');
            foreach ($users as $uId) {
                $success = self::purgeCustomer($uId, $phone) && $success;
            }
            if ($users->isEmpty()) {
                self::purgeCustomer(0, $phone);
            }
        }

        return $success;
    }

    /**
     * Build phone variants (with/without +91, last 10 digits).
     */
    public static function buildPhoneVariants(?string $phone, ?string $altPhone = null): array
    {
        $variants = [];
        $candidates = array_filter([$phone, $altPhone]);

        foreach ($candidates as $cand) {
            $trimmed = trim((string)$cand);
            if (empty($trimmed)) continue;

            $variants[] = $trimmed;

            $digits = preg_replace('/\D/', '', $trimmed);
            if (!empty($digits)) {
                $variants[] = $digits;
                if (strlen($digits) >= 10) {
                    $last10 = substr($digits, -10);
                    $variants[] = $last10;
                    $variants[] = '+91' . $last10;
                    $variants[] = '91' . $last10;
                    $variants[] = '0' . $last10;
                }
            }
        }

        return array_values(array_unique($variants));
    }

    /**
     * Clean up orphaned records across tables where the parent user/driver no longer exists.
     *
     * @return array Summary of deleted orphan counts by table
     */
    public static function purgeAllOrphans(): array
    {
        $counts = [];

        try {
            DB::beginTransaction();

            if (Schema::hasTable('common_user_base') && Schema::hasTable('tj_conducteur')) {
                $counts['common_user_base_drivers'] = DB::table('common_user_base')
                    ->where('user_type', 'driver')
                    ->whereNotIn('user_id', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('common_user_base') && Schema::hasTable('tj_user_app')) {
                $counts['common_user_base_customers'] = DB::table('common_user_base')
                    ->whereIn('user_type', ['customer', 'user'])
                    ->whereNotIn('user_id', DB::table('tj_user_app')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('tj_conducteur_categories') && Schema::hasTable('tj_conducteur')) {
                $counts['tj_conducteur_categories'] = DB::table('tj_conducteur_categories')
                    ->whereNotIn('driver_id', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('driver_service_skills') && Schema::hasTable('tj_conducteur')) {
                $counts['driver_service_skills'] = DB::table('driver_service_skills')
                    ->whereNotIn('driver_id', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('driver_service_pricing') && Schema::hasTable('tj_conducteur')) {
                $counts['driver_service_pricing'] = DB::table('driver_service_pricing')
                    ->whereNotIn('driver_id', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('driver_service_items') && Schema::hasTable('tj_conducteur')) {
                $counts['driver_service_items'] = DB::table('driver_service_items')
                    ->whereNotIn('driver_id', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('driver_document') && Schema::hasTable('tj_conducteur')) {
                $counts['driver_document'] = DB::table('driver_document')
                    ->whereNotIn('driver_id', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            if (Schema::hasTable('tj_vehicule') && Schema::hasTable('tj_conducteur')) {
                $counts['tj_vehicule'] = DB::table('tj_vehicule')
                    ->whereNotIn('id_conducteur', DB::table('tj_conducteur')->pluck('id'))
                    ->delete();
            }

            DB::commit();
            Log::info('UserPurgeService::purgeAllOrphans completed', $counts);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('UserPurgeService::purgeAllOrphans error: ' . $e->getMessage());
        }

        return $counts;
    }

    /**
     * Safely delete a file from the filesystem.
     */
    private static function deleteFile(string $path): void
    {
        try {
            if (File::exists($path) && is_file($path)) {
                File::delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning('UserPurgeService::deleteFile failed: ' . $e->getMessage());
        }
    }
}
