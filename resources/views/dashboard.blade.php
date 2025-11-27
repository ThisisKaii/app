<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Welcome back!') }} {{ auth()->user()->name }}
        </h2>
    </x-slot>
    @if(session('success'))
        <div id="toast-success"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;">
            <span style="font-size: 1.5rem;">✓</span>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0; margin-left: 0.5rem; line-height: 1;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div id="toast-error"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #ef4444; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;">
            <span style="font-size: 1.5rem;">✕</span>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0; margin-left: 0.5rem; line-height: 1;">&times;</button>
        </div>
    @endif
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Add Board Button -->
            @livewire('add-board')

            <!-- Boards Grid -->
            @livewire('board-list')
        </div>
    </div>
</x-app-layout>