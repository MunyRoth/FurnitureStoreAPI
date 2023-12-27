<x-mail::message>
# Hello, {{ $user->name }}!
Please use the following OTP code to verify your email address.
<div style="text-align: center;">OTP Code: <strong>{{ $otp }}</strong></div><br>

You can also click the button below to verify your email address:
<x-mail::button :url="$url">
Confirm Email Address
</x-mail::button>

If you did not register an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
