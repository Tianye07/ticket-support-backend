<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function responseSuccess($data): JsonResponse
    {
        return response()->json([
            'code' => '0',
            'success' => true,
            'data' => $data,
        ]);
    }
}
