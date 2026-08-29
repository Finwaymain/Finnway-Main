<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankValidationHelper
{
    public static function extractLast10Digits($phone)
    {
        if (empty($phone)) return null;
        $digits = preg_replace('/[^0-9]/', '', (string)$phone);
        if (strlen($digits) >= 10) {
            return substr($digits, -10);
        }
        return $digits;
    }

    /**
     * Validate Bank Details and ensure Account Number is uniquely mapped to this Phone/User.
     */
    public static function validateBankDetails($bankName, $accountNo, $ifscCode, $currentPhone = null, $currentId = null, $userType = 'driver')
    {
        $bankName = trim((string)$bankName);
        $accountNo = trim((string)$accountNo);
        $ifscCode = strtoupper(trim((string)$ifscCode));

        // 1. Bank Name: Only letters & spaces
        if (empty($bankName)) {
            return ['valid' => false, 'error' => 'Bank name is required.'];
        }
        if (!preg_match('/^[a-zA-Z\s]+$/', $bankName)) {
            return ['valid' => false, 'error' => 'Bank name must contain only letters and spaces (numbers and special symbols &6✓]{ are not allowed).'];
        }
        if (strlen($bankName) < 2 || strlen($bankName) > 100) {
            return ['valid' => false, 'error' => 'Bank name must be between 2 and 100 characters.'];
        }

        // 2. Account Number: Only numeric digits
        if (empty($accountNo)) {
            return ['valid' => false, 'error' => 'Account number is required.'];
        }
        if (!preg_match('/^[0-9]+$/', $accountNo)) {
            return ['valid' => false, 'error' => 'Account number must contain only numeric digits (no letters or symbols).'];
        }
        if (strlen($accountNo) < 8 || strlen($accountNo) > 22) {
            return ['valid' => false, 'error' => 'Account number must be between 9 and 18 digits.'];
        }

        // 3. IFSC Code: 11 Alphanumeric characters
        if (empty($ifscCode)) {
            return ['valid' => false, 'error' => 'IFSC code is required.'];
        }
        if (!preg_match('/^[A-Z0-9]{11}$/', $ifscCode)) {
            return ['valid' => false, 'error' => 'IFSC code must be 11 alphanumeric characters (e.g. SBIN0001234).'];
        }

        // Resolve current user phone from DB if not passed directly
        if (empty($currentPhone) && !empty($currentId)) {
            if ($userType === 'driver' && Schema::hasTable('tj_conducteur')) {
                $row = DB::table('tj_conducteur')->where('id', $currentId)->first();
                $currentPhone = $row ? ($row->phone ?? null) : null;
            } elseif (Schema::hasTable('tj_user_app')) {
                $row = DB::table('tj_user_app')->where('id', $currentId)->first();
                $currentPhone = $row ? ($row->phone ?? null) : null;
            }
        }

        $cleanPhone = self::extractLast10Digits($currentPhone);

        // 4. Global Unique Mapping Rule:
        // Check in tj_conducteur
        if (Schema::hasTable('tj_conducteur') && Schema::hasColumn('tj_conducteur', 'account_no')) {
            $otherDrivers = DB::table('tj_conducteur')
                ->where('account_no', $accountNo)
                ->when($userType === 'driver' && !empty($currentId), function($q) use ($currentId) {
                    return $q->where('id', '!=', $currentId);
                })
                ->get();

            foreach ($otherDrivers as $d) {
                $otherPhone = self::extractLast10Digits($d->phone ?? '');
                if (!empty($cleanPhone) && !empty($otherPhone) && $otherPhone !== $cleanPhone) {
                    return [
                        'valid' => false,
                        'error' => 'This bank account number is already registered with another mobile number in the system. Duplicate use across different mobile numbers is not allowed.'
                    ];
                }
                if (empty($cleanPhone) && !empty($otherPhone)) {
                    return [
                        'valid' => false,
                        'error' => 'This bank account number is already registered with another mobile number in the system.'
                    ];
                }
            }
        }

        // Check in tj_user_app
        if (Schema::hasTable('tj_user_app') && Schema::hasColumn('tj_user_app', 'account_no')) {
            $otherUsers = DB::table('tj_user_app')
                ->where('account_no', $accountNo)
                ->when($userType === 'customer' && !empty($currentId), function($q) use ($currentId) {
                    return $q->where('id', '!=', $currentId);
                })
                ->get();

            foreach ($otherUsers as $u) {
                $otherPhone = self::extractLast10Digits($u->phone ?? '');
                if (!empty($cleanPhone) && !empty($otherPhone) && $otherPhone !== $cleanPhone) {
                    return [
                        'valid' => false,
                        'error' => 'This bank account number is already registered with another mobile number in the system. Duplicate use across different mobile numbers is not allowed.'
                    ];
                }
                if (empty($cleanPhone) && !empty($otherPhone)) {
                    return [
                        'valid' => false,
                        'error' => 'This bank account number is already registered with another mobile number in the system.'
                    ];
                }
            }
        }

        return ['valid' => true, 'error' => null];
    }
}