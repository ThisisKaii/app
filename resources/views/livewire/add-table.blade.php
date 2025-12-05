<div>
    @if($show)
        <div wire:click="closeModal"
            style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 2rem;">
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

                        <!-- Title -->
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


                        <!-- Type and Priority Row -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <!-- Type -->
                            <div>
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Type
                                    <span style="color: #ef4444;">*</span></label>
                                <input type="text" wire:model="type" placeholder="e.g., Bug, Feature, Design"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    required>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label
                                    style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Priority
                                    <span style="color: #ef4444;">*</span></label>
                                <select wire:model="priority"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    required>
                                    <option value="">None</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div style="margin-bottom: 1rem;">
                            <label
                                style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                            <textarea wire:model="description" rows="3" placeholder="Add a description..."
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem; resize: vertical; font-family: inherit;"></textarea>
                        </div>
                        <!-- Status and Due Date Row -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <!-- Status -->
                            <div>
                                <label
                                    style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Status</label>
                                <select wire:model="status"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                    <option value="to_do">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="in_review">In Review</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Due
                                    Date</label>
                                <input type="date" wire:model="due_date"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    onclick="this.showPicker()">
                            </div>
                        </div>

                        <!-- URL -->
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">URL /
                                Link</label>
                            <input type="url" wire:model="url" placeholder="https://example.com"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                        </div>

                        <!-- Assignee -->
                        <div style="margin-bottom: 1rem;">
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

                        <!-- Tags -->
                        <div style="margin-bottom: 1rem;">
                            <label
                                style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Tags</label>
                            <input type="text" wire:model="tagsInput"
                                placeholder="Enter tags separated by commas (e.g., urgent, backend, api)"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                            <span style="color: #6e7681; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                                Separate multiple tags with commas
                            </span>

                            @if($tagsInput)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                                    @foreach(array_filter(array_map('trim', explode(',', $tagsInput))) as $tag)
                                        <span
                                            style="display: inline-block; padding: 0.25rem 0.625rem; background: rgba(163, 113, 247, 0.2); color: #a371f7; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">
                                            {{ $tag }}
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
                            <button wire:click="askDelete" type="button"
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

    @if($showDeleteConfirm)
        <div wire:click="cancelDelete"
            style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div wire:click.stop
                style="background: #161b22; border-radius: 8px; padding: 1.5rem; width: 90%; max-width: 380px; border: 1px solid #30363d;">

                <h2 style="color: #f0f6fc; font-size: 1.25rem; margin-bottom: 1rem; text-align:center;">
                    Confirm Delete
                </h2>

                <p style="color: #c9d1d9; margin-bottom: 1.5rem; text-align:center;">
                    Are you sure you want to delete this task?
                </p>

                <div style="display:flex; justify-content: center; gap: 1rem;">
                    <button wire:click="delete"
                        style="background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Yes, Delete
                    </button>

                    <button wire:click="cancelDelete"
                        style="background: #21262d; color: #c9d1d9; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>