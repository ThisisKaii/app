<div>
    @foreach($tasks as $task)
        <div wire:key="task-{{ $task->id }}" 
             wire:click.stop="$dispatch('open-modal-{{ $status }}', { taskId: {{ $task->id }} })"
            class="task-card" draggable="true" data-task-id="{{ $task->id }}">
            <div class="task-card-title">{{ $task->title }} - {{ $task->id }}</div>

            <div class="task-card-meta">
                @if($task->priority)
                    <span class="priority-badge priority-{{ $task->priority }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                @endif

                @if($task->due_date)
                    <span>📅 {{ $task->due_date->format('M d') }}</span>
                @endif

                @if($task->assignee)
                    <span>👤 {{ $task->assignee->name }}</span>
                @endif
            </div>
        </div>
    @endforeach

    <!-- ADD -->
    <button class="add-card" wire:click.stop="$dispatch('open-modal-{{ $status }}')">
        <span>+</span> New page
    </button>

    <livewire:add-modal :boardId="$board->id" :status="$status" :key="'add-modal-' . $status" />
</div>