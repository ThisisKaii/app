<x-app-layout>
    <x-slot name="header">
        <header class="bg-gray-900 dark:bg-gray-900 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold text-white dark:text-gray-100 leading-tight">
                    {{ __('Profile') }}
                </h2>
            </div>
        </header>
    </x-slot>

    <main class="bg-gray-900 dark:bg-gray-900 min-h-screen py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Update Profile Card -->
            <div class="p-4 sm:p-8 bg-gray-800 dark:bg-gray-800 shadow rounded-lg">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Update Password Card -->
            <div class="p-4 sm:p-8 bg-gray-800 dark:bg-gray-800 shadow rounded-lg">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Delete User Card -->
            <div class="p-4 sm:p-8 bg-gray-800 dark:bg-gray-800 shadow rounded-lg">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </main>
</x-app-layout>
