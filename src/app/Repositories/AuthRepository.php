<?php

namespace App\Repositories;

use App\DTOs\Auth\LoginDTO;
use App\Services\RepositoryInterface\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthRepository implements AuthRepositoryInterface
{
    public function attempt(LoginDTO $credentials): string
    {
        return Auth::attempt(['email' => $credentials->email, 'password' => $credentials->password]);
    }
}
