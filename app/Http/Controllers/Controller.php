<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
        public function Res(mixed $data, string $message, int $statusCode): JsonResponse
        {
            return response()->json([
                'message'  => $message,
                'data' => $data
            ], $statusCode);
        }
}
