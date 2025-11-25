@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'profile-input block w-full rounded-md border border-gray-700 bg-gray-800 text-gray-200 placeholder-gray-500 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-25 shadow-sm'
    ]) }}
>
