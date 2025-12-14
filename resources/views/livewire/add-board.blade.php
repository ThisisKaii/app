<div>
    <!-- Welcome / Create Board Button -->
    <div class="flex justify-between items-center p-4 sm:p-6 bg-gray-900 shadow-sm sm:rounded-lg">
        <a class="auth-button text-sm px-4 py-2" wire:click.prevent="openModal">
            + Create Board
        </a>
    </div>

    <!-- Modal Backdrop -->
    @if($isOpen)
        <div
            style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
            <div
                style="background: #0d1117; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid #30363d;">

                <!-- Header -->
                <div
                    style="padding: 1.5rem; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="color: #f0f6fc; font-size: 1.25rem; margin: 0;">Add New Board</h2>
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
                            <input type="text" wire:model="title" placeholder="Enter board title"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;"
                                required>
                            @error('title')
                                <span
                                    style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Board Type -->
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: #c9d1d9; margin-bottom: 0.75rem; font-weight: 500;">
                                Board Type <span style="color: #ef4444;">*</span>
                            </label>

                            <!-- Radio Button Cards -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">

                                <!-- Normal Board Option -->
                                <div wire:click="$set('list_type', 'Normal')" style="cursor: pointer;">
                                    <div
                                        style="padding: 1rem; background: #161b22; border: 2px solid {{ $list_type === 'Normal' ? '#238636' : '#30363d' }}; border-radius: 8px; transition: all 0.2s;">
                                        <div
                                            style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                            <!-- Icon -->
                                            <svg style="width: 32px; height: 32px; color: {{ $list_type === 'Normal' ? '#238636' : '#8b949e' }};"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>

                                            <!-- Title -->
                                            <div style="text-align: center;">
                                                <div
                                                    style="color: {{ $list_type === 'Normal' ? '#238636' : '#c9d1d9' }}; font-weight: 600; margin-bottom: 0.25rem;">
                                                    Normal
                                                </div>
                                                <div style="color: #8b949e; font-size: 0.75rem; line-height: 1.3;">
                                                    Task management with kanban board
                                                </div>
                                            </div>

                                            <!-- Selected Indicator -->
                                            @if($list_type === 'Normal')
                                                <div style="margin-top: 0.5rem;">
                                                    <svg style="width: 20px; height: 20px; color: #238636;" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Business Board Option -->
                                <div wire:click="$set('list_type', 'Business')" style="cursor: pointer;">
                                    <div
                                        style="padding: 1rem; background: #161b22; border: 2px solid {{ $list_type === 'Business' ? '#238636' : '#30363d' }}; border-radius: 8px; transition: all 0.2s;">
                                        <div
                                            style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                            <!-- Icon -->
                                            <svg style="width: 32px; height: 32px; color: {{ $list_type === 'Business' ? '#238636' : '#8b949e' }};"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>

                                            <!-- Title -->
                                            <div style="text-align: center;">
                                                <div
                                                    style="color: {{ $list_type === 'Business' ? '#238636' : '#c9d1d9' }}; font-weight: 600; margin-bottom: 0.25rem;">
                                                    Business
                                                </div>
                                                <div style="color: #8b949e; font-size: 0.75rem; line-height: 1.3;">
                                                    Budget tracking and expense management
                                                </div>
                                            </div>

                                            <!-- Selected Indicator -->
                                            @if($list_type === 'Business')
                                                <div style="margin-top: 0.5rem;">
                                                    <svg style="width: 20px; height: 20px; color: #238636;" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>

                            @error('list_type')
                                <span
                                    style="color: #ef4444; font-size: 0.75rem; margin-top: 0.5rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Info Box based on selection -->
                        @if($list_type)
                            <div
                                style="padding: 1rem; background: {{ $list_type === 'Normal' ? '#1c2128' : '#1c2128' }}; border-left: 3px solid {{ $list_type === 'Normal' ? '#58a6ff' : '#a371f7' }}; border-radius: 6px; margin-bottom: 1rem;">
                                <div style="display: flex; gap: 0.75rem;">
                                    <svg style="width: 20px; height: 20px; color: {{ $list_type === 'Normal' ? '#58a6ff' : '#a371f7' }}; flex-shrink: 0; margin-top: 2px;"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div style="flex: 1;">
                                        @if($list_type === 'Normal')
                                            <div
                                                style="color: #c9d1d9; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">
                                                Normal Board Features:
                                            </div>
                                            <ul
                                                style="color: #8b949e; font-size: 0.8rem; line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                                                <li>Task management with multiple statuses</li>
                                                <li>Kanban board view</li>
                                                <li>Table view with filters</li>
                                                <li>Task assignments and priorities</li>
                                                <li>Due dates and tags</li>
                                            </ul>
                                        @else
                                            <div
                                                style="color: #c9d1d9; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">
                                                Business Board Features:
                                            </div>
                                            <ul
                                                style="color: #8b949e; font-size: 0.8rem; line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                                                <li>Budget allocation and tracking</li>
                                                <li>Category-based expense management</li>
                                                <li>5-stage approval workflow</li>
                                                <li>Real-time spending calculations</li>
                                                <li>Visual progress indicators</li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- Footer -->
                    <div
                        style="padding: 1rem 1.5rem; border-top: 1px solid #21262d; display: flex; gap: 0.75rem; justify-content: flex-end;">
                        <button type="button" wire:click="closeModal()"
                            style="background: #21262d; color: #c9d1d9; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Cancel
                        </button>
                        <button type="submit"
                            style="background: #238636; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; {{ !$list_type ? 'opacity: 0.5; cursor: not-allowed;' : '' }}"
                            {{ !$list_type ? 'disabled' : '' }}>
                            Create Board
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif
</div>