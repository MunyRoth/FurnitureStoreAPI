<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function changePassword(Request $request): Response
    {
        // get user id
        $userid = Auth::guard('api')->user()->id;

        // validate the request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()){
            return Response([
                'status' => 400,
                'message' => $validator->errors()->first(),
                'data' => ''
            ], 400);
        }

        try {
            if (!(Hash::check(request('current_password'), Auth::user()->password))) {
                return Response([
                    'status' => 400,
                    'message' => 'check your old password',
                    'data' => ''
                ], 400);
            } else if ((Hash::check(request('password'), Auth::user()->password))) {
                return Response([
                    'status' => 400,
                    'message' => 'please enter a password which is not similar then current password',
                    'data' => ''
                ], 400);
            } else {
                User::where('id', $userid)->update(['password' => Hash::make($request->password_confirmation)]);
                return Response([
                    'status' => 200,
                    'message' => 'password updated successfully',
                    'data' => ''
                ], 200);
            }
        } catch (Exception $ex) {
            return Response([
                'status' => 500,
                'message' => $ex->errorInfo[2] ?? $ex->getMessage(),
                'data' => ''
            ], 500);
        }
    }

    public function forgotPassword(Request $request): Response
    {
        // validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()){
            return Response([
                'status' => 400,
                'message' => $validator->errors()->first(),
                'data' => ''
            ], 400);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            return match ($status) {
                Password::RESET_LINK_SENT => Response([
                    'status' => 200,
                    'message' => trans($status),
                    'data' => ''
                ], 200),
                Password::INVALID_USER => Response([
                    'status' => 400,
                    'message' => trans($status),
                    'data' => ''
                ], 400),
                default => Response([
                    'status' => 200,
                    'message' => 'send reset link successfully',
                    'data' => ''
                ], 200),
            };

        } catch (\Swift_TransportException|Exception $ex) {
            return Response([
                'status' => 500,
                'message' => $ex->getMessage(),
                'data' => ''
            ], 500);
        }
    }

    public function resetPassword(Request $request): Response
    {
        // validate the request
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()){
            return Response([
                'status' => 400,
                'message' => $validator->errors()->first(),
                'data' => ''
            ], 400);
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
            return Response([
                'status' => 200,
                'message'=> 'password reset successfully',
                'data' => ''
            ], 200);
        }

        return Response([
            'status' => 500,
            'message'=> __($status),
            'data' => ''
        ], 500);
    }
}
