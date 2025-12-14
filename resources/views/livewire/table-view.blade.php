<div class="table-view-container" wire:poll.10s.keep-alive>
    <div class="table-header">
        <h2 class="table-title">Tasks Overview</h2>
        <div class="table-stats">
            <button class="filter-btn" wire:click="toggleFilters" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #21262d; color: #c9d1d9; border: 1px solid #30363d; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#30363d'; this.style.borderColor='#484f58';" onmouseout="this.style.background='#21262d'; this.style.borderColor='#30363d';">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>

            <button class="add-task-btn" wire:click="$dispatch('open-group-modal')">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Task
            </button>
            @livewire('board-members', ['boardId' => $boardId])
            <span class="stat-badge">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                {{ count($groups) }} {{ Str::plural('group', count($groups)) }}
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
                        wire:model.live.debounce.300ms="searchFilter">
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

    @if(count($groups) > 0)
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40%">Title</th>
                        <th style="width: 15%">Status</th>
                        <th style="width: 20%">Progress</th>
                        <th style="width: 15%">Team</th>
                        <th style="width: 10%">Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        {{-- Master Row --}}
                        <tr wire:key="group-{{ $group->id }}" 
                            class="table-row master-row" 
                            style="cursor: pointer; border-bottom: none;"
                            wire:click="toggleGroup({{ $group->id }})">
                            <td style="width: 40%">
                                <div class="task-title" style="font-weight: 600; color: #f0f6fc; display: flex; align-items: center; gap: 8px;">
                                    <svg style="width: 16px; height: 16px; transition: transform 0.2s; transform: {{ in_array($group->id, $expandedGroups) ? 'rotate(90deg)' : 'rotate(0deg)' }}" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ $group->title }}
                                </div>
                            </td>
                            <td style="width: 15%">
                                <span class="status-badge status-{{ Str::slug($group->status ?? 'to_do') }}">
                                    {{ str_replace('_', ' ', ucfirst($group->status ?? 'to do')) }}
                                </span>
                            </td>
                            <td style="width: 20%">
                                {{-- Progress Bar --}}
                                @php
                                    $total = $group->tasks_count;
                                    $completed = $group->completed_tasks_count;
                                    $percent = $total > 0 ? ($completed / $total) * 100 : 0;
                                @endphp
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: #30363d; border-radius: 3px; overflow: hidden;">
                                        <div style="width: {{ $percent }}%; height: 100%; background: #238636; transition: width 0.3s ease;"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; color: #8b949e; min-width: 30px;">
                                        {{ $completed }}/{{ $total }}
                                    </span>
                                </div>
                            </td>
                            <td style="width: 15%">
                                <div class="assignee-info" style="display:flex; gap:4px; flex-wrap:wrap;">
                                    @php
                                        $users = $group->tasks->pluck('users')->flatten()->unique('id');
                                    @endphp
                                    @if($users->count() > 0)
                                        <div style="display: flex; margin-left: 8px;">
                                            @foreach($users->take(4) as $index => $user)
                                                <div title="{{ $user->name }}" 
                                                     style="width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #0d1117; margin-left: -8px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: white; font-weight: 600;">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            @endforeach
                                            @if($users->count() > 4)
                                                <div style="width: 24px; height: 24px; border-radius: 50%; background: #30363d; border: 2px solid #0d1117; margin-left: -8px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: #c9d1d9;">
                                                    +{{ $users->count() - 4 }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem;">Unassigned</span>
                                    @endif
                                </div>
                            </td>
                            <td style="width: 10%">
                                @php
                                    $earliestDue = $group->tasks->pluck('due_date')->filter()->sort()->first();
                                @endphp
                                @if($earliestDue)
                                    <span style="color: #8b949e; font-size: 0.875rem;">{{ \Carbon\Carbon::parse($earliestDue)->format('M d') }}</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.875rem;">-</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Detail Row (Expanded) --}}
                        @if(in_array($group->id, $expandedGroups))
                            <tr class="table-row detail-row" style="background: rgba(22, 27, 34, 0.5);">
                                <td colspan="6" style="padding: 0;">
                                    <div style="padding: 1rem 1rem 1rem 3rem; border-top: 1px dashed #30363d;">
                                        
                                        @if($group->tasks->count() > 0)
                                            <div style="display: grid; grid-template-columns: 1fr; gap: 0.5rem;">
                                                @foreach($group->tasks as $task)
                                                    <div style="display: grid; grid-template-columns: 3fr 1fr 1fr 1fr; align-items: center; gap: 1rem; padding: 0.5rem; border-radius: 6px; background: rgba(255,255,255,0.02); transition: background 0.2s;"
                                                         onmouseover="this.style.background='rgba(255,255,255,0.05)'"
                                                         onmouseout="this.style.background='rgba(255,255,255,0.02)'">
                                                        
                                                        {{-- Task Title / Checkbox --}}
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            <input type="checkbox" 
                                                                   class="modern-checkbox"
                                                                   wire:click="toggleTaskCompletion({{ $task->id }})"
                                                                   {{ $task->completed_at ? 'checked' : '' }}>
                                                            <span wire:click="$dispatch('open-task-modal', { taskId: {{ $task->id }} })"
                                                                  style="cursor: pointer; color: #c9d1d9; font-size: 0.9rem; {{ $task->completed_at ? 'text-decoration: line-through; opacity: 0.5;' : '' }}">
                                                                {{ $task->type ?: 'General Task' }}
                                                            </span>
                                                        </div>

                                                        {{-- Assignee --}}
                                                        <div>
                                                            @if($task->users->count() > 0)
                                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                                    <div style="width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 10px; color: white;">
                                                                        {{ substr($task->users->first()->name, 0, 1) }}
                                                                    </div>
                                                                    <span style="font-size: 0.8rem; color: #8b949e;">{{ $task->users->first()->name }}</span>
                                                                    @if($task->users->count() > 1)
                                                                        <span style="font-size: 0.8rem; color: #8b949e;">+{{ $task->users->count() - 1 }}</span>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <span style="font-size: 0.8rem; color: #484f58;">Unassigned</span>
                                                            @endif
                                                        </div>

                                                        {{-- Priority --}}
                                                        <div>
                                                            @if($task->priority)
                                                                <span class="priority-badge priority-{{ strtolower($task->priority) }}" style="font-size: 0.7rem;">
                                                                    {{ ucfirst($task->priority) }}
                                                                </span>
                                                            @else
                                                                <span style="font-size: 0.8rem; color: #484f58;">-</span>
                                                            @endif
                                                        </div>

                                                        {{-- Due Date --}}
                                                        <div>
                                                            @if($task->due_date)
                                                                <span style="font-size: 0.8rem; color: {{ $task->due_date->isPast() ? '#ef4444' : '#8b949e' }};">
                                                                    {{ $task->due_date->format('M d') }}
                                                                </span>
                                                            @else
                                                                <span style="font-size: 0.8rem; color: #484f58;">-</span>
                                                            @endif
                                                        </div>

                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div style="text-align: center; padding: 1rem; color: #484f58; font-size: 0.9rem;">
                                                No sub-tasks yet. <span wire:click="$dispatch('open-group-modal', { groupId: {{ $group->id }} })" style="color: #58a6ff; cursor: pointer; text-decoration: underline;">Add one</span>
                                            </div>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endif
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
            <h3 class="empty-title">No groups found</h3>
            <p class="empty-text">
                Create a new group to get started.
            </p>
        </div>
    @endif

    <livewire:add-modal :boardId="$boardId" wire:key="table-add-modal" />
</div>