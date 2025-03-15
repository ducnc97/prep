<?php

namespace App\Services\RepositoryInterface;

use App\DTOs\Auth\LoginDTO;

interface AuthRepositoryInterface
{
    public function attempt(LoginDTO $credentials): string;
}
