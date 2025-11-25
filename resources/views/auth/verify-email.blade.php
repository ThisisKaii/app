<x-guest-layout>
    <div class="auth-card">
        <h2 class="auth-title">{{ __('Verify Email') }}</h2>

        <div class="auth-description">
            {{ __('Thanks for signing up! Please verify your email address by clicking the link we sent. If you didn’t receive it, we will gladly send another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="auth-success">
                <p class="auth-success-text">
                    {{ __('A new verification link has been sent to your email address.') }}
                </p>
            </div>
        @endif

        <div class="auth-remember-row">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="auth-button">{{ __('Resend Verification Email') }}</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-link">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</x-guest-layout>
