<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    /**
     * Verify email by OTP.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verifyEmailByLink(Request $request): JsonResponse
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return $this->Res(null, 'Email has been verified!', 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->Res($this->createToken($user), 'Verification Successfully!', 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verifyEmailByOTP(Request $request): JsonResponse
    {
        // Validate the request
        $this->validate($request, [
            'email' => 'required|string|email|max:255',
            'otp' => 'required|string|min:6|max:6', // Adjust the length as needed
        ]);

        $user = User::where('email', $request['email'])->first();

        if (!$user) {
            return $this->Res(null, 'Account not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->Res(null, 'Email has been verified!', 200);
        }

        // Check if the provided OTP matches the one stored in the database
        if ($user->otp == $request['otp'] &&
            $user->otp_expired_at > now() &&
            $user->otp_status == 'pending'
        ) {
            $user->markEmailAsVerified();
            $user->otp_status = 'verified';
            $user->save();

            // Create and set the expiration time for the access token (optional)
            $accessToken = $this->createToken($user);

            return $this->Res($accessToken, 'Verification Successfully!', 200);
        }

        return $this->Res(null, "Code incorrect", 403);
    }

    public function resendEmail(Request $request): Response
    {
        $request->user()->sendEmailVerificationNotification();

        return Response([
            'status' => 200,
            'message' => 'already resend email',
            'data' => null
        ], 200);
    }

    /**
     * Resend the OTP email.
     *
     * @param $email
     * @return void
     */
    private function resendOTP($email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            if ($user->otp_expired_at < now()) {
                $user->otp = $this->generateOtp();
                $user->otp_expired_at = now()->addHours();
                $user->save();
                //send code to mail
                $user->sendEmailVerificationNotification();

                $this->Res(null, "The code has been sent.", 200);
            } else {
                $this->Res(null, "The code is already send, please wait 1 minute for resend code.", 200);
            }

        } else {
            $this->Res(null, 'Account not exist!', 403);
        }
    }

    /**
     * Generate a random OTP.
     *
     * @param int $length
     * @return string
     */
    function generateOtp(int $length = 6): string
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
