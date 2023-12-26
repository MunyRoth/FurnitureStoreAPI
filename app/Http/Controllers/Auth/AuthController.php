<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Providers
    private const PROVIDERS = [
        'google'
    ];

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
            'avatar' => 'image|mimes:jpeg,jpg,png,gif|max:8191',
            'name' => 'required|string',
            'email' => 'required|string|email|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if email is already registered
        if (User::where('email', $request->email)->exists()) {
            return $this->Res(null, 'Email is already registered', 409);
        }

        $avatar = null;
        if ($request->file('avatar') != null) {
            // Upload the image to Cloudinary
            $avatar = Cloudinary::upload($request->file('avatar')->getRealPath(), [
                'folder' => 'FurnitureStore'
            ])->getSecurePath();
        }

        // Create a new user
        $user = User::create([
            'avatar' => $avatar,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => $this->generateOtp(),
            'otp_expired_at' => now()->addMinutes(5), // Set OTP expiration time to 5 minutes
            'otp_status' => 'pending',
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
            return $this->Res(null, "Invalid credentials", 401);
        }

        // User
        $user = User::findOrFail(auth()->user()->id);

        // Check if the email is not verified
        if (!$user->hasVerifiedEmail()) {
            if ($user->otp_expired_at < now()) {
                $user->otp = $this->generateOtp();
                $user->otp_expired_at = now()->addMinutes(5);
                $user->save();
                $user->sendEmailVerificationNotification();
                return $this->Res(null, "Email not verified, email has been resend.", 403);
            }
            return $this->Res(null, "Email not verified", 403);
        }

        // Create and set the expiration time for the access token
        $data = $this->createToken($this->guard->user());

        return $this->Res($data, "Logged in successfully");
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
            return $this->Res($user, "Update Profile Successfully");
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

        return $this->Res(null, 'Logged out successfully');
    }

    public function getProfile(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return $this->Res($user, "Got data success");
    }

    /**
     * Social login:redirect to provider.
     *
     * @param $provider
     * @return RedirectResponse
     */
    public function redirectToProvider($provider): RedirectResponse
    {
        // check if provider exists
        if(!in_array($provider, self::PROVIDERS)){
            return redirect(env('FRONT_URL') . '/provider?status=404');
        }

        return redirect(Socialite::driver($provider)->stateless()->redirect()->getTargetUrl());
    }

    /**
     * Social login:handle provider callback.
     *
     * @param $provider
     * @return RedirectResponse
     */
    public function handleProviderCallback($provider): RedirectResponse
    {
        // check if provider exists
        if(!in_array($provider, self::PROVIDERS)){
            return redirect(env('FRONT_URL') . '/provider?status=404');
        }

        try {
            // get user from provider
            $providerUser = Socialite::driver($provider)->stateless()->user();

            // Check if user is already registered with this provider
            $user = User::where('provider_name', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();

            if ($user) {
                return redirect(env('FRONT_URL') . '/login?status=200&token='.$user->createToken(env('APP_NAME') . ' Token')->accessToken);
            }

            // Check if user is already registered with this email
            $userUpdate = User::where('email', $providerUser->getEmail())->first();
            if ($userUpdate) {
                // update user in database
                $userUpdate->update([
                    'provider_name' => $provider,
                    'provider_id' => $providerUser->getId(),
                    'avatar' => $providerUser->getAvatar(),
                    'name' => $providerUser->getName()
                ]);
            } else {
                // store user in database
                $userUpdate = User::create([
                    'provider_name' => $provider,
                    'provider_id' => $providerUser->getId(),
                    'avatar' => $providerUser->getAvatar(),
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                ]);
            }

            return redirect(env('FRONT_URL') . '/register?status=201&token='.$userUpdate->createToken(env('APP_NAME') . ' Token')->accessToken);
        } catch (Exception $ex) {
            return redirect(env('FRONT_URL') . '/error?status=500%error=' . $ex->getMessage());
        }
    }
}
