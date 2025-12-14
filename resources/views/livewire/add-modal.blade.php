<div>
    @if($show)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
            <div style="background: #0d1117; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #30363d;">

                <!-- Header -->
                <div style="padding: 1.5rem; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="color: #f0f6fc; font-size: 1.25rem; margin: 0;">
                        @if($mode === 'group')
                            {{ $isEditing ? 'Edit Title' : 'New Title' }}
                        @else
                            {{ $isEditing ? 'Edit Item' : 'New Item' }}
                        @endif
                    </h2>
                    <button type="button" wire:click="closeModal"
                        style="background: none; border: none; color: #8b949e; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save">
                    <div style="padding: 1.5rem;">

                        @if($mode === 'group')
                            <!-- Title (Group) -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">
                                    Title (Category) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="text" wire:model="title"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    required>
                                @error('title')
                                    <div style="color: #ef4444; margin-top: 0.5rem; font-size: 0.875rem;">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @else
                            <!-- Type (Task) -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Type (Sub-item) <span style="color: #ef4444;">*</span></label>
                                <input type="text" wire:model="type" placeholder="e.g., Bug, Feature, Design"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    required>
                                @error('type')
                                    <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Priority</label>
                                <select wire:model="priority"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>

                            <!-- Due Date -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Due Date</label>
                                <input type="date" wire:model="due_date"
                                    style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                    onclick="this.showPicker()">
                            </div>

                            <!-- Assignees (Multi) -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Assignees</label>
                                <div style="max-height: 150px; overflow-y: auto; background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 0.5rem;">
                                    @foreach($this->users as $user)
                                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0; cursor: pointer; color: #c9d1d9;">
                                            <input type="checkbox" wire:model="assignee_ids" value="{{ $user->id }}" style="cursor: pointer;">
                                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div style="width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: white;">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                {{ $user->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- Footer -->
                    <div style="padding: 1rem 1.5rem; border-top: 1px solid #21262d; display: flex; gap: 0.75rem; justify-content: space-between;">
                        @if($isEditing)
                            <button wire:click="askDelete" type="button"
                                style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                                Delete
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
                                {{ $isEditing ? 'Save Changes' : ($mode === 'group' ? 'Create Title' : 'Create Item') }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    @endif

    @if($showDeleteConfirm)
        <div wire:click="cancelDelete" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div wire:click.stop style="background: #161b22; border-radius: 8px; padding: 1.5rem; width: 90%; max-width: 380px; border: 1px solid #30363d;">

                <h2 style="color: #f0f6fc; font-size: 1.25rem; margin-bottom: 1rem; text-align:center;">
                    Confirm Delete
                </h2>

                <p style="color: #c9d1d9; margin-bottom: 1.5rem; text-align:center;">
                    Are you sure you want to delete this {{ $mode === 'group' ? 'Title' : 'Item' }}?
                </p>

                <div style="display:flex; justify-content: center; gap: 1rem;">
                    <button wire:click="delete" style="background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Yes, Delete
                    </button>

                    <button wire:click="cancelDelete" style="background: #21262d; color: #c9d1d9; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>