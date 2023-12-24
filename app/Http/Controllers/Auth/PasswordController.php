<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        if ($validator->fails()){
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

        if ($validator->fails()){
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            return match ($status) {
                Password::RESET_LINK_SENT => $this->Res(null, trans($status)),
                Password::INVALID_USER => $this->Res(null, trans($status), 400),
                default => $this->Res(null, 'Send reset link successfully'),
            };

        } catch (\Swift_TransportException|Exception $ex) {
            return $this->Res(null, $ex->getMessage(), 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // validate the request
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()){
            return $this->Res(null, $validator->errors()->first(), 400);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return $this->Res(null, 'Password reset successfully');
        }

        return $this->Res(null, __($status), 500);
    }
}
