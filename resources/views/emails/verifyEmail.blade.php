@component('mail::message')
# Verify Email Address

Please click the button below to verify your email address.

@component('mail::button', ['url' => env('VUE_FRONT_URL').'?token='.$user['verification_token']])
Verify Email Address
@endcomponent

In case if this link don't work then please copy below link and paste it in your browser.

<a href="{{ env('VUE_FRONT_URL').'?token='.$user['verification_token'] }}">
    {{ env('VUE_FRONT_URL').'?token='.$user['verification_token'] }}
</a>

<code>Please note that this link is valid only for {{ env('EMAIL_TOKEN_EXPIRY_DAYS') }} days. If not verified within {{ env('EMAIL_TOKEN_EXPIRY_DAYS') }} days account will automatically delete !</code>

If you did not create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
