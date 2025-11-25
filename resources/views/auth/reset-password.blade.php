<x-guest-layout>
    <div class="auth-card">
        <h2 class="auth-title">{{ __('Reset Password') }}</h2>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="auth-form-group">
                <label for="email" class="auth-label">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="auth-input" placeholder="you@example.com" />
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
                <button type="submit" class="auth-button">{{ __('Reset Password') }}</button>
            </div>
        </form>
    </div>
</x-guest-layout>
