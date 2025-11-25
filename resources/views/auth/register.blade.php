<x-guest-layout>
    <div class="auth-card">
        <h2 class="auth-title">{{ __('Create Account') }}</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="auth-form-group">
                <label for="name" class="auth-label">{{ __('Name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="auth-input" placeholder="John Doe" />
                <x-input-error :messages="$errors->get('name')" class="auth-error" />
            </div>

            <div class="auth-form-group">
                <label for="email" class="auth-label">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="auth-input" placeholder="you@example.com" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-form-group">
                <label for="password" class="auth-label">{{ __('Password') }}</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="auth-input" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-form-group">
                <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
            </div>

            <div class="auth-form-group">
                <button type="submit" class="auth-button">{{ __('Register') }}</button>
            </div>
        </form>

        <div class="auth-divider">
            <span class="auth-divider-text">{{ __('Already registered?') }}</span>
            <a href="{{ route('login') }}" class="auth-link">{{ __('Log in') }}</a>
        </div>
    </div>
</x-guest-layout>
