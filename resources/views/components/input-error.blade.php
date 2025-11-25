@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge([
        'class' => 'profile-error text-sm text-red-500 dark:text-red-400 mt-1 space-y-1'
    ]) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
