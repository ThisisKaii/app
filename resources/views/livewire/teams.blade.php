<div class="teams">
    <div class="teams-wrapper">
        <!-- Teams Header -->
        <div class="teams-header">
            <h2>Team Workload</h2>
            <p>View tasks assigned to each team member</p>
        </div>

        <!-- Teams View (columns by user + unassigned) -->
        <div class="kanban-container">
            <!-- Each team member -->
            @foreach($teamMembers as $member)
                <div class="kanban-column">
                    <div class="column-header">
                        <div class="team-member-info">
                            <div class="team-member-details">
                                <div class="team-member-name">
                                    <div class="user-status-indicator {{ $member->isOnline() ? 'online' : 'offline' }}"></div>
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
                            $visibleTasks = $member->tasks->take(8);
                            $remainingCount = $member->tasks->count() - 8;
                        @endphp

                        @forelse($visibleTasks as $task)
                            <div class="task-card {{ $task->priority ? 'priority-' . strtolower($task->priority) : '' }}">
                                <div class="task-card-header">
                                    <div class="status-badge {{ $task->status }}"></div>
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
                                            📅 {{ $dueDate->format('M d') }}
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
                                <div class="empty-state-icon">📭</div>
                                <div>No tasks assigned</div>
                            </div>
                        @endforelse

                        @if($remainingCount > 0)
                            <button class="show-more-button">
                                <span>↓</span>
                                <span>Show {{ $remainingCount }} more</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Unassigned Tasks -->
            <div class="kanban-column unassigned">
                <div class="column-header">
                    <div class="team-member-info">
                        <div class="team-member-avatar" style="background: linear-gradient(135deg, #6e7681 0%, #484f58 100%);">
                            📌
                        </div>
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
                        $visibleTasks = $unassignedTasks->take(8);
                        $remainingCount = $unassignedTasks->count() - 8;
                    @endphp

                    @forelse($visibleTasks as $task)
                        <div class="task-card {{ $task->priority ? 'priority-' . strtolower($task->priority) : '' }}">
                            <div class="task-card-header">
                                <div class="status-badge {{ $task->status }}"></div>
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
                                        📅 {{ $dueDate->format('M d') }}
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

                    @if($remainingCount > 0)
                        <button class="show-more-button">
                            <span>↓</span>
                            <span>Show {{ $remainingCount }} more</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>