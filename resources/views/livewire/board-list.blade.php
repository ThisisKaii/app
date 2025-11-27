<div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($boards as $board)
            @php
                $memberCount = $board->members()->count();
            @endphp
            <a href="{{ route('boards.show', $board) }}"
                class="block p-6 bg-gray-800 rounded-lg shadow hover:bg-gray-700 transition-colors">
                <h4 class="text-lg font-semibold text-gray-100">{{ $board->title }}</h4>
                <div class="flex items-center gap-4 text-gray-400 text-sm mt-1">
                    <span>{{ $board->tasks()->count() }} tasks</span>
                    <span>
                        @if($memberCount > 1)
                            {{ $memberCount }} members
                        @elseif($memberCount === 1)
                            {{ $memberCount }} member
                        @else
                            No members
                        @endif
                    </span>
                    <span>Update: {{ $board->updated_at->diffForHumans() }}</span>
                </div>


            </a>
        @empty
            <div class="p-6 bg-gray-800 rounded-lg shadow text-gray-400">
                You haven't created any boards yet.
            </div>
        @endforelse
    </div>
</div>