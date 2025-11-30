<div class="tasklist-container">

    <!-- Header Tabs -->
    <div class="view-tabs">
        <button wire:click="switchView('individual')"
            class="view-tab {{ $view === 'individual' ? 'active' : '' }}">
            ✓ My Tasks
        </button>

        <button wire:click="switchView('teams')" 
            class="view-tab {{ $view === 'teams' ? 'active' : '' }}">
            👥 Team Overview
        </button>
    </div>

    <!-- Individual View (Personal Task List) -->
    @if($view === 'individual')
        <div class="personal-tasks-wrapper">
            <div class="personal-tasks-header">
                <div>
                    <h2 class="personal-tasks-title">My Workspace</h2>
                    <p class="personal-tasks-subtitle">{{ $myTasks->count() }} tasks assigned to you</p>
                </div>
                <div class="tasks-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $myTasks->where('status', 'todo')->count() }}</div>
                        <div class="stat-label">To Do</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $myTasks->where('status', 'progress')->count() }}</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $myTasks->where('status', 'completed')->count() + $myTasks->where('status', 'published')->count() }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>

            <div class="personal-tasks-content">
                <!-- Priority Section -->
                <div class="task-section">
                    <div class="section-header">
                        <span class="section-icon">🔥</span>
                        <span class="section-title">High Priority</span>
                        <span class="section-count">{{ $myTasks->where('priority', 'high')->where('status', '!=', 'published')->count() }}</span>
                    </div>
                    <div class="task-list">
                        @forelse($myTasks->where('priority', 'high')->where('status', '!=', 'published')->take(5) as $task)
                            <div class="personal-task-card priority-high">
                                <div class="task-checkbox">
                                    <input type="checkbox" {{ in_array($task->status, ['completed', 'published']) ? 'checked' : '' }}>
                                </div>
                                <div class="task-content">
                                    <div class="task-main-row">
                                        <div class="task-title-row">
                                            <span class="task-title">{{ $task->title }}</span>
                                            <span class="task-status-pill status-{{ $task->status }}">
                                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($task->due_date || $task->description)
                                        <div class="task-meta-row">
                                            @if($task->due_date)
                                                <span class="task-due {{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'overdue' : '' }}">
                                                    📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                                </span>
                                            @endif
                                            @if($task->description)
                                                <span class="task-description">{{ Str::limit($task->description, 60) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-section">No high priority tasks</div>
                        @endforelse
                        
                        @if($myTasks->where('priority', 'high')->where('status', '!=', 'published')->count() > 5)
                            <button class="show-more-inline">
                                + {{ $myTasks->where('priority', 'high')->where('status', '!=', 'published')->count() - 5 }} more high priority tasks
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Today / Upcoming Section -->
                <div class="task-section">
                    <div class="section-header">
                        <span class="section-icon">📅</span>
                        <span class="section-title">Due Soon</span>
                        <span class="section-count">{{ $myTasks->where('due_date', '!=', null)->where('status', '!=', 'published')->count() }}</span>
                    </div>
                    <div class="task-list">
                        @forelse($myTasks->where('due_date', '!=', null)->where('status', '!=', 'published')->sortBy('due_date')->take(5) as $task)
                            <div class="personal-task-card">
                                <div class="task-checkbox">
                                    <input type="checkbox" {{ in_array($task->status, ['completed', 'published']) ? 'checked' : '' }}>
                                </div>
                                <div class="task-content">
                                    <div class="task-main-row">
                                        <div class="task-title-row">
                                            <span class="task-title">{{ $task->title }}</span>
                                            <span class="task-status-pill status-{{ $task->status }}">
                                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-meta-row">
                                        <span class="task-due {{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'overdue' : '' }}">
                                            📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                            @if(\Carbon\Carbon::parse($task->due_date)->isToday())
                                                <span class="due-today-badge">Today</span>
                                            @elseif(\Carbon\Carbon::parse($task->due_date)->isTomorrow())
                                                <span class="due-tomorrow-badge">Tomorrow</span>
                                            @endif
                                        </span>
                                        @if($task->priority)
                                            <span class="priority-indicator priority-{{ $task->priority }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-section">No upcoming deadlines</div>
                        @endforelse
                    </div>
                </div>

                <!-- All Active Tasks -->
                <div class="task-section">
                    <div class="section-header">
                        <span class="section-icon">📋</span>
                        <span class="section-title">All Active Tasks</span>
                        <span class="section-count">{{ $myTasks->whereIn('status', ['todo', 'in_progress', 'progress', 'review', 'in_review'])->count() }}</span>
                    </div>
                    <div class="task-list">
                        @forelse($myTasks->whereIn('status', ['todo', 'in_progress', 'progress', 'review', 'in_review'])->sortByDesc('created_at')->take(8) as $task)
                            <div class="personal-task-card">
                                <div class="task-checkbox">
                                    <input type="checkbox">
                                </div>
                                <div class="task-content">
                                    <div class="task-main-row">
                                        <div class="task-title-row">
                                            <span class="task-title">{{ $task->title }}</span>
                                            <span class="task-status-pill status-{{ $task->status }}">
                                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-meta-row">
                                        @if($task->due_date)
                                            <span class="task-due">
                                                📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                            </span>
                                        @endif
                                        @if($task->priority)
                                            <span class="priority-indicator priority-{{ $task->priority }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-section">No active tasks</div>
                        @endforelse
                        
                        @if($myTasks->whereIn('status', ['todo', 'in_progress', 'progress', 'review', 'in_review'])->count() > 8)
                            <button class="show-more-inline">
                                + {{ $myTasks->whereIn('status', ['todo', 'in_progress', 'progress', 'review', 'in_review'])->count() - 8 }} more tasks
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- Teams View (columns by user + unassigned) -->
    @if($view === 'teams')
        <div class="kanban-container" style="overflow-x: auto; justify-content: flex-start;">

            <!-- Each team member -->
            @foreach($teamMembers as $member)
                <div class="kanban-column">
                    <div class="column-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                            <div class="user-status-indicator {{ $member->isOnline() ? 'online' : 'offline' }}"></div>
                            <span>{{ $member->name }}</span>
                        </div>
                        <span style="color: #8b949e; font-size: 0.75rem;">
                            {{ $member->tasks->count() }}
                        </span>
                    </div>

                    <div class="cards-container">
                        @php
                            $visibleTasks = $member->tasks->take(5);
                            $remainingCount = $member->tasks->count() - 5;
                        @endphp

                        @forelse($visibleTasks as $task)
                            <div class="task-card">
                                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <div class="status-badge {{ $task->status }}"></div>
                                    <div class="task-card-title" style="margin: 0;">{{ $task->title }}</div>
                                </div>
                                @if($task->description)
                                    <div class="task-card-meta">{{ Str::limit($task->description, 50) }}</div>
                                @endif
                                @if($task->due_date)
                                    <div class="task-card-meta" style="margin-top: 0.5rem;">
                                        📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-state" style="padding: 2rem 1rem;">
                                No tasks assigned
                            </div>
                        @endforelse

                        @if($remainingCount > 0)
                            <button class="add-card" style="margin-top: 0.5rem;">
                                + Show {{ $remainingCount }} more
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Unassigned Tasks -->
            <div class="kanban-column">
                <div class="column-header">
                    <span>📌 Unassigned</span>
                    <span style="margin-left: auto; color: #8b949e; font-size: 0.75rem;">
                        {{ $unassignedTasks->count() }}
                    </span>
                </div>
                
                <div class="cards-container">
                    @php
                        $visibleTasks = $unassignedTasks->take(5);
                        $remainingCount = $unassignedTasks->count() - 5;
                    @endphp

                    @forelse($visibleTasks as $task)
                        <div class="task-card">
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div class="status-badge {{ $task->status }}"></div>
                                <div class="task-card-title" style="margin: 0;">{{ $task->title }}</div>
                            </div>
                            @if($task->description)
                                <div class="task-card-meta">{{ Str::limit($task->description, 50) }}</div>
                            @endif
                            @if($task->due_date)
                                <div class="task-card-meta" style="margin-top: 0.5rem;">
                                    📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state" style="padding: 2rem 1rem;">
                            No unassigned tasks
                        </div>
                    @endforelse

                    @if($remainingCount > 0)
                        <button class="add-card" style="margin-top: 0.5rem;">
                            + Show {{ $remainingCount }} more
                        </button>
                    @endif
                </div>
            </div>

        </div>
    @endif

</div>
