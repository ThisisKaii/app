<div>
    @if($board->list_type === 'Business')
        {{-- Business Board Views --}}
        @if($currentView === 'budget')
            @livewire('budget-board', ['boardId' => $board->id], key('budget-board-' . $board->id))
        @elseif($currentView === 'table')
            @livewire('budget-table-view', ['boardId' => $board->id], key('budget-table-' . $board->id))
        @elseif($currentView === 'members')
            @livewire('members-list', ['boardId' => $board->id], key('members-' . $board->id))
        @elseif($currentView === 'logs')
            @livewire('activity-log-component', ['boardId' => $board->id], key('logs-' . $board->id))
        @endif
    @else
        {{-- Normal Board Views --}}
        @if($currentView === 'board')
            @livewire('boards', ['boardId' => $board->id], key('board-' . $board->id))
        @elseif($currentView === 'table')
            @livewire('table-view', ['boardId' => $board->id], key('table-' . $board->id))
        @elseif($currentView === 'tasks')
            @livewire('tasks-list', ['boardId' => $board->id], key('tasks-' . $board->id))
        @elseif($currentView === 'members')
            @livewire('members-list', ['boardId' => $board->id], key('members-' . $board->id))
        @elseif($currentView === 'logs')
            @livewire('activity-log-component', ['boardId' => $board->id], key('logs-' . $board->id))
        @endif
    @endif
</div>