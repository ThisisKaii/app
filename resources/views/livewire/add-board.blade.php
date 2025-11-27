<div>
    <!-- Welcome / Create Board Button -->
    <div class="flex justify-between items-center p-4 sm:p-6 bg-gray-900 shadow-sm sm:rounded-lg">
        <a class="auth-button text-sm px-4 py-2" wire:click.prevent="openModal">
            + Create Board
        </a>
    </div>

    <!-- Modal Backdrop -->
    @if($isOpen)
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
        <div style="background: #0d1117; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #30363d;">
            
            <!-- Header -->
            <div style="padding: 1.5rem; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: #f0f6fc; font-size: 1.25rem; margin: 0;">Add New Board</h2>
                <button type="button" wire:click="closeModal" style="background: none; border: none; color: #8b949e; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
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
                               placeholder="Enter board title"
                               style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                               required>
                        @error('title') 
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- View Type -->
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">View Type <span style="color: #ef4444;">*</span></label>
                        <select wire:model="list_type" 
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                            <option value="">Choose type</option>
                            <option value="normal">Normal</option>
                            <option value="business">Business</option>
                        </select>
                        @error('list_type') 
                            <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> 
                        @enderror
                    </div>

                </div>

                <!-- Footer -->
                <div style="padding: 1rem 1.5rem; border-top: 1px solid #21262d; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" 
                            wire:click="closeModal()" 
                            style="background: #21262d; color: #c9d1d9; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="background: #238636; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Create Board
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endif
</div>
