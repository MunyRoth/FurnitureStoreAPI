<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected Guard $guard;

    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        // Validate the request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string|email|max:255',
            'password' => 'required|min:8',
        ]);

        // Check if email is already registered
        if (User::where('email', $request->email)->exists()) {
            return $this->Res(null, 'Email is already registered', 400);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => $this->generateOtp(),
        ]);

        // send confirmation email
        event(new Registered($user));

        return $this->Res(null, 'Registered successfully, Please check email for verify!', 201);
    }

    /**
     * Login a user.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Validate the request
        $this->validate($request, [
            'email' => 'required|string|email|max:255',
            'password' => 'required|min:8'
        ]);

        // Check email and password
        if (!auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->Res(null, "Invalid credentials", 400);
        }

        // Check if the email is verified
        if (!auth()->user()->hasVerifiedEmail()) {
            return $this->Res(null, "Email not verified", 403);
        }

        // Create and set the expiration time for the access token
        $data = $this->createToken($this->guard->user());

        return $this->Res($data, "Logged in successfully", 200);
    }

    /**
     * store a user.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function store(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        try {
            if ($request->file('avatar')) {
                // Upload the image to Cloudinary
                $avatar = Cloudinary::upload($request->file('avatar')->getRealPath(), [
                    'folder' => 'FurnitureStore'
                ])->getSecurePath();
                $user->avatar = $avatar;
            }
            // Update user name if it's present
            if ($request->name) {
                $user->name = $request->name;
            }
            $user->save();
            return $this->Res(null, "Update Profile Successfully", 200);
        } catch (\Exception $e) {
            return $this->Res(null, $e->getMessage(), 500);
        }
    }

    /**
     * Logout a user.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        // Revoke the user's access token
        auth()->user()->token()->revoke();

        return $this->Res(null, 'Logged out successfully', 200);
    }

    public function getProfile(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return $this->Res($user, "Got data success", 200);
    }
}
