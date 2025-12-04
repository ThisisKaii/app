<div>
    <div wire:key="view-{{ $view }}-{{ $renderKey }}">
        @if($view === 'board')
            @livewire('boards', ['boardId' => $boardId])
        @elseif($view === 'table')
            <div style="padding: 2rem;">
                @livewire('table', ['boardId' => $boardId])
            </div>
        @elseif($view === 'tasks')
            <div style="padding: 2rem;">
                @livewire('tasks-list')
            </div>
        @endif
    </div>
</div>