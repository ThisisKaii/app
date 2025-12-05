<div>
    @if($view === 'board')
        <div wire:key="board-view-{{ $renderKey }}">
            <livewire:boards :boardId="$boardId" :key="'board-'.$renderKey" />
        </div>
    @elseif($view === 'table')
        <div style="padding: 2rem;" wire:key="table-view-{{ $renderKey }}">
            <livewire:table-view :boardId="$boardId" :key="'table-'.$renderKey" />
        </div>
    @elseif($view === 'tasks')
        <div style="padding: 2rem;" wire:key="tasks-view-{{ $renderKey }}">
            <livewire:tasks-list :key="'tasks-'.$renderKey" />
        </div>
    @endif
</div>