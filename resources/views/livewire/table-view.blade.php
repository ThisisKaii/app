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

            <button class="add-task-btn" wire:click="openModal()">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Task
            </button>
            @livewire('board-members', ['boardId' => $boardId])
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
                        <tr wire:key="task-{{ $task->id }}" style="cursor: pointer;"
                            wire:click="openModal({{ $task->id }})" class="table-row">
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
                                            <span class="tag-badge"
                                                style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }};">
                                                {{ $tag->name }}
                                            </span>
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

    <!-- Task Modal -->
    @if($showModal)
        <div wire:click="closeModal"
            style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 2rem;">
            <div wire:click.stop
                style="background: #0d1117; border-radius: 8px; width: 95%; max-width: 1100px; max-height: 90vh; overflow-y: auto; border: 1px solid #30363d;">

                <!-- Header -->
                <div
                    style="padding: 1.5rem; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="color: #f0f6fc; font-size: 1.25rem; margin: 0;">
                        {{ $isEditing ? 'Edit Task' : 'New Task' }}
                    </h2>
                    <button type="button" wire:click="closeModal"
                        style="background: none; border: none; color: #8b949e; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save">
                    <div style="padding: 1.5rem;">

                        <!-- Row 1: Title (full width) -->
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">
                                Title <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" wire:model="title"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                required>
                            @error('title')
                                <span
                                    style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Row 2: Description (full width) -->
                        <div style="margin-bottom: 1rem;">
                            <label
                                style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                            <textarea wire:model="description" rows="2" placeholder="Add a description..."
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem; resize: vertical; font-family: inherit;"></textarea>
                        </div>

                        <!-- Row 3: Type, Priority, Status (3 columns) -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label
                                    style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Type</label>
                                <input type="text" wire:model="type" placeholder="e.g., Bug, Feature"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                            </div>

                            <div>
                                <label
                                    style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Priority</label>
                                <select wire:model="priority"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                    <option value="">None</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Status</label>
                                <select wire:model="status"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                    <option value="to_do">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="in_review">In Review</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 4: Due Date, Assignee, URL (3 columns) -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Due
                                    Date</label>
                                <input type="date" wire:model="due_date"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    onclick="this.showPicker()">
                            </div>

                            <div>
                                <label
                                    style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Assignee</label>
                                <select wire:model="assignee_id"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">URL /
                                    Link</label>
                                <input type="url" wire:model="url" placeholder="https://example.com"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                            </div>
                        </div>

                        <!-- Row 5: Tags (full width with live color preview) -->
                        <div style="margin-bottom: 1rem;">
                            <label
                                style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Tags</label>
                            <input type="text" wire:model.live="tagsInput"
                                placeholder="Enter tags separated by commas (e.g., urgent, backend, api)"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">

                            @if($tagsInput)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                    @foreach(array_filter(array_map('trim', explode(',', $tagsInput))) as $tagName)
                                        @php
                                            $existingTag = \App\Models\Tag::where('name', $tagName)->first();
                                            $color = $existingTag ? $existingTag->color : $availableColors[array_rand($availableColors)];
                                        @endphp
                                        <span
                                            style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.625rem; background: {{ $color }}20; color: {{ $color }}; border: 1px solid {{ $color }}; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">
                                            <span
                                                style="width: 8px; height: 8px; border-radius: 50%; background: {{ $color }};"></span>
                                            {{ $tagName }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>

                    <!-- Footer -->
                    <div
                        style="padding: 1rem 1.5rem; border-top: 1px solid #21262d; display: flex; gap: 0.75rem; justify-content: space-between;">
                        @if($isEditing)
                            <button type="button" wire:click="confirmDelete"
                                style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                                Delete Task
                            </button>
                        @else
                            <div></div>
                        @endif

                        <div style="display: flex; gap: 0.75rem;">
                            <button type="button" wire:click="closeModal"
                                style="background: #21262d; color: #c9d1d9; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                                Cancel
                            </button>
                            <button type="submit"
                                style="background: #238636; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                                {{ $isEditing ? 'Save Changes' : 'Create Task' }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    @endif

    <!-- Delete Confirmation -->
    @if($showDeleteConfirm)
        <div wire:click="cancelDelete"
            style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 2100; display: flex; align-items: center; justify-content: center;">
            <div wire:click.stop
                style="background: #161b22; border-radius: 8px; padding: 1.5rem; width: 90%; max-width: 380px; border: 1px solid #30363d;">

                <h2 style="color: #f0f6fc; font-size: 1.25rem; margin-bottom: 1rem; text-align:center;">
                    Confirm Delete
                </h2>

                <p style="color: #c9d1d9; margin-bottom: 1.5rem; text-align:center;">
                    Are you sure you want to delete this task?
                </p>

                <div style="display:flex; justify-content: center; gap: 1rem;">
                    <button type="button" wire:click="deleteTask"
                        style="background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Yes, Delete
                    </button>

                    <button type="button" wire:click="cancelDelete"
                        style="background: #21262d; color: #c9d1d9; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>