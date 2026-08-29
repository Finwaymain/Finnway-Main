// Debug: show what's in the referral table for user_id 103 and 117
echo "=== Referral table rows for recently created users ===\n";
$rows = DB::table('referral')->whereIn('user_id', [101, 102, 103, 104, 116, 117])->get();
foreach ($rows as $r) {
    echo "  id={$r->id} user_id={$r->user_id} referral_code={$r->referral_code} referral_by_id={$r->referral_by_id} user_type={$r->user_type}\n";
}

echo "\n=== For user_id=103 (User B), what does the query return? ===\n";
$id = 103;
$refRows = DB::table('referral')
    ->where(function($q) use ($id) {
        $q->where('referral_by_id', $id)
          ->orWhere('referral_by_id', (string)$id);
    })
    ->where('user_id', '!=', $id)
    ->get();
echo "  referral_by_id=$id query returned " . count($refRows) . " rows:\n";
foreach ($refRows as $r) {
    echo "  -> id={$r->id} user_id={$r->user_id} referral_code={$r->referral_code}\n";
}

echo "\n=== ref_by column check for user_id=103 ===\n";
$ubRow = DB::table('tj_user_app')->where('id', 103)->first();
echo "  tj_user_app.ref_by = " . ($ubRow->ref_by ?? 'NULL') . "\n";
echo "  tj_user_app.referral_code = " . ($ubRow->referral_code ?? 'NULL') . "\n";
