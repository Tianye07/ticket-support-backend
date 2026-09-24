<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class NotFoundTicketException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'code' => '0001',
            'success' => false,
            'message' => 'Ticket not found.',
        ], 404);
    }
}
