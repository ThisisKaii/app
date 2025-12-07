<div class="board-list-container">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-left">
            <!-- Search Box -->
            <div class="search-box">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search boards..."
                    class="search-input">
                @if($searchQuery)
                    <button wire:click="clearSearch" class="clear-search">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Type Filter Tabs -->
            <div class="filter-tabs">
                <button wire:click="setFilter('all')" class="filter-tab {{ $filterType === 'all' ? 'active' : '' }}">
                    <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    All Boards
                </button>

                <button wire:click="setFilter('business')"
                    class="filter-tab {{ $filterType === 'business' ? 'active' : '' }}">
                    <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Business
                </button>

                <button wire:click="setFilter('normal')"
                    class="filter-tab {{ $filterType === 'normal' ? 'active' : '' }}">
                    <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Normal
                </button>
            </div>
        </div>

        <!-- Results Count -->
        <div class="results-count">
            {{ count($boards) }} {{ Str::plural('board', count($boards)) }}
        </div>
    </div>

    <!-- Boards Grid -->
    @if(count($boards) > 0)
        <div class="boards-grid">
            @foreach($boards as $board)
                @php
                    $memberCount = $board->members()->count();
                    $userMember = $board->members()->where('user_id', auth()->id())->first();
                    $userRole = $userMember ? $userMember->pivot->role : 'member';
                @endphp

                <a href="{{ route('boards.show', $board) }}" class="board-card" wire:key="board-{{ $board->id }}">

                    <!-- Card Header -->
                    <div class="board-card-header">
                        <!-- Type badge -->
                        <div class="board-type-badge board-type-{{ $board->list_type }}">
                            @if($board->list_type === 'business')
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Business
                            @else
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Normal
                            @endif
                        </div>

                        <!-- Right side buttons container -->
                        <div class="board-header-right">
                            <!-- Role badge -->
                            @if($userRole === 'owner')
                                <div class="role-badge role-owner">Owner</div>
                            @elseif($userRole === 'admin')
                                <div class="role-badge role-admin">Admin</div>
                            @endif

                            <!-- Action buttons -->
                            <div class="board-actions">
                                <!-- Delete button (only owner) -->
                                @if($userRole === 'owner')
                                    <div class="board-menu">
                                        <button class="menu-btn" wire:click.prevent="confirmDelete({{ $board->id }})"
                                            wire:key="delete-btn-{{ $board->id }}" title="Delete board">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif

                                <!-- Rename button (owner or admin) -->
                                @if(in_array($userRole, ['owner', 'admin']))
                                    <div class="board-menu">
                                        <button class="menu-btn rename-btn" wire:click.prevent="openRenameModal({{ $board->id }})"
                                            wire:key="rename-btn-{{ $board->id }}" title="Rename board">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Board Title -->
                    <h3 class="board-card-title">{{ $board->title }}</h3>

                    <!-- Board Stats -->
                    <div class="board-card-stats">
                        <div class="stat-item">
                            <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span>{{ $board->tasks_count }} {{ Str::plural('task', $board->tasks_count) }}</span>
                        </div>

                        <div class="stat-item">
                            <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>{{ $memberCount }} {{ Str::plural('member', $memberCount) }}</span>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="board-card-footer">
                        <svg class="footer-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Updated {{ $board->updated_at->diffForHumans() }}</span>
                    </div>

                    <!-- Hover Effect Overlay -->
                    <div class="board-card-overlay">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="empty-title">
                @if($searchQuery || $filterType !== 'all')
                    No boards found
                @else
                    No boards yet
                @endif
            </h3>
            <p class="empty-text">
                @if($searchQuery)
                    Try adjusting your search or filters
                @elseif($filterType !== 'all')
                    No {{ $filterType }} boards found. Try changing the filter.
                @else
                    Create your first board or wait to be added to one
                @endif
            </p>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($deleteModalOpen)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-content dark-modal" wire:click.stop>
                <div class="modal-header">
                    <h3 class="modal-title">Delete Board</h3>
                    <button class="modal-close" wire:click="closeDeleteModal">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="warning-icon">
                        <svg width="48" height="48" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.948-.833-2.678 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <p class="modal-text">Are you sure you want to delete "<strong>{{ $deleteBoardTitle }}</strong>"?</p>
                    <p class="modal-subtext">This action cannot be undone. All tasks and data will be permanently removed.
                    </p>
                </div>

                <div class="modal-footer">
                    <button class="btn-secondary" wire:click="closeDeleteModal">
                        Cancel
                    </button>
                    <button class="btn-danger" wire:click="deleteBoard">
                        <svg wire:loading.remove wire:target="deleteBoard" width="16" height="16" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span wire:loading wire:target="deleteBoard" class="loading-spinner"></span>
                        Delete Board
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Rename Modal -->
    @if($renameModalOpen)
        <div class="modal-overlay" wire:click="closeRenameModal">
            <div class="modal-content dark-modal" wire:click.stop>
                <div class="modal-header">
                    <h3 class="modal-title">Rename Board</h3>
                    <button class="modal-close" wire:click="closeRenameModal">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="warning-icon">
                        <svg width="48" height="48" fill="none" stroke="#3b82f6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <p class="modal-text">Rename "<strong>{{ $renameBoardTitle }}</strong>"</p>

                    <div class="form-group mt-4">
                        <label for="newBoardTitle" class="form-label">New Board Name</label>
                        <input type="text" id="newBoardTitle" wire:model="newBoardTitle" wire:keydown.enter="renameBoard"
                            class="form-input" placeholder="Enter new board name" autofocus>
                        @error('newBoardTitle')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn-secondary" wire:click="closeRenameModal">
                        Cancel
                    </button>
                    <button class="btn-primary" wire:click="renameBoard">
                        <svg wire:loading.remove wire:target="renameBoard" width="16" height="16" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading wire:target="renameBoard" class="loading-spinner"></span>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>