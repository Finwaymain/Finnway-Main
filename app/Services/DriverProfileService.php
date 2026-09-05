<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DriverProfileService
{
    /**
     * Check if a driver has actually completed the onboarding process.
     */
    public static function isOnboardingCompleted(int|string $driverId): bool
    {
        $id = (int)$driverId;
        if ($id <= 0) {
            return false;
        }

        $driver = DB::table('tj_conducteur')->where('id', $id)->first();
        if (!$driver) {
            return false;
        }

        // If explicitly marked not onboarded, return false immediately
        $onboardingStatus = strtolower(trim((string)($driver->onboarding_completed ?? '')));
        if ($onboardingStatus === 'no') {
            return false;
        }

        if (!Schema::hasTable('tj_conducteur_categories')) {
            return false;
        }

        $hasCategories = DB::table('tj_conducteur_categories')->where('driver_id', $id)->exists();
        if (!$hasCategories) {
            if ($onboardingStatus === 'yes') {
                DB::table('tj_conducteur')->where('id', $id)->update([
                    'onboarding_completed' => 'no',
                    'is_verified' => 0,
                    'statut' => 'no',
                    'statut_vehicule' => 'no',
                ]);
            }
            return false;
        }

        // Check if driver has submitted bank details
        $hasBank = !empty(trim((string)($driver->bank_name ?? ''))) ||
                   !empty(trim((string)($driver->account_no ?? ''))) ||
                   !empty(trim((string)($driver->ifsc_code ?? '')));

        // Check if driver has uploaded identity/license documents in tj_conducteur
        $hasDocs = !empty(trim((string)($driver->photo_licence_path ?? ''))) ||
                   !empty(trim((string)($driver->photo_nic_path ?? ''))) ||
                   !empty(trim((string)($driver->photo_car_service_book_path ?? '')));

        // Check driver_document / driver_documents table
        if (!$hasDocs) {
            if (Schema::hasTable('driver_document')) {
                $hasDocs = DB::table('driver_document')->where('driver_id', $id)->exists();
            } elseif (Schema::hasTable('driver_documents')) {
                $hasDocs = DB::table('driver_documents')->where('driver_id', $id)->exists();
            }
        }

        // Check if driver has registered a vehicle
        $hasVehicle = false;
        if (Schema::hasTable('tj_vehicule')) {
            $hasVehicle = DB::table('tj_vehicule')
                ->where('id_conducteur', $id)
                ->where('statut', 'yes')
                ->exists();
        }

        // Check if driver has home service skills
        $hasSkills = false;
        if (Schema::hasTable('driver_service_skills')) {
            $hasSkills = DB::table('driver_service_skills')->where('driver_id', $id)->exists();
        }

        // KYC status is an admin identity check, NOT onboarding completion.
        // True onboarding requires actual operational artifacts (bank, docs, vehicle, or skills).
        $hasOnboardedArtifacts = ($hasBank || $hasDocs || $hasVehicle || $hasSkills);

        if (!$hasOnboardedArtifacts) {
            self::purgeDriverCategories($id);
            DB::table('tj_conducteur')->where('id', $id)->update([
                'onboarding_completed' => 'no',
                'is_verified' => 0,
                'statut' => 'no',
                'statut_vehicule' => 'no',
            ]);
            return false;
        }

        // If onboarding_completed column exists and is not 'yes', check if driver has verified status
        if ($onboardingStatus !== 'yes') {
            $isVerified = ($driver->is_verified ?? 0) == 1 || ($driver->statut ?? '') === 'yes';
            if ($isVerified && $hasCategories && $hasOnboardedArtifacts) {
                DB::table('tj_conducteur')->where('id', $id)->update(['onboarding_completed' => 'yes']);
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Purge all categories and pricing/skills for a specific driver ID.
     */
    public static function purgeDriverCategories(int|string $driverId): void
    {
        $id = (int)$driverId;
        if ($id <= 0) {
            return;
        }

        if (Schema::hasTable('tj_conducteur_categories')) {
            DB::table('tj_conducteur_categories')->where('driver_id', $id)->delete();
        }
        if (Schema::hasTable('driver_service_pricing')) {
            DB::table('driver_service_pricing')->where('driver_id', $id)->delete();
        }
        if (Schema::hasTable('driver_service_skills')) {
            DB::table('driver_service_skills')->where('driver_id', $id)->delete();
        }
    }

    /**
     * Completely clean all orphaned category, pricing, skill, and access rows
     * where the driver no longer exists in tj_conducteur.
     */
    public static function cleanAllOrphanDriverRecords(): array
    {
        $purged = [
            'categories' => 0,
            'pricing' => 0,
            'skills' => 0,
            'access' => 0,
            'unonboarded_categories' => 0,
        ];

        $validDriverIds = DB::table('tj_conducteur')->pluck('id')->toArray();

        if (Schema::hasTable('tj_conducteur_categories')) {
            $purged['categories'] = DB::table('tj_conducteur_categories')
                ->whereNotIn('driver_id', $validDriverIds)
                ->delete();
        }

        if (Schema::hasTable('driver_service_pricing')) {
            $purged['pricing'] = DB::table('driver_service_pricing')
                ->whereNotIn('driver_id', $validDriverIds)
                ->delete();
        }

        if (Schema::hasTable('driver_service_skills')) {
            $purged['skills'] = DB::table('driver_service_skills')
                ->whereNotIn('driver_id', $validDriverIds)
                ->delete();
        }

        if (Schema::hasTable('users_access')) {
            $purged['access'] = DB::table('users_access')
                ->where('user_type', 'driver')
                ->whereNotIn('user_id', $validDriverIds)
                ->delete();
        }

        return $purged;
    }
}