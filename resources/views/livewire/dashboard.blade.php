
<!-- resources/views/livewire/dashboard.blade.php -->

<div class="dashboard-wrapper" x-data wire:poll.15s.keep-alive>
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">{{ $board->title }}</h1>
            <p class="dashboard-subtitle">Analytics Overview</p>
        </div>

        <!-- Date Range Filter -->
        <div class="date-filter">
            @foreach(['all' => 'All Time', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter'] as $key => $label)
                <button wire:click="setDateRange('{{ $key }}')" 
                    class="filter-btn {{ $dateRange === $key ? 'active' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if($isBusinessBoard)
        <!-- ==================== BUSINESS BOARD ANALYTICS ==================== -->
        
        @if(isset($dashboardData['noBudget']) && $dashboardData['noBudget'])
            <div class="stat-card" style="text-align: center; padding: 4rem; border-style: dashed;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">
                    <svg class="stat-icon" style="width: 4rem; height: 4rem; margin: 0 auto; color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="card-title" style="margin-bottom: 0.5rem;">No Budget Created Yet</h2>
                <p class="stat-subtext" style="margin-bottom: 1.5rem;">{{ $dashboardData['message'] }}</p>
                <a href="{{ route('boards.show', $board->id) }}" class="btn-primary" style="display: inline-block;">
                    Go to Board
                </a>
            </div>
        @else
            <!-- KPI Grid -->
            <div class="kpi-grid">
                <!-- Total Budget -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper bg-indigo-subtle">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-value">${{ number_format($dashboardData['totalBudget'], 0) }}</div>
                    <div class="stat-subtext">Total Allocated Budget</div>
                </div>
                
                <!-- Spent -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper bg-rose-subtle">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="stat-label">Used</div>
                    </div>
                    <div class="stat-value {{ $dashboardData['spentPercent'] > 90 ? 'text-rose' : '' }}">
                        ${{ number_format($dashboardData['totalSpent'], 0) }}
                    </div>
                    <div class="progress-container">
                        <div class="progress-fill bg-rose" style="width: {{ min($dashboardData['spentPercent'], 100) }}%"></div>
                    </div>
                    <div class="stat-subtext" style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                        <span>{{ $dashboardData['spentPercent'] }}%</span>
                        <span>Used</span>
                    </div>
                </div>

                <!-- Remaining -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper bg-emerald-subtle">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="stat-label">{{ $dashboardData['remaining'] < 0 ? 'Over' : 'Left' }}</div>
                    </div>
                    <div class="stat-value {{ $dashboardData['remaining'] < 0 ? 'text-rose' : 'text-emerald' }}">
                        ${{ number_format(abs($dashboardData['remaining']), 0) }}
                    </div>
                    <div class="stat-subtext">
                        {{ $dashboardData['remaining'] < 0 ? 'Over Budget' : 'Remaining Funds' }}
                    </div>
                </div>

                <!-- Burn Rate -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper bg-amber-subtle">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="stat-label {{ ($dashboardData['spendingTrend'] ?? 0) > 0 ? 'text-rose' : 'text-emerald' }}">
                            {{ ($dashboardData['spendingTrend'] ?? 0) > 0 ? '↑' : '↓' }} {{ abs($dashboardData['spendingTrend'] ?? 0) }}%
                        </div>
                    </div>
                    <div class="stat-value">${{ number_format($dashboardData['burnRate'], 0) }}</div>
                    <div class="stat-subtext">Daily Burn Rate</div>
                </div>
            </div>

            <div class="dashboard-columns">
                <!-- Enhanced Analytics Column -->
                <div style="grid-column: span 1; display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Category Breakdown (Progress Bars) -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">Budget Allocation</h3>
                            <span class="stat-subtext">Top Categories</span>
                        </div>
                        <div class="item-list">
                            @foreach($dashboardData['categoryBreakdown'] as $category)
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem;">
                                        <div>
                                            <div class="item-title">{{ $category['title'] }}</div>
                                            <div class="item-subtitle">${{ number_format($category['spent']) }} / ${{ number_format($category['estimated']) }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="item-title {{ $category['progress'] > 100 ? 'text-rose' : '' }}">{{ number_format($category['progress'], 0) }}%</div>
                                        </div>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-fill {{ $category['progress'] > 100 ? 'bg-rose' : ($category['progress'] > 80 ? 'bg-amber' : 'bg-indigo') }}" 
                                            style="width: {{ min($category['progress'], 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Transactions</h3>
                            <button wire:click="toggleAllTransactions" class="stat-subtext" style="background: none; border: none; cursor: pointer; text-decoration: underline;">View All</button>
                        </div>
                        <div class="item-list">
                            @forelse($dashboardData['recentExpenses'] as $expense)
                                <div class="list-item">
                                    <div class="item-left">
                                        <div class="avatar-circle">
                                            <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="item-title">{{ $expense['category'] }}</div>
                                            <div class="item-subtitle">{{ Str::limit($expense['description'] ?? 'No description', 30) }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="item-title text-rose" style="font-family: monospace;">-${{ number_format($expense['amount'], 2) }}</div>
                                        <div class="item-subtitle">{{ \Carbon\Carbon::parse($expense['created_at'])->format('M d') }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-[#8b949e]">No recent transactions</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Side Stats Column -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Status Checks -->
                    <div class="content-card">
                        <h3 class="card-title" style="margin-bottom: 1.5rem;">Approval Status</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            @foreach($dashboardData['statusDistribution'] as $status => $count)
                                <div style="padding: 1rem; border-radius: 0.75rem; background: #161b22; border: 1px solid var(--border-color);">
                                    <div class="stat-value">{{ $count }}</div>
                                    <div class="stat-subtext" style="text-transform: uppercase; letter-spacing: 0.05em;">{{ ucfirst($status) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Largest Expenses -->
                    <div class="content-card">
                        <h3 class="card-title" style="margin-bottom: 1.5rem;">Largest Expenses</h3>
                        <div class="item-list">
                            @foreach($dashboardData['largestExpenses'] as $expense)
                                <div class="list-item">
                                    <span class="item-title">{{ $expense['category'] }}</span>
                                    <span class="item-title text-rose" style="font-family: monospace;">-${{ number_format($expense['amount'], 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                             <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="stat-subtext">Budget Health</span>
                                @if($dashboardData['budgetHealth'] === 'on_track')
                                    <span class="stat-label bg-emerald-subtle">On Track</span>
                                @elseif($dashboardData['budgetHealth'] === 'warning')
                                    <span class="stat-label bg-amber-subtle">Warning</span>
                                @else
                                    <span class="stat-label bg-rose-subtle">Over Budget</span>
                                @endif
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @else
        <!-- ==================== NORMAL (TASK) DASHBOARD ==================== -->
        
        <!-- Task Metrics -->
        <div class="kpi-grid">
            <div class="stat-card">
                <div class="stat-subtext" style="margin-bottom: 0.5rem;">Total Tasks</div>
                <div class="stat-value">{{ $dashboardData['totalTasks'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-subtext" style="margin-bottom: 0.5rem;">Completion Rate</div>
                <div class="stat-value text-emerald">{{ $dashboardData['completionRate'] }}%</div>
                <div class="progress-container">
                    <div class="progress-fill bg-emerald" style="width: {{ $dashboardData['completionRate'] }}%"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-subtext" style="margin-bottom: 0.5rem;">Active</div>
                <div class="stat-value text-blue">{{ $dashboardData['activeTasks'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-subtext" style="margin-bottom: 0.5rem;">Overdue</div>
                <div class="stat-value {{ $dashboardData['overdueTasks'] > 0 ? 'text-rose' : 'text-secondary' }}">
                    {{ $dashboardData['overdueTasks'] }}
                </div>
            </div>
        </div>

        <div class="dashboard-columns">
            <!-- Left Column -->
            <div style="grid-column: span 1; display: flex; flex-direction: column; gap: 2rem;">
                <!-- Recommended Focus Widget -->
                @if(isset($dashboardData['recommendedTasks']) && count($dashboardData['recommendedTasks']) > 0)
                    <div class="content-card recommended-card">
                        <svg class="recommended-bg-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z"/>
                        </svg>
                        <div style="position: relative; z-index: 10;">
                            <div class="recommended-label" style="margin-bottom: 1rem;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Recommended Focus
                            </div>
                            
                            <div class="recommended-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach($dashboardData['recommendedTasks'] as $task)
                                    <div class="rec-task-item" style="padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 6px; border-left: 3px solid {{ $task->priority === 'high' ? '#f43f5e' : ($task->priority === 'medium' ? '#f59e0b' : '#3b82f6') }}; transition: background 0.2s; cursor: pointer;"
                                         wire:click="$dispatch('open-task-modal', { taskId: {{ $task->id }} })"
                                         onmouseover="this.style.background='rgba(0,0,0,0.3)'"
                                         onmouseout="this.style.background='rgba(0,0,0,0.2)'">
                                        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.25rem; color: #fff;">{{ $task->title }}</div>
                                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7); display: flex; align-items: center; gap: 12px;">
                                            <span>{{ ucfirst($task->priority ?? 'normal') }} Priority</span>
                                            @if($task->due_date)
                                                <span>• Due {{ $task->due_date->format('M d') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recent Tasks List -->
                <div class="content-card">
                    <h3 class="card-title" style="margin-bottom: 1.5rem;">Recent Activity</h3>
                    <div class="item-list custom-scrollbar" style="max-height: 350px; overflow-y: auto; padding-right: 0.5rem;">
                        @forelse($dashboardData['recentTasks'] as $task)
                            <div class="list-item">
                                <div class="item-left">
                                    <span class="item-title">{{ $task->title }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.75rem;">
                                    @if($task->assignee)
                                        <div style="display: flex; align-items: center; gap: 0.25rem; color: var(--text-secondary);">
                                            <span class="avatar-circle" style="width: 1.25rem; height: 1.25rem; font-size: 0.625rem;">
                                                {{ substr($task->assignee->name, 0, 1) }}
                                            </span>
                                            {{ $task->assignee->name }}
                                        </div>
                                    @endif
                                    <span class="stat-subtext">{{ $task->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">No recent tasks</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Priority Breakdown -->
                <div class="content-card">
                    <h3 class="card-title" style="margin-bottom: 1.5rem;">By Priority</h3>
                    <div class="item-list">
                        @foreach(['high' => 'bg-rose-subtle', 'medium' => 'bg-amber-subtle', 'low' => 'bg-blue-subtle'] as $priority => $classes)
                            <div class="list-item {{ $classes }}" style="border: 1px solid rgba(255,255,255,0.1);">
                                <span style="text-transform: capitalize; font-weight: 500;">{{ $priority }}</span>
                                <span style="font-weight: 700;">{{ $dashboardData['priorityBreakdown'][$priority] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Team Stats -->
                <div class="content-card">
                    <h3 class="card-title" style="margin-bottom: 1.5rem;">Top Contributors</h3>
                    <div class="item-list">
                        @forelse($dashboardData['assigneeStats'] as $stat)
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <div class="item-left">
                                        <div style="position: relative;">
                                            <div class="avatar-circle">
                                                {{ substr($stat['assignee'], 0, 1) }}
                                            </div>
                                            <div style="position: absolute; bottom: -2px; right: -2px; width: 0.75rem; height: 0.75rem; border-radius: 50%; border: 2px solid #0d1117; background-color: {{ $stat['is_active'] ? '#10b981' : '#6e7681' }};"></div>
                                        </div>
                                        <div>
                                            <div class="item-title">{{ $stat['assignee'] }}</div>
                                            <div class="item-subtitle">{{ $stat['completed'] }} / {{ $stat['total'] }} tasks</div>
                                        </div>
                                    </div>
                                    <div class="item-title">{{ $stat['percentage'] }}%</div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill bg-emerald" style="width: {{ $stat['percentage'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                <svg style="width: 2rem; height: 2rem; margin: 0 auto 0.5rem; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div>No assigned tasks yet</div>
                                <div class="item-subtitle">Assign tasks to team members to see contributions</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- All Transactions Modal -->
    @if($showAllTransactions && $isBusinessBoard)
        <div class="modal-overlay" wire:click.self="toggleAllTransactions">
            <div class="modal-content dark-modal" wire:click.stop style="max-width: 800px;">
                <div class="modal-header">
                    <h3 class="modal-title">All Transactions</h3>
                    <button type="button" wire:click="toggleAllTransactions" class="modal-close">&times;</button>
                </div>

                <div class="modal-body" style="max-height: 600px; overflow-y: auto; text-align: left;">
                    <div class="item-list">
                        @forelse($dashboardData['recentExpenses'] ?? [] as $expense)
                            <div class="list-item" style="border: 1px solid var(--border-color);">
                                <div class="item-left">
                                    <div class="avatar-circle" style="width: 3rem; height: 3rem; font-size: 1.5rem;">
                                        🧾
                                    </div>
                                    <div>
                                        <div class="item-title" style="font-size: 1rem;">{{ $expense['category'] }}</div>
                                        <div class="item-subtitle">{{ $expense['description'] ?? 'No description' }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="item-title text-rose" style="font-family: monospace; font-size: 1rem;">-${{ number_format($expense['amount'], 2) }}</div>
                                    <div class="item-subtitle">{{ \Carbon\Carbon::parse($expense['created_at'])->format('M d, Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">
                                    <svg style="width: 3rem; height: 3rem; margin: 0 auto; color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <div>No transactions found</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" wire:click="toggleAllTransactions" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>