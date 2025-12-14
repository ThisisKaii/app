<div class="activity-log-container">
    <!-- Header -->
    <div class="activity-header">
        <div class="header-left">
            <h2 class="activity-title">Activity Log</h2>
            <p class="activity-subtitle">Track all changes and updates in your board</p>
        </div>

        <div class="header-actions">
            <button class="filter-btn" wire:click="toggleFilters">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filters
            </button>

            <span class="stat-badge">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ count($activities) }} {{ Str::plural('activity', count($activities)) }}
            </span>
        </div>
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
        <div class="filter-panel active">
            <div class="filter-content">
                <div class="filter-group">
                    <label class="filter-label">Model Type</label>
                    <select class="filter-select" wire:model.live="filterType">
                        <option value="">All Types</option>
                        @foreach($modelTypes as $type)
                            <option value="{{ $type }}">{{ class_basename($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Action</label>
                    <select class="filter-select" wire:model.live="filterAction">
                        <option value="">All Actions</option>
                        @foreach($actionTypes as $action)
                            <option value="{{ $action }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">User</label>
                    <select class="filter-select" wire:model.live="filterUser">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" class="filter-input" placeholder="Search activities..."
                        wire:model.live.debounce.300ms="searchQuery">
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

    <!-- Activity Timeline -->
    <div class="activity-timeline">
        @if(count($activities) > 0)
            @php
                $currentDate = null;
            @endphp

            @foreach($activities as $activity)
                @php
                    $activityDate = $activity->created_at->format('Y-m-d');
                    $showDateSeparator = $currentDate !== $activityDate;
                    $currentDate = $activityDate;
                @endphp

                @if($showDateSeparator)
                    <div class="date-separator">
                        <span class="date-text">
                            @if($activity->created_at->isToday())
                                Today
                            @elseif($activity->created_at->isYesterday())
                                Yesterday
                            @else
                                {{ $activity->created_at->format('F d, Y') }}
                            @endif
                        </span>
                    </div>
                @endif

                <div class="activity-item" wire:key="activity-{{ $activity->id }}">
                    <div class="activity-indicator"
                        style="background-color: {{ $this->getActivityColor($activity->action_type) }}">
                        <div class="flex items-center justify-center w-full h-full">
                            @switch($activity->action_type)
                                @case('created')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    @break
                                @case('updated')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    @break
                                @case('deleted')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    @break
                                @case('status_changed')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    @break
                                @case('assigned')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    @break
                                @case('commented')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    @break
                                @case('completed')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @break
                                @default
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            @endswitch
                        </div>
                    </div>

                    <div class="activity-content">
                        <div class="activity-header-row">
                            <div class="activity-user-info">
                                @if($activity->user)
                                    <div class="activity-avatar">
                                        {{ substr($activity->user->name, 0, 1) }}
                                    </div>
                                    <span class="activity-username">{{ $activity->user->name }}</span>
                                @else
                                    <div class="activity-avatar system-avatar">S</div>
                                    <span class="activity-username">System</span>
                                @endif

                                <span class="activity-action-badge"
                                    style="background-color: {{ $this->getActivityColor($activity->action_type) }}20; color: {{ $this->getActivityColor($activity->action_type) }}">
                                    {{ ucfirst(str_replace('_', ' ', $activity->action_type)) }}
                                </span>
                            </div>

                            <span class="activity-time" title="{{ $activity->created_at->format('F d, Y h:i A') }}">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="activity-description">
                            {{ $activity->description }}
                        </div>

                        <div class="activity-meta">
                            <span class="activity-model-type">
                                <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ class_basename($activity->model_type) }}
                            </span>

                            @if($activity->model_id)
                                <span class="activity-model-id">
                                    <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    ID: {{ $activity->model_id }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if($hasMore)
                <div class="load-more-container">
                    <button class="load-more-btn" wire:click="loadMore">
                        <svg class="load-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        Load More Activities
                    </button>
                </div>
            @endif
        @else
            <div class="empty-state">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="empty-title">No activities found</h3>
                <p class="empty-text">
                    @if($filterType || $filterAction || $filterUser || $searchQuery)
                        Try adjusting your filters to see more activities.
                    @else
                        Activities will appear here as changes are made to your board.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>