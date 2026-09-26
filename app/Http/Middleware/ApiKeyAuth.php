<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Language;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Request;
use DB;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */

    public function handle($request, Closure $next, $guard = null)
    {
        // Public customer food routes and webview finance portal routes do not require internal key
        if ($request->is('api/v1/food/customer/*') || $request->is('*/v1/food/customer/*') ||
            $request->is('api/v1/finance/*') || $request->is('*/v1/finance/*')) {
            return $next($request);
        }

        $apiKey = $request->header('apikey') ?: $request->query('apikey');
        $validKeys = [config('app.key'), 'f7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2', env('API_KEY')];
        if (empty($apiKey) || !in_array($apiKey, array_filter($validKeys))) {
            return BaseApiController::errorResponse([], 'Unauthorized', [], Response::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}