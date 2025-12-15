<div class="members-list-container" wire:poll.5s.keep-alive>
    <!-- Header -->
    <div class="table-header">
        <h2 class="table-title">Board Members</h2>
        <div class="table-stats">
            <button class="filter-btn" wire:click="toggleFilters">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>

            @if($currentUserRole && in_array($currentUserRole, ['owner', 'admin']))
                <button class="add-task-btn" wire:click="openAddModal">
                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Member
                </button>
            @endif

            <span class="stat-badge">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ count($members) }} {{ Str::plural('member', count($members)) }}
            </span>
        </div>
    </div>

    <!-- Filters -->
    @if($showFilters)
        <div class="filter-panel active">
            <div class="filter-content">
                <div class="filter-group">
                    <label class="filter-label">Role</label>
                    <select class="filter-select" wire:model.live="roleFilter">
                        <option value="">All Roles</option>
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                        <option value="member">Member</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" class="filter-input" placeholder="Search by name or email..."
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

    <!-- Members Table -->
    @if(count($members) > 0)
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $member)
                        <tr wire:key="member-{{ $member->id }}" class="table-row">
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span class="task-title">{{ $member->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="type-text">{{ $member->email }}</span>
                            </td>
                            <td>
                                @if($currentUserRole && in_array($currentUserRole, ['owner', 'admin']) && isset($member->pivot) && $member->pivot->role !== 'owner')
                                    <select 
                                        onchange="@this.call('updateRole', {{ $member->id }}, this.value)"
                                        style="padding: 0.375rem 0.75rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                        <option value="member" {{ isset($member->pivot) && $member->pivot->role === 'member' ? 'selected' : '' }}>Member</option>
                                        <option value="admin" {{ isset($member->pivot) && $member->pivot->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                @else
                                    <span class="status-badge" style="background: rgba(88, 166, 255, 0.2); color: #58a6ff;">
                                        {{ isset($member->pivot) ? ucfirst($member->pivot->role) : 'Member' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="date-text">
                                    {{ isset($member->pivot) && $member->pivot->created_at ? \Carbon\Carbon::parse($member->pivot->created_at)->format('M d, Y') : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($currentUserRole && in_array($currentUserRole, ['owner', 'admin']) && isset($member->pivot) && $member->pivot->role !== 'owner')
                                    <button wire:click="askDelete({{ $member->id }})"
                                        style="background: transparent; border: 1px solid #ef4444; color: #ef4444; padding: 0.375rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500;">
                                        Remove
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
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
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="empty-title">No members found</h3>
            <p class="empty-text">Try adjusting your filters</p>
        </div>
    @endif

    <!-- Add Member Modal -->
    @if($showAddModal)
        <div wire:click="closeAddModal"
            style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center;">
            <div wire:click.stop
                style="background: #0d1117; border-radius: 8px; width: 90%; max-width: 500px; border: 1px solid #30363d;">

                <div style="padding: 1.5rem; border-bottom: 1px solid #21262d;">
                    <h2 style="color: #f0f6fc; font-size: 1.25rem; margin: 0;">Add New Member</h2>
                </div>

                <form wire:submit.prevent="addMember">
                    <div style="padding: 1.5rem;">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">
                                Email <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="email" wire:model="email" placeholder="member@example.com"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                            @error('email') <span style="color: #ef4444; font-size: 0.75rem;">{{ $message }}</span> @enderror
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; color: #c9d1d9; margin-bottom: 0.5rem; font-weight: 500;">Role</label>
                            <select wire:model="role"
                                style="width: 100%; padding: 0.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 6px; color: #c9d1d9; font-size: 0.875rem;">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div style="padding: 1rem 1.5rem; border-top: 1px solid #21262d; display: flex; gap: 0.75rem; justify-content: flex-end;">
                        <button type="button" wire:click="closeAddModal"
                            style="background: #21262d; color: #c9d1d9; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Cancel
                        </button>
                        <button type="submit"
                            style="background: #238636; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Add Member
                        </button>
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
                    Remove Member
                </h2>

                <p style="color: #c9d1d9; margin-bottom: 1.5rem; text-align:center;">
                    Are you sure you want to remove this member from the board?
                </p>

                <div style="display:flex; justify-content: center; gap: 1rem;">
                    <button wire:click="removeMember"
                        style="background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Yes, Remove
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