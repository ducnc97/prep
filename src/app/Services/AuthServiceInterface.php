<?php

namespace App\Services;

use App\DTOs\Auth\LoginDTO;

interface AuthServiceInterface
{
    public function login(LoginDTO $request): array;
    public function refreshToken(string $refreshToken): array;
}
