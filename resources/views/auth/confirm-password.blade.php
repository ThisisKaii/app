<x-guest-layout>
    <div class="auth-card">
        <h2 class="auth-title">{{ __('Confirm Password') }}</h2>

        <div class="auth-description">
            {{ __('This is a secure area. Please confirm your password before continuing.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="auth-form-group">
                <label for="password" class="auth-label">{{ __('Password') }}</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="auth-input" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-form-group">
                <button type="submit" class="auth-button">{{ __('Confirm') }}</button>
            </div>
        </form>
    </div>
</x-guest-layout>
