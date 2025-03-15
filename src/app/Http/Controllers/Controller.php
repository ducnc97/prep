<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function responseJson(mixed $data = null, mixed $message = null, $status = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $status);
    }
}
