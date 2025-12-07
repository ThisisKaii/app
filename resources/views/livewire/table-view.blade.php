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

            <button class="add-task-btn" wire:click="openTaskModal()">
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
        <div class="modal-overlay" wire:click.self="closeModal">
            <div class="modal-content" wire:click.stop>
                <!-- Header -->
                <div class="modal-header">
                    <h2>{{ $isEditing ? 'Edit Task' : 'New Task' }}</h2>
                    <button type="button" wire:click="closeModal" class="modal-close">&times;</button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="saveTask">
                    <div class="modal-body">
                        <!-- Row 1: Title (full width) -->
                        <div class="form-group">
                            <label>Title <span class="required">*</span></label>
                            <input type="text" wire:model="title" required>
                            @error('title')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Row 3: Type, Priority, Status (3 columns) -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Type</label>
                                <input type="text" wire:model="type" placeholder="e.g., Bug, Feature">
                            </div>

                            <div class="form-group">
                                <label>Priority</label>
                                <select wire:model="priority">
                                    <option value="">None</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select wire:model="status">
                                    <option value="to_do">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="in_review">In Review</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 4: Due Date, Assignee, URL (3 columns) -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" wire:model="due_date" onclick="this.showPicker()">
                            </div>

                            <div class="form-group">
                                <label>Assignee</label>
                                <select wire:model="assignee_id">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>URL / Link</label>
                                <input type="url" wire:model="url" placeholder="https://example.com">
                            </div>
                        </div>

                        <!-- Row 2: Description (full width) -->
                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="description" rows="2" placeholder="Add a description..."></textarea>
                        </div>

                        <!-- Row 5: Tags (full width with live color preview) -->
                        <div class="form-group">
                            <label>Tags</label>
                            <input type="text" wire:model.live="tagsInput"
                                placeholder="Enter tags separated by commas (e.g., urgent, backend, api)">

                            @if($tagsInput)
                                <div class="tags-preview">
                                    @foreach(array_filter(array_map('trim', explode(',', $tagsInput))) as $tagName)
                                        @php
                                            $existingTag = \App\Models\Tag::where('name', $tagName)->first();
                                            $color = $existingTag ? $existingTag->color : $availableColors[array_rand($availableColors)];
                                        @endphp
                                        <span class="tag-preview"
                                            style="background: {{ $color }}20; color: {{ $color }}; border-color: {{ $color }};">
                                            <span class="tag-dot" style="background: {{ $color }};"></span>
                                            {{ $tagName }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        @if($isEditing)
                            <button type="button" wire:click="showDeleteConfirmation()" class="btn-delete"
                                wire:loading.attr="disabled">
                                Delete Task
                            </button>
                        @else
                            <div></div>
                        @endif

                        <div class="footer-actions">
                            <button type="button" wire:click="closeModal" class="btn-cancel">
                                Cancel
                            </button>
                            <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                                <span wire:loading.remove
                                    wire:target="saveTask">{{ $isEditing ? 'Save Changes' : 'Create Task' }}</span>
                                <span wire:loading wire:target="saveTask">Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal-overlay delete-overlay" wire:click.self="cancelDelete">
            <div class="delete-confirmation" wire:click.stop>
                <h2>Confirm Delete</h2>
                <p>Are you sure you want to delete this task? This action cannot be undone.</p>

                <div class="delete-actions">
                    <button type="button" wire:click="performDelete" class="btn-confirm-delete"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="performDelete">Yes, Delete</span>
                        <span wire:loading wire:target="performDelete">Deleting...</span>
                    </button>
                    <button type="button" wire:click="cancelDelete" class="btn-cancel-delete" wire:loading.attr="disabled">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('close-modal', () => {
            @this.showModal = false;
        });
    });
</script>