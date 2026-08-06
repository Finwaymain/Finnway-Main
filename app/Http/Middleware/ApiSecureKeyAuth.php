<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Language;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Request;
use DB;

class ApiSecureKeyAuth
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
        $apiKey = $request->header('apikey') ?: $request->query('apikey');
        $accessToken = $request->header('accesstoken') ?: $request->query('accesstoken');

        if (empty($apiKey) || empty($accessToken) || $apiKey != config('app.key')) {
            return BaseApiController::errorResponse([], 'Unauthorized', [], Response::HTTP_UNAUTHORIZED);
        }

        $user = DB::table('users_access')->where('accesstoken', $accessToken)->first();
        if (!$user) {
            return BaseApiController::errorResponse([], 'Unauthorized', [], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}