<section class="profile-card space-y-6">
    <header>
        <h2 class="profile-header">{{ __('Delete Account') }}</h2>
        <p class="profile-subtitle">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="profile-button-secondary">
        {{ __('Delete Account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-6">
            @csrf
            @method('delete')

            <h2 class="profile-header">{{ __('Are you sure you want to delete your account?') }}</h2>
            <p class="profile-subtitle">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="profile-input w-3/4" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="profile-error" />
            </div>

            <div class="profile-form-row">
                <x-secondary-button x-on:click="$dispatch('close')" class="profile-button-secondary">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button class="profile-button">{{ __('Delete Account') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
