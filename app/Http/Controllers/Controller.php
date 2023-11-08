<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

        //respone success
        public function Success(mixed $data, string $message, int $statusCode)
        {
            return response()->json([
                'message'  => $message,
                'data' => $data
            ], $statusCode);
        }
    
        //respone fails
        public function Fails(mixed $data, string $message, int $statusCode)
        {
            return response()->json([
                'message'  => $message,
                'data' => $data
            ], $statusCode);
        }
    
}
