<x-guest-layout>
    <div class="auth-card">
        <h2 class="auth-title">{{ __('Forgot Password') }}</h2>

        <div class="auth-description">
            {{ __('Forgot your password? No problem. Enter your email and we will send a password reset link.') }}
        </div>

        <x-auth-session-status class="auth-status" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="auth-form-group">
                <label for="email" class="auth-label">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="auth-input" placeholder="you@example.com" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-form-group">
                <button type="submit" class="auth-button">{{ __('Email Password Reset Link') }}</button>
            </div>
        </form>

        <div class="auth-divider">
            <a href="{{ route('login') }}" class="auth-link">{{ __('Back to login') }}</a>
        </div>
    </div>
</x-guest-layout>
