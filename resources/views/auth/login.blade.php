<x-guest-layout>
    <div class="auth-card">
        <h2 class="auth-title">{{ __('Welcome Back') }}</h2>

        <x-auth-session-status class="auth-status" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="auth-form-group">
                <label for="email" class="auth-label">{{ __('Email') }}</label>
                <input 
                    id="email" 
                    class="auth-input" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="you@example.com" />
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="auth-form-group">
                <label for="password" class="auth-label">{{ __('Password') }}</label>
                <input 
                    id="password" 
                    class="auth-input" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••" />
                @error('password')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="auth-remember-row">
                <label for="remember_me" class="auth-checkbox-label">
                    <input 
                        id="remember_me" 
                        type="checkbox" 
                        name="remember" 
                        class="auth-checkbox" />
                    <span class="auth-checkbox-text">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <!-- Login Button -->
            <div class="auth-form-group">
                <button type="submit" class="auth-button">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="auth-divider">
                <span class="auth-divider-text">{{ __("Don't have an account?") }}</span>
                <a href="{{ route('register') }}" class="auth-link" style="margin-left: 0.25rem; font-weight: 600;">
                    {{ __('Register') }}
                </a>
            </div>
        @endif
    </div>
</x-guest-layout>