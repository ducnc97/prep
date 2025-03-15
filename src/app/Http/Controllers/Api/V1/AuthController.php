<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    const LIMIT_DEVICE = 2;

    public function __construct(
        private AuthServiceInterface $authService,
    ) {}

    /**
     * Login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginDTO = new LoginDTO($request['email'], $request['password'], $request['force_login'] ?? null);
            $result = $this->authService->login($loginDTO);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->responseJson('', 'Error server.', 500);
        }

        return $this->responseJson($result['data'], $result['message'], $result['status']);
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken($request->bearerToken());
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->responseJson('', 'Error server.', 500);
        }

        return $this->responseJson($result);
    }
}
