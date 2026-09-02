<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class WalletBalanceHelper
{
    /**
     * Clean a phone string to the last 10 digits.
     */
    public static function cleanPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        $digits = preg_replace('/\D/', '', $phone);
        return (strlen($digits) >= 10) ? substr($digits, -10) : $digits;
    }

    /**
     * Resolve user strictly using unique phone number first, then ID with type.
     */
    public static function resolveUser(?string $phone, $id, string $userType = 'customer')
    {
        $table = ($userType === 'driver') ? 'tj_conducteur' : 'tj_user_app';
        $clean = self::cleanPhone($phone);

        if (!empty($clean)) {
            $user = DB::table($table)->where('phone', 'like', "%{$clean}%")->first();
            if ($user) {
                return $user;
            }
        }

        if (!empty($id)) {
            return DB::table($table)->where('id', $id)->first();
        }

        return null;
    }
}
