<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome / Create Board Button -->
            <div class="flex justify-between items-center p-4 sm:p-6 bg-gray-900 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-100">Your Boards</h3>
                <a href="#" class="auth-button text-sm px-4 py-2">
                    + Create Board
                </a>
            </div>
            
            <!-- Boards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($boards as $board)
                    <a href="{{ route('boards.show', $board) }}" class="block p-6 bg-gray-800 rounded-lg shadow hover:bg-gray-700 transition-colors">
                        <h4 class="text-lg font-semibold text-gray-100">{{ $board->title }}</h4>
                        <p class="text-gray-400 text-sm mt-1">{{ $board->tasks()->count() }} tasks</p>
                    </a>
                @empty
                    <div class="p-6 bg-gray-800 rounded-lg shadow text-gray-400">
                        You haven't created any boards yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
