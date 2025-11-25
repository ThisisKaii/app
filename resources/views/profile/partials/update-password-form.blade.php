<section class="profile-card space-y-6">
    <header>
        <h2 class="profile-header">{{ __('Update Password') }}</h2>
        <p class="profile-subtitle">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="profile-label" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="profile-input" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="profile-error" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" class="profile-label" />
            <x-text-input id="update_password_password" name="password" type="password" class="profile-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="profile-error" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="profile-label" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="profile-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="profile-error" />
        </div>

        <div class="profile-form-row">
            <x-primary-button class="profile-button">{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="profile-subtitle">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
