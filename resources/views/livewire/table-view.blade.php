<div class="table-container">

    <div class="table-header">
        <h2 class="table-title">Tasks Overview</h2>
        <div class="table-stats">
            <button class="filter-btn" wire:click="toggleFilters">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <button class="add-task-btn" wire:click="openTaskModal">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Task
            </button>
                @livewire('add-table', ['boardId' => $boardId])
            <span class="stat-badge">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                {{ count($tasks) }} {{ Str::plural('task', count($tasks)) }}
            </span>
        </div>
    </div>

    @if($showFilters)
        <div class="filter-panel active">
            <div class="filter-content">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="to_do">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="in_review">In Review</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Priority</label>
                    <select class="filter-select" wire:model.live="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Assignee</label>
                    <select class="filter-select" wire:model.live="assigneeFilter">
                        <option value="">All Assignees</option>
                        @php
                            $uniqueAssignees = collect($tasks)->pluck('assignee')->filter()->unique('name')->sortBy('name');
                        @endphp
                        @foreach($uniqueAssignees as $assignee)
                            <option value="{{ $assignee->name }}">{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" class="filter-input" placeholder="Search tasks..."
                        wire:model.live.debounce.100ms="searchFilter">
                </div>
                <button class="clear-filters-btn" wire:click="clearFilters">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    @endif

    @if(count($tasks) > 0)
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Assignee</th>
                        <th>Due Date</th>
                        <th>Tags</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr wire:key="task-{{ $task->id }}" style="cursor: pointer;" wire:click="openTaskModal({{ $task->id }})"
                            class="table-row">
                            <td>
                                <div class="task-title">{{ $task->title }}</div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ Str::slug($task->status) }}">
                                    {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="type-text">{{ $task->type ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($task->priority)
                                    <span class="priority-badge priority-{{ strtolower($task->priority) }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($task->assignee) && $task->assignee)
                                    <div class="assignee-info">
                                        <span class="assignee-name">{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($task->due_date)
                                    <span class="date-text">{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                                @else
                                    <span class="text-muted">No date</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($task->tags) && count($task->tags) > 0)
                                    <div class="tags-container">
                                        @foreach($task->tags as $tag)
                                            <span class="tag-badge">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No tags</span>
                                @endif
                            </td>
                            <td>
                                @if($task->url)
                                    <a href="{{ $task->url }}" target="_blank" class="url-link" onclick="event.stopPropagation();">
                                        <svg class="link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        View
                                    </a>
                                @else
                                    <span class="text-muted">No link</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <h3 class="empty-title">No tasks found</h3>
            <p class="empty-text">
                @if($statusFilter || $priorityFilter || $assigneeFilter || $searchFilter)
                    Try adjusting your filters or create a new task.
                @else
                    Create your first task to get started.
                @endif
            </p>
        </div>
    @endif
</div>