<div class="teams" wire:poll.5s.keep-alive>
    <div class="teams-wrapper">
        <!-- Teams Header -->
        <div class="teams-header">
            <h2>Team Workload</h2>
            <p>View tasks assigned to each team member</p>
        </div>

        <!-- Teams View (columns by user + unassigned) -->
        <div class="kanban-container" style="justify-content: flex-start;">
            <!-- Each team member -->
            @foreach($teamMembers as $member)
                <div class="kanban-column">
                    <div class="column-header">
                        <div class="team-member-info">
                            <div class="team-member-details">
                                <div class="team-member-name">
                                    <div class="user-status-indicator {{ $member->isOnline() ? 'online' : 'offline' }}">
                                    </div>
                                    <span>{{ $member->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-task-count">
                            {{ $member->tasks->count() }}
                        </div>
                    </div>

                    <div class="cards-container">
                        @php
                            $isExpanded = $this->isExpanded($member->id);
                            $visibleTasks = $isExpanded ? $member->tasks : $member->tasks->take(8);
                            $remainingCount = $member->tasks->count() - 8;
                        @endphp

                        @forelse($visibleTasks as $task)
                            <div class="task-card {{ $task->priority ? 'priority-' . strtolower($task->priority) : '' }}"
                                style="cursor: pointer;" wire:click="$dispatch('openTaskModal', { taskId: {{ $task->id }} })">
                                <div class="task-card-header">
                                    <div class="task-card-title">{{ $task->title }}</div>
                                </div>

                                @if($task->description)
                                    <div class="task-card-description">
                                        {{ Str::limit($task->description, 80) }}
                                    </div>
                                @endif

                                <div class="task-card-meta">
                                    @if($task->due_date)
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($task->due_date);
                                            $today = \Carbon\Carbon::today();
                                            $isOverdue = $dueDate->isPast();
                                            $isDueSoon = $dueDate->diffInDays($today) <= 3 && !$isOverdue;
                                        @endphp
                                        <span class="due-date-badge {{ $isOverdue ? 'overdue' : ($isDueSoon ? 'due-soon' : '') }}">
                                            {{ $dueDate->format('M d') }}
                                        </span>
                                    @endif

                                    @if($task->type)
                                        <span style="color: #8b949e; font-size: 0.7rem;">
                                            {{ ucfirst($task->type) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-state-icon"></div>
                                <div>No tasks assigned</div>
                            </div>
                        @endforelse

                        @if($remainingCount > 0 && !$isExpanded)
                            <button class="show-more-button" wire:click="showMore({{ $member->id }})">
                                <span>↓</span>
                                <span>Show {{ $remainingCount }} more</span>
                            </button>
                        @elseif($isExpanded && $member->tasks->count() > 8)
                            <button class="show-more-button" wire:click="showMore({{ $member->id }})">
                                <span>↑</span>
                                <span>Show less</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Unassigned Tasks -->
            <div class="kanban-column unassigned">
                <div class="column-header">
                    <div class="team-member-info">
                        <div class="team-member-details">
                            <div class="team-member-name">
                                <span>Unassigned</span>
                            </div>
                        </div>
                    </div>
                    <div class="team-task-count">
                        {{ $unassignedTasks->count() }}
                    </div>
                </div>

                <div class="cards-container">
                    @php
                        $isExpanded = $this->isExpanded('unassigned');
                        $visibleTasks = $isExpanded ? $unassignedTasks : $unassignedTasks->take(8);
                        $remainingCount = $unassignedTasks->count() - 8;
                    @endphp

                    @forelse($visibleTasks as $task)
                        <div class="task-card {{ $task->priority ? 'priority-' . strtolower($task->priority) : '' }}"
                            style="cursor: pointer;" wire:click="$dispatch('openTaskModal', { taskId: {{ $task->id }} })">
                            <div class="task-card-header">
                                <div class="task-card-title">{{ $task->title }}</div>
                            </div>

                            @if($task->description)
                                <div class="task-card-description">
                                    {{ Str::limit($task->description, 80) }}
                                </div>
                            @endif

                            <div class="task-card-meta">
                                @if($task->due_date)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($task->due_date);
                                        $today = \Carbon\Carbon::today();
                                        $isOverdue = $dueDate->isPast();
                                        $isDueSoon = $dueDate->diffInDays($today) <= 3 && !$isOverdue;
                                    @endphp
                                    <span class="due-date-badge {{ $isOverdue ? 'overdue' : ($isDueSoon ? 'due-soon' : '') }}">
                                        {{ $dueDate->format('M d') }}
                                    </span>
                                @endif

                                @if($task->type)
                                    <span style="color: #8b949e; font-size: 0.7rem;">
                                        {{ ucfirst($task->type) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">✓</div>
                            <div>All tasks assigned!</div>
                        </div>
                    @endforelse

                    @if($remainingCount > 0 && !$isExpanded)
                        <button class="show-more-button" wire:click="showMoreUnassigned">
                            <span>↓</span>
                            <span>Show {{ $remainingCount }} more</span>
                        </button>
                    @elseif($isExpanded && $unassignedTasks->count() > 8)
                        <button class="show-more-button" wire:click="showMoreUnassigned">
                            <span>↑</span>
                            <span>Show less</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>