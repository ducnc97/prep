<?php

namespace App\Services;

use Illuminate\Http\Request;

interface CheckLocationFraudServiceInterface
{
    public function checkFraud(Request $request): bool;
    public function isFraud(array $params): bool;
}
