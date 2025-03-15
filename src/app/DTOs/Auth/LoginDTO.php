<?php

namespace App\DTOs\Auth;

class LoginDTO
{
    public $email;
    public $password;
    public $forceLogin;

    public function __construct(string $email, string $password, string $forceLogin = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->forceLogin = $forceLogin;
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'force_login' => $this->forceLogin,
        ];
    }
}
