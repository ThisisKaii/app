
<!-- Edit modal -->
<div>
    @foreach($tasks as $task)
        <div wire:key="task-{{ $task->id }}" 
             wire:click.stop="$dispatch('open-modal-{{ $status }}', { taskId: {{ $task->id }} })"
            class="task-card" draggable="true" data-task-id="{{ $task->id }}">
            <div class="task-card-title">{{ $task->title }}</div>

            <div class="task-card-meta">
                @if($task->priority)
                    <span class="priority-badge priority-{{ $task->priority }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                @endif

                @if($task->due_date)
                    @php
                        $dueDate = \Carbon\Carbon::parse($task->due_date);
                        $isOverdue = $dueDate->isPast();
                        $daysUntilDue = now()->diffInDays($dueDate, false);
                        $isDueSoon = !$isOverdue && $daysUntilDue >= 0 && $daysUntilDue <= 3;
                        
                        if ($isOverdue) {
                            $bgColor = 'rgba(239, 68, 68, 0.15)';
                            $textColor = '#ef4444';
                        } elseif ($isDueSoon) {
                            $bgColor = 'rgba(245, 158, 11, 0.15)';
                            $textColor = '#f59e0b';
                        } else {
                            $bgColor = 'rgba(88, 166, 255, 0.15)';
                            $textColor = '#58a6ff';
                        }
                    @endphp
                    <span style="background: {{ $bgColor }}; color: {{ $textColor }}; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">{{ $task->due_date->format('M d') }}</span>
                @endif

                @if($task->assignee)
                    <span style="background: rgba(139, 148, 158, 0.15); color: #8b949e; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">{{ $task->assignee->name }}</span>
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
