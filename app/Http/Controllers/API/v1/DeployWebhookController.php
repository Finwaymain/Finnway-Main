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
        
        $envPrefix = "export GIT_CONFIG_GLOBAL=/tmp/.gitconfig; git config --global --add safe.directory '*' 2>/dev/null || true; ";
        
        if (file_exists($deployScript)) {
            $output = shell_exec("{$envPrefix} bash {$deployScript} 2>&1");
        } elseif (file_exists($varDeployScript)) {
            $output = shell_exec("{$envPrefix} bash {$varDeployScript} 2>&1");
        } else {
            $cmd = "{$envPrefix} cd " . base_path() . " && git -c safe.directory='*' fetch origin main 2>&1 && git -c safe.directory='*' reset --hard origin/main 2>&1 && php artisan optimize:clear 2>&1 && php artisan optimize 2>&1";
            $output = shell_exec($cmd);
        }

        return response()->json([
            'success' => true,
            'message' => 'Auto deployment triggered successfully.',
            'output'  => $output,
        ]);
    }
}
