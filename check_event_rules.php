$fixed = 0;
$skipped = 0;

echo "=== CHECKING api_key_settings for event rules ===\n";

$rules = DB::table('api_key_settings')
    ->where('key_name', 'like', 'event_rule_%')
    ->orderBy('key_name')
    ->get();

if ($rules->isEmpty()) {
    echo "NO event rules found in api_key_settings!\n";
    echo "Admin has not saved rules yet, or they are stored differently.\n\n";
    
    echo "=== All api_key_settings rows ===\n";
    $all = DB::table('api_key_settings')->limit(30)->get();
    foreach ($all as $r) {
        echo "  key_name={$r->key_name}  key_value={$r->key_value}\n";
    }
} else {
    echo "Found " . count($rules) . " event rule entries:\n";
    foreach ($rules as $r) {
        echo "  {$r->key_name} = {$r->key_value}\n";
    }
}

echo "\n=== Computing what creditReferralReward would return ===\n";
$events = ['app_install', 'registration'];
$total = 0;
foreach ($events as $evt) {
    $enabled = DB::table('api_key_settings')->where('key_name', "event_rule_{$evt}_enable")->value('key_value');
    $type    = DB::table('api_key_settings')->where('key_name', "event_rule_{$evt}_type")->value('key_value') ?? 'flat';
    $value   = DB::table('api_key_settings')->where('key_name', "event_rule_{$evt}_value")->value('key_value') ?? '0';
    $val     = floatval(preg_replace('/[^0-9.]/', '', (string)$value));
    echo "  [$evt] enabled=$enabled type=$type value=$value parsed_val=$val\n";
    if ($enabled !== '0' && $val > 0) {
        if (strtolower($type) === 'flat') {
            $total += $val;
        } else {
            $total += round((100.0 * $val) / 100, 2);
        }
    }
}
echo "  => Total reward would be: Rs.$total\n";

echo "\n=== tj_settings->referral_amount fallback ===\n";
$s = DB::table('tj_settings')->first();
echo "  referral_amount = " . ($s->referral_amount ?? 'NOT SET') . "\n";
