<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\UserApp;
use App\Models\Driver;

class PhoneService
{
    /**
     * Normalize any phone number to canonical E.164 (+91XXXXXXXXXX for 10-digit Indian numbers).
     */
    public static function normalize(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $phone = trim($phone);
        $digits = preg_replace('/\D/', '', $phone);

        if (empty($digits)) {
            return '';
        }

        // If 10 digits (standard Indian mobile): prepend +91
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }

        // If 11 digits starting with 0: strip 0 and prepend +91
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '+91' . substr($digits, 1);
        }

        // If 12 digits starting with 91: prepend +
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        // If starts with +, retain + with all digits
        if (str_starts_with($phone, '+')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    /**
     * Get the last 10 digits of a phone number.
     */
    public static function getLast10(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 10 ? substr($digits, -10) : $digits;
    }

    /**
     * Get all common representations of a phone number for broad matching.
     */
    public static function getVariants(?string $phone): array
    {
        if (empty($phone)) {
            return [];
        }

        $canonical = self::normalize($phone);
        $last10 = self::getLast10($phone);
        $digits = preg_replace('/\D/', '', $phone);

        $variants = [
            trim($phone),
            $canonical,
            $last10,
            $digits,
        ];

        if (strlen($last10) === 10) {
            $variants[] = '+91' . $last10;
            $variants[] = '91' . $last10;
            $variants[] = '0' . $last10;
        }

        return array_values(array_filter(array_unique($variants)));
    }

    /**
     * Check if a customer with this phone number already exists in tj_user_app.
     */
    public static function customerExists(string $phone, ?int $excludeId = null): bool
    {
        $variants = self::getVariants($phone);
        if (empty($variants)) {
            return false;
        }

        $query = DB::table('tj_user_app')->whereIn('phone', $variants);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if a driver with this phone number already exists in tj_conducteur.
     */
    public static function driverExists(string $phone, ?int $excludeId = null): bool
    {
        $variants = self::getVariants($phone);
        if (empty($variants)) {
            return false;
        }

        $query = DB::table('tj_conducteur')->whereIn('phone', $variants);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Find customer by phone across all variants, prioritizing canonical phone and highest ID.
     */
    public static function findCustomer(string $phone)
    {
        $canonical = self::normalize($phone);
        $variants = self::getVariants($phone);
        if (empty($variants)) {
            return null;
        }

        return UserApp::whereIn('phone', $variants)
            ->orderByRaw("CASE WHEN phone = ? THEN 0 ELSE 1 END", [$canonical])
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Find driver by phone across all variants, prioritizing canonical phone and highest ID.
     */
    public static function findDriver(string $phone)
    {
        $canonical = self::normalize($phone);
        $variants = self::getVariants($phone);
        if (empty($variants)) {
            return null;
        }

        return Driver::whereIn('phone', $variants)
            ->orderByRaw("CASE WHEN phone = ? THEN 0 ELSE 1 END", [$canonical])
            ->orderBy('id', 'desc')
            ->first();
    }
}
