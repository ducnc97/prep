<?php

namespace App\Services;

use App\DTOs\Auth\LoginDTO;
use App\Services\RepositoryInterface\AuthRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthService implements AuthServiceInterface
{
    const LIMIT_DEVICE = 2;

    public function __construct(private AuthRepositoryInterface $authRepo) {}

    /**
     * Login.
     */
    public function login(LoginDTO $credentials): array
    {
        // check email, password
        if (! $accessToken = $this->authRepo->attempt($credentials)) {
            return [
                'data' => '',
                'message' => 'Unauthorized',
                'status' => Response::HTTP_UNAUTHORIZED,
            ];
        }

        // get token from cache
        $accessTokens = $this->getAccessTokenWithinExpired();

        // not force login
        // check login limit device
        $isLimitDevice = $this->isLimitDevice($accessTokens);
        if (!$credentials->forceLogin && $isLimitDevice) {
            return [
                'data' => '',
                'message' => 'Limit device.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ];
        }

        // save access token
        $this->saveAccessToken($accessTokens, $accessToken);

        // create refresh token
        $refreshToken = Auth::setTTL(config('jwt.refresh_ttl'))->login(Auth::user());

        return [
            'data' => $this->respondWithToken($accessToken, $refreshToken),
            'message' => '',
            'status' => Response::HTTP_OK,
        ];
    }

    public function refreshToken(string $refreshToken): array
    {
        $accessToken = Auth::tokenById(Auth::id());

        // get token within expired from cache
        $accessTokens = $this->getAccessTokenWithinExpired();

        // save access token
        $this->saveAccessToken($accessTokens, $accessToken);

        return [
            'data' => $this->respondWithToken($accessToken, $refreshToken),
            'message' => '',
            'status' => Response::HTTP_OK,
        ];
    }

    /**
     * Check limit device.
     */
    private function isLimitDevice(Collection $accessTokens): bool
    {
        if ($accessTokens?->count() >= self::LIMIT_DEVICE) {
            return true;
        }

        return false;
    }

    /**
     * Save access_token to cache.
     */
    private function saveAccessToken($accessTokens, $accessToken)
    {
        $userId = Auth::id();
        $keyCache = 'user_' . $userId . '_access_token';

        // get by limit device
        $valueCache = $accessTokens->push([
            'access_token' => $accessToken,
            'expired' => now()->addSeconds(config('jwt.ttl') * 60)->timestamp,
        ])->sortByDesc('expired')->take(2)->values();

        // save cache
        $accessTokens = Cache::put($keyCache, $valueCache, config('jwt.ttl') * 60);
    }

    /**
     * Get access_token within expired from cache.
     */
    private function getAccessTokenWithinExpired(): Collection
    {
        $userId = Auth::id();
        $keyCache = 'user_' . $userId . '_access_token';
        $accessTokens = Cache::get($keyCache) ?? collect();
        $accessTokenWithinExpired = $accessTokens->where('expired', '>', now()->timestamp) ?? collect();

        return $accessTokenWithinExpired;
    }

    /**
     * Get the token array structure.
     */
    private function respondWithToken(string $accessToken, string $refreshToken): array
    {
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }
}
