<div>
    @foreach($tasks->where('status', $status) as $task)
    <div class="task-card" draggable="true" data-task-id="{{ $task->id }}" wire:key="task-{{ $task->id }}" wire:click="openModal({{ $task->id }})">
        <div class="task-card-title">{{ $task->title }}</div>
        <div class="task-card-meta">
            @if($task->priority)
            <span class="priority-badge priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
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
    <!-- Modal Backdrop -->
    @if($isOpen)
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
        <div style="background: #0d1117; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #30363d;">

            <!-- Header -->
            <div style="padding: 1.5rem; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: #f0f6fc; font-size: 1.25rem; margin: 0;">Edit Task</h2>
                <button type="button" wire:click="closeModal()" style="background: none; border: none; color: #8b949e; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="save">
                <div style="padding: 1.5rem;">

                    <!-- Title -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">
                            Title <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text"
                            wire:model="title"
                            style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                            required>
                        @error('title')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Type</label>
                        <input type="text"
                            wire:model="type"
                            placeholder="e.g., Bug, Feature, Design"
                            style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                            >
                    </div>

                    <!-- Priority -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Priority</label>
                        <select wire:model="priority"
                            style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                            <option value="">None</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Due Date</label>
                        <input type="date"
                            wire:model="due_date"
                            style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                            onclick="this.showPicker()">
                    </div>

                    <!-- URL -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">URL</label>
                        <input type="url"
                            wire:model="url"
                            placeholder="https://example.com"
                            style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                    </div>

                    <!-- Description -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                        <textarea wire:model="description"
                            rows="4"
                            placeholder="Add task details..."
                            style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem; resize: vertical;"></textarea>
                    </div>

                </div>

                <!-- Footer -->
                <div style="padding: 1rem 1.5rem; border-top: 1px solid #21262d; display: flex; gap: 0.75rem; justify-content: space-between;">
                    <button wire:click="delete"
                        type="button"
                        style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Delete Task
                    </button>
                    <div style="display: flex; gap: 0.75rem;">
                        <button type="button"
                            wire:click="closeModal()"
                            style="background: #21262d; color: #c9d1d9; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Cancel
                        </button>
                        <button type="submit"
                            style="background: #238636; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
    @endif
</div>