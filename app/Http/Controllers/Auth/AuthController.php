<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class AuthController extends Controller
{
    protected $guard;

    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
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
            'password' => Hash::make($request->password)
        ]);

        // Create and set the expiration time for the access token
        $data = $this->createToken($user);

        return $this->Res($data, 'Registered successfully', 201);
    }

    /**
     * Login a user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
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

        // Create and set the expiration time for the access token
        $data = $this->createToken($this->guard->user());

        return $this->Res($data, "Logged in successfully", 200);
    }

    /**
     * store a user.
     *
     * @param Request $request
     */
    public function store(UpdateProfileRequest $request)
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Revoke the user's access token
        auth()->user()->token()->revoke();

        return $this->Res(null, 'Logged out successfully', 200);
    }



    /**
     * Create and set the expiration time for the access token.
     *
     * @param User $user
     * @return array
     */
    private function createToken(User $user)
    {
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return [
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
        ];
    }

    public function getProfile()
    {
        $user = Auth::guard('api')->user();

        return $this->Res($user, "Got data success", 200);
    }
}
