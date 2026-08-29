<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeployWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $deployScript = base_path('deploy.sh');
        $varDeployScript = '/var/www/fiinway-backend/deploy.sh';
        
        if (file_exists($deployScript)) {
            $output = shell_exec("bash {$deployScript} 2>&1");
        } elseif (file_exists($varDeployScript)) {
            $output = shell_exec("bash {$varDeployScript} 2>&1");
        } else {
            $cmd = "cd " . base_path() . " && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1 && php artisan optimize:clear 2>&1 && php artisan optimize 2>&1";
            $output = shell_exec($cmd);
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto deployment triggered successfully.',
            'output'  => $output,
        ]);
    }
}
