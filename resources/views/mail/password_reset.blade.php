<x-mail::message>
# Hello, {{ $user->name }}!
This email was sent to you because we received a password reset request for your account.
Please use the following OTP code to reset your password.
<div style="text-align: center;">OTP Code: <strong>{{ $otp }}</strong></div><br>

You can also click the button below to reset your password:
<x-mail::button :url="$url">
Reset Password
</x-mail::button>

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
