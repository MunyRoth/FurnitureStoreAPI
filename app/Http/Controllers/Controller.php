<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
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
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Create and set the expiration time for the access token.
     *
     * @param User $user
     * @return array
     */
    public function createToken(User $user): array
    {
        $tokenResult = $user->createToken(env('APP_NAME') . ' Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks();
        $token->save();

        return [
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
        ];
    }

    /**
     * Generate a random OTP.
     *
     * @param int $length
     * @return string
     */
    public function generateOtp(int $length = 6): string
    {
        // Generate a random string of specified length
        $characters = '0123456789';
        $otp = '';

        for ($i = 0; $i < $length; $i++) {
            $otp .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $otp;
    }
}
