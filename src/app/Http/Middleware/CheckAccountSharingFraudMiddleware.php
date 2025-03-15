<?php

namespace App\Http\Middleware;

use App\Services\CheckLocationFraudServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountSharingFraudMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAccessTokenFraud = $this->checkAccessToken($request);
        if (!$isAccessTokenFraud) {
            return response()->json([
                'data' => '',
                'message' => 'token het han'
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $isLocationFraud = $this->checkLocation($request);
        if ($isLocationFraud) {
            return response()->json('2 thiet bi dang nhap cung luc');
        }

        return $next($request);
    }

    /**
     * Check access token fraud.
     */
    private function checkAccessToken($request)
    {
        $userId = Auth::id();
        $keyCache = 'user_' . $userId . '_access_token';
        $accessTokens = Cache::get($keyCache) ?? collect();

        return $accessTokens->where('expired', '>', now()->timestamp)->where('access_token', $request->bearerToken())->count();
    }

    /**
     * Check location fraud.
     */
    private function checkLocation($request)
    {
        $checkLocationFraudService = app(CheckLocationFraudServiceInterface::class);
        return $checkLocationFraudService->check($request);
    }
}
