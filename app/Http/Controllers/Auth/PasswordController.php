<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function changePassword(Request $request): JsonResponse
    {
        // get user id
        $userid = Auth::guard('api')->user()->id;

        // validate the request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        try {
            if (!(Hash::check(request('current_password'), Auth::user()->password))) {
                return $this->Res(null, 'Incorrect current password', 422);
            } else if ((Hash::check(request('new_password'), Auth::user()->password))) {
                return $this->Res(null, 'New password should be different from the current password', 400);
            } else {
                User::where('id', $userid)->update(['password' => Hash::make($request->new_password)]);
                return $this->Res(null, 'Password updated successfully');
            }
        } catch (Exception $ex) {
            return $this->Res(null, $ex->errorInfo[2] ?? $ex->getMessage(), 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        // validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        try {
            // Generate and store OTP
            $otp = $this->generateOTP();
            Cache::put('password_reset_' . $request->email, $otp, now()->addMinutes(15));

            // Send password reset email
            $resetLink = route('password.check', ['otp' => $otp, 'email' => $request->email]);
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->Res(null, 'Account not found', 404);
            }

            Mail::to($request->email)->send(new PasswordResetMail($user, $otp, $resetLink));

            return $this->Res(null, 'Password reset email sent successfully');
        } catch (Exception $ex) {
            return $this->Res(null, $ex->getMessage(), 500);
        }
    }

    public function checkOtp(Request $request): JsonResponse | RedirectResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        // Validate OTP
        $storedOtp = Cache::get('password_reset_' . $request->email);

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return $this->Res(null, 'Invalid OTP', 422);
        }

        return redirect(env('FRONT_URL') . '/password/reset?email='.$request->email.'&otp='.$request->otp);
    }

    public function verifyOTP(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        // Validate OTP
        $storedOtp = Cache::get('password_reset_' . $request->email);

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return $this->Res(null, 'Invalid OTP', 422);
        }

        return $this->Res(null, 'OTP verified successfully');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'otp' => 'required|string|min:6|max:6',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        // Validate OTP
        $storedOtp = Cache::get('password_reset_' . $request->email);

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return $this->Res(null, 'Invalid OTP', 422);
        }

        // OTP is valid, proceed with password reset
        try {
            $user = User::where('email', $request->email)->first();

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Remove stored OTP after successful password reset
            Cache::forget('password_reset_' . $request->email);
            return $this->Res(null, 'Password reset successfully');
        } catch (Exception $ex) {
            return $this->Res(null, $ex->getMessage(), 500);
        }
    }
}
