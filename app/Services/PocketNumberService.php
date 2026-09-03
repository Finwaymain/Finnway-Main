<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class PocketNumberService
{
    const CUSTOMER_PREFIX = '7080';
    const DRIVER_PREFIX   = '7060';
    const INITIAL_SEQUENCE = 1001;

    /**
     * Resolve prefix based on user type.
     */
    public static function getPrefix(string $userType): string
    {
        $norm = strtolower(trim($userType));
        if ($norm === 'driver' || $norm === 'business' || $norm === 'partner') {
            return self::DRIVER_PREFIX;
        }
        return self::CUSTOMER_PREFIX;
    }

    /**
     * Resolve normalized user type ('customer' or 'driver').
     */
    public static function normalizeUserType(string $userType): string
    {
        $norm = strtolower(trim($userType));
        if ($norm === 'driver' || $norm === 'business' || $norm === 'partner') {
            return 'driver';
        }
        return 'customer';
    }

    /**
     * Generate and reserve a unique pocket number for a specific user ID.
     */
    public static function generateForUser(int $userId, string $userType): string
    {
        $normType = self::normalizeUserType($userType);
        $prefix   = self::getPrefix($normType);
        $table    = ($normType === 'driver') ? 'tj_conducteur' : 'tj_user_app';

        return DB::transaction(function () use ($userId, $normType, $prefix, $table) {
            $maxCommon = 1001;
            if (Schema::hasTable('common_user_base')) {
                $c = DB::table('common_user_base')
                    ->whereNotNull('ac_no')
                    ->whereRaw("ac_no REGEXP '^(7080|7060)[0-9]{8}$'")
                    ->whereRaw("CAST(SUBSTRING(ac_no, 5) AS UNSIGNED) < 500000")
                    ->lockForUpdate()
                    ->max(DB::raw('CAST(SUBSTRING(ac_no, 5) AS UNSIGNED)'));
                if ($c && (int)$c > $maxCommon) {
                    $maxCommon = (int)$c;
                }
            }

            $maxUser = 1001;
            if (Schema::hasTable('tj_user_app')) {
                $u = DB::table('tj_user_app')
                    ->whereNotNull('ac_no')
                    ->whereRaw("ac_no REGEXP '^(7080|7060)[0-9]{8}$'")
                    ->whereRaw("CAST(SUBSTRING(ac_no, 5) AS UNSIGNED) < 500000")
                    ->max(DB::raw('CAST(SUBSTRING(ac_no, 5) AS UNSIGNED)'));
                if ($u && (int)$u > $maxUser) {
                    $maxUser = (int)$u;
                }
            }

            $maxDriver = 1001;
            if (Schema::hasTable('tj_conducteur')) {
                $d = DB::table('tj_conducteur')
                    ->whereNotNull('ac_no')
                    ->whereRaw("ac_no REGEXP '^(7080|7060)[0-9]{8}$'")
                    ->whereRaw("CAST(SUBSTRING(ac_no, 5) AS UNSIGNED) < 500000")
                    ->max(DB::raw('CAST(SUBSTRING(ac_no, 5) AS UNSIGNED)'));
                if ($d && (int)$d > $maxDriver) {
                    $maxDriver = (int)$d;
                }
            }

            $nextSeq = max(self::INITIAL_SEQUENCE, (int)$maxCommon, (int)$maxUser, (int)$maxDriver) + 1;

            while (true) {
                $suffix = str_pad((string)$nextSeq, 8, '0', STR_PAD_LEFT);
                $candidate = $prefix . $suffix;

                // Ensure suffix does NOT exist anywhere across both roles
                $inCommon = DB::table('common_user_base')->where('ac_no', 'LIKE', '%' . $suffix)->exists();
                $inUser   = DB::table('tj_user_app')->where('ac_no', 'LIKE', '%' . $suffix)->exists();
                $inDriver = DB::table('tj_conducteur')->where('ac_no', 'LIKE', '%' . $suffix)->exists();

                if (!$inCommon && !$inUser && !$inDriver) {
                    // Update user table
                    DB::table($table)->where('id', $userId)->update(['ac_no' => $candidate]);

                    // Sync common_user_base
                    if (Schema::hasTable('common_user_base')) {
                        DB::table('common_user_base')->updateOrInsert(
                            ['user_id' => $userId, 'user_type' => $normType],
                            ['ac_no' => $candidate, 'status' => 1, 'date' => date('Y-m-d')]
                        );
                    }

                    return $candidate;
                }

                $nextSeq++;
            }
        });
    }

    /**
     * Generate a new, globally unique 12-digit pocket number (general method).
     */
    public static function generate(string $userType): string
    {
        $prefix = self::getPrefix($userType);

        return DB::transaction(function () use ($prefix) {
            $maxCommon = 1001;
            if (Schema::hasTable('common_user_base')) {
                $c = DB::table('common_user_base')
                    ->whereNotNull('ac_no')
                    ->whereRaw("ac_no REGEXP '^(7080|7060)[0-9]{8}$'")
                    ->whereRaw("CAST(SUBSTRING(ac_no, 5) AS UNSIGNED) < 500000")
                    ->lockForUpdate()
                    ->max(DB::raw('CAST(SUBSTRING(ac_no, 5) AS UNSIGNED)'));
                if ($c && (int)$c > $maxCommon) {
                    $maxCommon = (int)$c;
                }
            }

            $maxUser = 1001;
            if (Schema::hasTable('tj_user_app')) {
                $u = DB::table('tj_user_app')
                    ->whereNotNull('ac_no')
                    ->whereRaw("ac_no REGEXP '^(7080|7060)[0-9]{8}$'")
                    ->whereRaw("CAST(SUBSTRING(ac_no, 5) AS UNSIGNED) < 500000")
                    ->max(DB::raw('CAST(SUBSTRING(ac_no, 5) AS UNSIGNED)'));
                if ($u && (int)$u > $maxUser) {
                    $maxUser = (int)$u;
                }
            }

            $maxDriver = 1001;
            if (Schema::hasTable('tj_conducteur')) {
                $d = DB::table('tj_conducteur')
                    ->whereNotNull('ac_no')
                    ->whereRaw("ac_no REGEXP '^(7080|7060)[0-9]{8}$'")
                    ->whereRaw("CAST(SUBSTRING(ac_no, 5) AS UNSIGNED) < 500000")
                    ->max(DB::raw('CAST(SUBSTRING(ac_no, 5) AS UNSIGNED)'));
                if ($d && (int)$d > $maxDriver) {
                    $maxDriver = (int)$d;
                }
            }

            $nextSeq = max(self::INITIAL_SEQUENCE, (int)$maxCommon, (int)$maxUser, (int)$maxDriver) + 1;

            while (true) {
                $suffix = str_pad((string)$nextSeq, 8, '0', STR_PAD_LEFT);
                $candidate = $prefix . $suffix;

                $inCommon = DB::table('common_user_base')->where('ac_no', 'LIKE', '%' . $suffix)->exists();
                $inUser   = DB::table('tj_user_app')->where('ac_no', 'LIKE', '%' . $suffix)->exists();
                $inDriver = DB::table('tj_conducteur')->where('ac_no', 'LIKE', '%' . $suffix)->exists();

                if (!$inCommon && !$inUser && !$inDriver) {
                    return $candidate;
                }

                $nextSeq++;
            }
        });
    }

    /**
     * Get existing valid unique pocket number or generate and persist one for user.
     */
    public static function getOrCreatePocketNumber(int $userId, string $userType): string
    {
        $normType = self::normalizeUserType($userType);
        $prefix   = self::getPrefix($normType);
        $table    = ($normType === 'driver') ? 'tj_conducteur' : 'tj_user_app';

        $user = DB::table($table)->where('id', $userId)->first();
        if (!$user) {
            return self::generate($normType);
        }

        $currentAcNo = trim((string)($user->ac_no ?? ''));

        // Check if current ac_no is already valid 12-digit with correct prefix
        if (strlen($currentAcNo) === 12 && str_starts_with($currentAcNo, $prefix)) {
            $suffix = substr($currentAcNo, 4);

            // Check if suffix collides with opposite role
            $oppositeTable = ($normType === 'driver') ? 'tj_user_app' : 'tj_conducteur';
            $collides = DB::table($oppositeTable)
                ->whereNotNull('ac_no')
                ->where('ac_no', 'LIKE', '%' . $suffix)
                ->exists();

            if (!$collides) {
                // Ensure synced to common_user_base
                if (Schema::hasTable('common_user_base')) {
                    DB::table('common_user_base')->updateOrInsert(
                        ['user_id' => $userId, 'user_type' => $normType],
                        ['ac_no' => $currentAcNo, 'status' => 1, 'date' => date('Y-m-d')]
                    );
                }
                return $currentAcNo;
            }
        }

        // Check common_user_base for an existing ac_no
        if (Schema::hasTable('common_user_base')) {
            $base = DB::table('common_user_base')
                ->where('user_id', $userId)
                ->where('user_type', $normType)
                ->first();
            if ($base && !empty($base->ac_no) && strlen(trim($base->ac_no)) === 12 && str_starts_with(trim($base->ac_no), $prefix)) {
                $baseAcNo = trim($base->ac_no);
                $suffix = substr($baseAcNo, 4);
                $oppositeTable = ($normType === 'driver') ? 'tj_user_app' : 'tj_conducteur';
                $collides = DB::table($oppositeTable)->where('ac_no', 'LIKE', '%' . $suffix)->exists();
                if (!$collides) {
                    DB::table($table)->where('id', $userId)->update(['ac_no' => $baseAcNo]);
                    return $baseAcNo;
                }
            }
        }

        return self::generateForUser($userId, $normType);
    }

    /**
     * Fix all pocket number collisions between customers and drivers across the database.
     */
    public static function fixAllCollisions(): array
    {
        $updated = [];

        // 1. Sync any missing ac_no in tj_user_app from common_user_base
        if (Schema::hasTable('common_user_base')) {
            $usersMissing = DB::table('tj_user_app')
                ->where(function ($q) {
                    $q->whereNull('ac_no')->orWhere('ac_no', '')->orWhereRaw('LENGTH(ac_no) != 12');
                })
                ->get(['id']);

            foreach ($usersMissing as $u) {
                $base = DB::table('common_user_base')
                    ->where('user_id', $u->id)
                    ->where('user_type', 'customer')
                    ->first();
                if ($base && !empty($base->ac_no) && strlen(trim($base->ac_no)) === 12) {
                    DB::table('tj_user_app')->where('id', $u->id)->update(['ac_no' => trim($base->ac_no)]);
                }
            }

            // Sync missing in tj_conducteur
            $driversMissing = DB::table('tj_conducteur')
                ->where(function ($q) {
                    $q->whereNull('ac_no')->orWhere('ac_no', '')->orWhereRaw('LENGTH(ac_no) != 12');
                })
                ->get(['id']);

            foreach ($driversMissing as $d) {
                $base = DB::table('common_user_base')
                    ->where('user_id', $d->id)
                    ->where('user_type', 'driver')
                    ->first();
                if ($base && !empty($base->ac_no) && strlen(trim($base->ac_no)) === 12) {
                    DB::table('tj_conducteur')->where('id', $d->id)->update(['ac_no' => trim($base->ac_no)]);
                }
            }
        }

        // 2. Find colliding suffixes between tj_user_app and tj_conducteur
        $collisions = DB::select("
            SELECT u.id as user_id, u.ac_no as user_ac_no, u.creer as user_creer,
                   d.id as driver_id, d.ac_no as driver_ac_no, d.creer as driver_creer,
                   SUBSTRING(u.ac_no, 5) as suffix
            FROM tj_user_app u
            JOIN tj_conducteur d ON SUBSTRING(u.ac_no, 5) = SUBSTRING(d.ac_no, 5)
            WHERE u.ac_no IS NOT NULL AND LENGTH(u.ac_no) = 12
              AND d.ac_no IS NOT NULL AND LENGTH(d.ac_no) = 12
        ");

        foreach ($collisions as $col) {
            // Compare created dates: the later-created user gets re-assigned a new unique pocket number
            $userCreer   = strtotime($col->user_creer ?? '2026-01-01');
            $driverCreer = strtotime($col->driver_creer ?? '2026-01-01');

            if ($userCreer >= $driverCreer) {
                // User was created after or at same time -> reassign user
                $oldAcNo = $col->user_ac_no;
                $newAcNo = self::generateForUser($col->user_id, 'customer');

                if (Schema::hasTable('tj_transaction')) {
                    DB::table('tj_transaction')->where('ac_no', $oldAcNo)->update(['ac_no' => $newAcNo]);
                }
                if (Schema::hasTable('tbl_earning')) {
                    DB::table('tbl_earning')->where('ac_no', $oldAcNo)->update(['ac_no' => $newAcNo]);
                }

                $updated[] = [
                    'role' => 'customer',
                    'id' => $col->user_id,
                    'old_ac_no' => $oldAcNo,
                    'new_ac_no' => $newAcNo,
                ];
            } else {
                // Driver was created after -> reassign driver
                $oldAcNo = $col->driver_ac_no;
                $newAcNo = self::generateForUser($col->driver_id, 'driver');

                if (Schema::hasTable('tj_conducteur_transaction')) {
                    DB::table('tj_conducteur_transaction')->where('ac_no', $oldAcNo)->update(['ac_no' => $newAcNo]);
                }
                if (Schema::hasTable('tbl_earning')) {
                    DB::table('tbl_earning')->where('ac_no', $oldAcNo)->update(['ac_no' => $newAcNo]);
                }

                $updated[] = [
                    'role' => 'driver',
                    'id' => $col->driver_id,
                    'old_ac_no' => $oldAcNo,
                    'new_ac_no' => $newAcNo,
                ];
            }
        }

        // 3. Also check common_user_base for any lingering colliding suffixes
        if (Schema::hasTable('common_user_base')) {
            $baseCollisions = DB::select("
                SELECT c1.id as id1, c1.user_id as uid1, c1.user_type as utype1, c1.ac_no as ac1,
                       c2.id as id2, c2.user_id as uid2, c2.user_type as utype2, c2.ac_no as ac2
                FROM common_user_base c1
                JOIN common_user_base c2 ON SUBSTRING(c1.ac_no, 5) = SUBSTRING(c2.ac_no, 5) AND c1.id < c2.id
                WHERE LENGTH(c1.ac_no) = 12 AND LENGTH(c2.ac_no) = 12
            ");

            foreach ($baseCollisions as $bc) {
                // Reassign c2
                $newAcNo = self::generateForUser((int)$bc->uid2, $bc->utype2);

                $updated[] = [
                    'role' => $bc->utype2,
                    'id' => $bc->uid2,
                    'old_ac_no' => $bc->ac2,
                    'new_ac_no' => $newAcNo,
                ];
            }
        }

        return $updated;
    }
}
