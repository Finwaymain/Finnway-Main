<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\API\v1\AppFeatureAPIController;

$controller = new AppFeatureAPIController();

$req = Request::create('/api/v1/referral/stats', 'GET', ['user_id' => '999334']);
$res = $controller->getReferralStats($req);

echo "Stats for 999334:\n";
print_r(json_decode($res->getContent(), true));

$reqHist = Request::create('/api/v1/referral/history', 'GET', ['user_id' => '999334']);
$resHist = $controller->getReferralHistory($reqHist);

echo "\nHistory for 999334:\n";
print_r(json_decode($resHist->getContent(), true));
