<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Welcome back!') }} {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome / Create Board Button -->
            <div class="flex justify-between items-center p-4 sm:p-6 bg-gray-900 shadow-sm sm:rounded-lg">
                <a href="#" class="auth-button text-sm px-4 py-2">
                    + Create Board
                </a>
            </div>
            
            <!-- Boards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($boards as $board)
                    <a href="{{ route('boards.show', $board) }}" class="block p-6 bg-gray-800 rounded-lg shadow hover:bg-gray-700 transition-colors">
                        <h4 class="text-lg font-semibold text-gray-100">{{ $board->title }}</h4>
                        <div>

                            <p class="text-gray-400 text-sm mt-1">{{ $board->tasks()->count() }} tasks</p>

                            @php
                                $memberCount = $board->members()->count();
                            @endphp

                            @if($memberCount > 1)
                                <p class="text-gray-400 text-sm mt-1">{{ $memberCount }} members</p>
                            @elseif($memberCount === 1)
                                <p class="text-gray-400 text-sm mt-1">{{ $memberCount }} member</p>
                            @else
                                <p class="text-gray-400 text-sm mt-1">No members</p>
                            @endif
                                                        
                        </div>

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
