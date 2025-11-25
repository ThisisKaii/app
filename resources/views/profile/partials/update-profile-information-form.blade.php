<section class="profile-card space-y-6">
    <header>
        <h2 class="profile-header">{{ __('Profile Information') }}</h2>
        <p class="profile-subtitle">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" class="profile-label" />
            <x-text-input id="name" name="name" type="text" class="profile-input" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="profile-error" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="profile-label" />
            <x-text-input id="email" name="email" type="email" class="profile-input" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="profile-error" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="profile-subtitle mt-2">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="underline text-sm text-[#58a6ff] hover:text-[#79c0ff] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="profile-success">{{ __('A new verification link has been sent to your email address.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="profile-form-row">
            <x-primary-button class="profile-button">{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
