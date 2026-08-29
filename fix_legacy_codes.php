

$fixed = 0;
$skipped = 0;

// Fix all records in referral table that have FIINU or FIINB prefix
$referralRows = DB::table('referral')->get();

echo "Found " . count($referralRows) . " referral records\n";

foreach ($referralRows as $row) {
    $code = $row->referral_code ?? '';
    $isOldFormat = preg_match('/^(FIINU|FIINB)/i', $code);

    if ($isOldFormat) {
        // Correct code = FIIN + referral.id padded to 6 digits
        $newCode = 'FIIN' . str_pad((string)$row->id, 6, '0', STR_PAD_LEFT);

        DB::table('referral')->where('id', $row->id)->update([
            'referral_code' => $newCode,
        ]);

        // Also fix in tj_user_app
        if ($row->user_type !== 'driver' && Schema::hasColumn('tj_user_app', 'referral_code')) {
            DB::table('tj_user_app')->where('id', $row->user_id)->update(['referral_code' => $newCode]);
        }

        // Also fix in tj_conducteur
        if ($row->user_type === 'driver' && Schema::hasColumn('tj_conducteur', 'referral_code')) {
            DB::table('tj_conducteur')->where('id', $row->user_id)->update(['referral_code' => $newCode]);
        }

        echo "  Fixed: User #{$row->user_id} [{$row->user_type}]: $code -> $newCode\n";
        $fixed++;
    } else {
        $skipped++;
    }
}

// Also fix users in tj_user_app who have FIINU code but no referral table entry
$orphanedUsers = DB::table('tj_user_app')
    ->whereRaw("referral_code LIKE 'FIINU%' OR referral_code LIKE 'fiinu%'")
    ->whereNotIn('id', function($q) {
        $q->select('user_id')->from('referral');
    })
    ->get();

echo "\nOrphaned users (in tj_user_app but not in referral table): " . count($orphanedUsers) . "\n";

foreach ($orphanedUsers as $user) {
    $refId = DB::table('referral')->insertGetId([
        'user_id'       => $user->id,
        'user_type'     => 'customer',
        'referral_code' => 'TEMP',
        'code_used'     => 'false',
        'creer'         => now()->toDateTimeString(),
    ]);
    $newCode = 'FIIN' . str_pad((string)$refId, 6, '0', STR_PAD_LEFT);
    DB::table('referral')->where('id', $refId)->update(['referral_code' => $newCode]);
    DB::table('tj_user_app')->where('id', $user->id)->update(['referral_code' => $newCode]);
    echo "  Created: User #{$user->id}: {$user->referral_code} -> $newCode\n";
    $fixed++;
}

// Same for orphaned drivers
$orphanedDrivers = DB::table('tj_conducteur')
    ->whereRaw("referral_code LIKE 'FIINB%' OR referral_code LIKE 'fiinb%'")
    ->whereNotIn('id', function($q) {
        $q->select('user_id')->from('referral');
    })
    ->get();

echo "\nOrphaned drivers (in tj_conducteur but not in referral table): " . count($orphanedDrivers) . "\n";

foreach ($orphanedDrivers as $driver) {
    $refId = DB::table('referral')->insertGetId([
        'user_id'       => $driver->id,
        'user_type'     => 'driver',
        'referral_code' => 'TEMP',
        'code_used'     => 'false',
        'creer'         => now()->toDateTimeString(),
    ]);
    $newCode = 'FIIN' . str_pad((string)$refId, 6, '0', STR_PAD_LEFT);
    DB::table('referral')->where('id', $refId)->update(['referral_code' => $newCode]);
    DB::table('tj_conducteur')->where('id', $driver->id)->update(['referral_code' => $newCode]);
    echo "  Created: Driver #{$driver->id}: {$driver->referral_code} -> $newCode\n";
    $fixed++;
}

echo "\n========================================\n";
echo "DONE: Fixed=$fixed, Already correct=$skipped\n";
echo "All referral codes are now FIIN+6digits\n";
echo "========================================\n";
