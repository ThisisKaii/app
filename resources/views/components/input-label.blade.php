@props(['value'])

<label 
    {{ $attributes->merge([
        'class' => 'profile-label block font-medium text-sm text-gray-300'
    ]) }}
>
    {{ $value ?? $slot }}
</label>
