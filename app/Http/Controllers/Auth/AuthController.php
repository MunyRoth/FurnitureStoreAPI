<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request) {
        // Validate the request
        $this->validate($request, [
            'name' =>'required|string',
            'email' =>'required|string|email|max:255|unique:users',
            'password' => 'required|min:8',
        ]);

        // Check if email is registered
        if (User::where('email', $request->email)->exists()) {
            return $this->Res(null, 'Email is already registered', 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Create token
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'user' => $user,
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ], 201);
    }

    // Login a user
    public function login(Request $request) {
        // Validate the request
        $this->validate($request, [
            'email' =>'required|string|email|max:255',
            'password' => 'required|min:8',
        ]);

        // Check email and password
        if (!auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->Res(null, 'Invalid credentials', 400);
        }

        // Create token
        $tokenResult = auth()->user()->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'user' => auth()->user(),
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ], 200);
    }
}
