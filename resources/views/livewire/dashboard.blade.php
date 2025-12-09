<!-- resources/views/livewire/dashboard.blade.php -->

<div class="min-h-screen bg-[#161b22] text-[#c9d1d9] p-6 md:p-8" x-data wire:poll.15s>
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                {{ $isBusinessBoard ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }} mb-2">
                @if($isBusinessBoard)
                    Business Board
                @else
                    Task Board
                @endif
            </div>
            <h1 class="text-3xl font-bold text-[#f0f6fc]">{{ $board->title }}</h1>
            <p class="text-[#8b949e] mt-1">Analytics Overview</p>
        </div>

        <!-- Date Range Filter -->
        <div class="inline-flex bg-[#0d1117] p-1 rounded-xl border border-[#30363d]">
            @foreach(['all' => 'All Time', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter'] as $key => $label)
                <button wire:click="setDateRange('{{ $key }}')" 
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 
                    {{ $dateRange === $key 
                        ? 'bg-[#21262d] text-[#f0f6fc] border border-[#30363d]' 
                        : 'text-[#8b949e] hover:text-[#c9d1d9] hover:bg-[#21262d]/50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if($isBusinessBoard)
        <!-- ==================== BUSINESS BOARD ANALYTICS ==================== -->
        
        @if(isset($dashboardData['noBudget']) && $dashboardData['noBudget'])
            <div class="flex flex-col items-center justify-center py-20 bg-[#0d1117] rounded-3xl border border-[#30363d] border-dashed">
                <div class="text-6xl mb-4 opacity-50">
                    <svg class="w-16 h-16 mx-auto text-[#8b949e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold mb-2 text-[#f0f6fc]">No Budget Created Yet</h2>
                <p class="text-[#8b949e] mb-6">{{ $dashboardData['message'] }}</p>
                <a href="{{ route('boards.show', $board->id) }}" class="px-6 py-2 bg-[#238636] text-white font-semibold rounded-full hover:bg-[#2ea043] transition-colors">
                    Go to Board
                </a>
            </div>
        @else
            <!-- KPI Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Total Budget -->
                <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d] hover:border-[#8b949e] transition-colors group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400 group-hover:bg-indigo-500/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-xs font-medium text-[#8b949e] bg-[#21262d] px-2 py-1 rounded">Total</div>
                    </div>
                    <div class="text-2xl font-bold mb-1 text-[#f0f6fc]">${{ number_format($dashboardData['totalBudget'], 0) }}</div>
                    <div class="text-sm text-[#8b949e]">Total Allocated Budget</div>
                </div>
                
                <!-- Spent -->
                <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d] hover:border-[#8b949e] transition-colors group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-rose-500/10 rounded-lg text-rose-400 group-hover:bg-rose-500/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="text-xs font-medium text-[#8b949e] bg-[#21262d] px-2 py-1 rounded">Used</div>
                    </div>
                    <div class="text-2xl font-bold mb-1 {{ $dashboardData['spentPercent'] > 90 ? 'text-rose-400' : 'text-[#f0f6fc]' }}">
                        ${{ number_format($dashboardData['totalSpent'], 0) }}
                    </div>
                    <div class="w-full bg-[#21262d] h-1.5 rounded-full mt-3 overflow-hidden">
                        <div class="bg-rose-500 h-1.5 rounded-full transition-all duration-1000" style="width: {{ min($dashboardData['spentPercent'], 100) }}%"></div>
                    </div>
                    <div class="flex justify-between mt-1 text-xs text-[#8b949e]">
                        <span>{{ $dashboardData['spentPercent'] }}%</span>
                        <span>Used</span>
                    </div>
                </div>

                <!-- Remaining -->
                <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d] hover:border-[#8b949e] transition-colors group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400 group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="text-xs font-medium text-[#8b949e] bg-[#21262d] px-2 py-1 rounded">Left</div>
                    </div>
                    <div class="text-2xl font-bold mb-1 {{ $dashboardData['remaining'] < 0 ? 'text-rose-500' : 'text-emerald-400' }}">
                        ${{ number_format(abs($dashboardData['remaining']), 0) }}
                    </div>
                    <div class="text-sm text-[#8b949e]">
                        {{ $dashboardData['remaining'] < 0 ? 'Over Budget' : 'Remaining Funds' }}
                    </div>
                </div>

                <!-- Burn Rate -->
                <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d] hover:border-[#8b949e] transition-colors group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-400 group-hover:bg-amber-500/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="flex items-center gap-1 text-xs font-medium {{ ($dashboardData['spendingTrend'] ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ ($dashboardData['spendingTrend'] ?? 0) > 0 ? '↑' : '↓' }} {{ abs($dashboardData['spendingTrend'] ?? 0) }}%
                        </div>
                    </div>
                    <div class="text-2xl font-bold mb-1 text-[#f0f6fc]">${{ number_format($dashboardData['burnRate'], 0) }}</div>
                    <div class="text-sm text-[#8b949e]">Daily Burn Rate</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Enhanced Analytics Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Category Breakdown (Progress Bars) -->
                    <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-[#f0f6fc]">Budget Allocation</h3>
                            <span class="text-xs text-[#8b949e]">Top Categories</span>
                        </div>
                        <div class="space-y-5">
                            @foreach($dashboardData['categoryBreakdown'] as $category)
                                <div>
                                    <div class="flex justify-between items-end mb-2">
                                        <div>
                                            <div class="font-medium text-sm text-[#c9d1d9]">{{ $category['title'] }}</div>
                                            <div class="text-xs text-[#8b949e]">${{ number_format($category['spent']) }} / ${{ number_format($category['estimated']) }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold text-sm {{ $category['progress'] > 100 ? 'text-rose-400' : 'text-[#f0f6fc]' }}">{{ number_format($category['progress'], 0) }}%</div>
                                        </div>
                                    </div>
                                    <div class="w-full bg-[#21262d] h-2 rounded-full overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-1000 
                                            {{ $category['progress'] > 100 ? 'bg-rose-500' : ($category['progress'] > 80 ? 'bg-amber-500' : 'bg-indigo-500') }}" 
                                            style="width: {{ min($category['progress'], 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-[#f0f6fc]">Recent Transactions</h3>
                            <button wire:click="toggleAllTransactions" class="text-xs text-[#8b949e] hover:text-[#c9d1d9] transition-colors">View All</button>
                        </div>
                        <div class="space-y-4">
                            @forelse($dashboardData['recentExpenses'] as $expense)
                                <div class="flex items-center justify-between p-3 hover:bg-[#21262d] rounded-xl transition-colors group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-[#21262d] flex items-center justify-center border border-[#30363d] group-hover:border-[#8b949e]">
                                            <svg class="w-5 h-5 text-[#8b949e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-sm text-[#c9d1d9]">{{ $expense['category'] }}</div>
                                            <div class="text-xs text-[#8b949e]">{{ Str::limit($expense['description'] ?? 'No description', 30) }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-medium text-sm text-[#f0f6fc]">-${{ number_format($expense['amount'], 2) }}</div>
                                        <div class="text-xs text-[#8b949e]">{{ \Carbon\Carbon::parse($expense['created_at'])->format('M d') }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-[#8b949e]">No recent transactions</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Side Stats Column -->
                <div class="space-y-8">
                    <!-- Status Checks -->
                    <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                        <h3 class="text-lg font-semibold mb-6 text-[#f0f6fc]">Approval Status</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($dashboardData['statusDistribution'] as $status => $count)
                                <div class="p-4 rounded-xl bg-[#161b22] border border-[#30363d]">
                                    <div class="text-2xl font-bold mb-1 text-[#f0f6fc]">{{ $count }}</div>
                                    <div class="text-xs text-[#8b949e] uppercase tracking-wider">{{ ucfirst($status) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Largest Expenses -->
                    <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                        <h3 class="text-lg font-semibold mb-6 text-[#f0f6fc]">Largest Expenses</h3>
                        <div class="space-y-4">
                            @foreach($dashboardData['largestExpenses'] as $expense)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-[#c9d1d9]">{{ $expense['category'] }}</span>
                                    <span class="font-mono text-rose-400">-${{ number_format($expense['amount'], 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-[#30363d]">
                             <div class="flex justify-between items-center">
                                <span class="text-sm text-[#8b949e]">Budget Health</span>
                                @if($dashboardData['budgetHealth'] === 'on_track')
                                    <span class="px-2 py-1 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">On Track</span>
                                @elseif($dashboardData['budgetHealth'] === 'warning')
                                    <span class="px-2 py-1 rounded text-xs bg-amber-500/10 text-amber-400 border border-amber-500/20">Warning</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-rose-500/10 text-rose-400 border border-rose-500/20">Over Budget</span>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d]">
                <div class="text-sm text-[#8b949e] mb-2">Total Tasks</div>
                <div class="text-3xl font-bold text-[#f0f6fc]">{{ $dashboardData['totalTasks'] }}</div>
            </div>
            <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d]">
                <div class="text-sm text-[#8b949e] mb-2">Completion Rate</div>
                <div class="text-3xl font-bold text-emerald-400">{{ $dashboardData['completionRate'] }}%</div>
                <div class="w-full bg-[#21262d] h-1 mt-3 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-1 rounded-full" style="width: {{ $dashboardData['completionRate'] }}%"></div>
                </div>
            </div>
            <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d]">
                <div class="text-sm text-[#8b949e] mb-2">In Progress</div>
                <div class="text-3xl font-bold text-blue-400">{{ $dashboardData['inProgressTasks'] }}</div>
            </div>
            <div class="bg-[#0d1117] rounded-2xl p-5 border border-[#30363d]">
                <div class="text-sm text-[#8b949e] mb-2">Overdue</div>
                <div class="text-3xl font-bold {{ $dashboardData['overdueTasks'] > 0 ? 'text-rose-400' : 'text-[#8b949e]' }}">
                    {{ $dashboardData['overdueTasks'] }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Task Status Bars -->
                <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                    <h3 class="text-lg font-semibold mb-6 text-[#f0f6fc]">Task Composition</h3>
                    <div class="space-y-6">
                        @foreach(['to_do' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review', 'published' => 'Completed'] as $key => $label)
                            @php 
                                $count = $dashboardData['statusDistribution'][$key] ?? 0;
                                $percentage = $dashboardData['totalTasks'] > 0 ? ($count / $dashboardData['totalTasks']) * 100 : 0;
                                $color = match($key) {
                                    'to_do' => 'bg-[#6e7681]',
                                    'in_progress' => 'bg-[#58a6ff]',
                                    'in_review' => 'bg-[#d29922]',
                                    'published' => 'bg-[#3fb950]',
                                };
                            @endphp
                            <div>
                                <div class="flex justify-between mb-2 text-sm">
                                    <span class="text-[#c9d1d9]">{{ $label }}</span>
                                    <span class="font-mono text-[#8b949e]">{{ $count }} tasks</span>
                                </div>
                                <div class="w-full bg-[#21262d] h-3 rounded-full overflow-hidden">
                                    <div class="{{ $color }} h-3 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Recent Tasks List -->
                <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                    <h3 class="text-lg font-semibold mb-6 text-[#f0f6fc]">Recent Activity</h3>
                    <div class="space-y-2 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($dashboardData['recentTasks'] as $task)
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-[#21262d] transition-colors border border-transparent hover:border-[#30363d]">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-[#c9d1d9]">{{ $task->title }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-xs">
                                    @if($task->assignee)
                                        <div class="flex items-center gap-1 text-[#8b949e]">
                                            <span class="w-5 h-5 rounded-full bg-[#21262d] flex items-center justify-center text-[10px] border border-[#30363d]">
                                                {{ substr($task->assignee->name, 0, 1) }}
                                            </span>
                                            {{ $task->assignee->name }}
                                        </div>
                                    @endif
                                    <span class="text-[#8b949e]">{{ $task->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-[#8b949e] text-center py-6">No recent tasks</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Priority Breakdown -->
                <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                    <h3 class="text-lg font-semibold mb-6 text-[#f0f6fc]">By Priority</h3>
                    <div class="space-y-3">
                        @foreach(['high' => 'text-rose-400 bg-rose-400/10 border-rose-400/20', 'medium' => 'text-amber-400 bg-amber-400/10 border-amber-400/20', 'low' => 'text-blue-400 bg-blue-400/10 border-blue-400/20'] as $priority => $classes)
                            <div class="flex items-center justify-between p-3 rounded-xl border {{ $classes }}">
                                <span class="capitalize font-medium">{{ $priority }}</span>
                                <span class="font-bold">{{ $dashboardData['priorityBreakdown'][$priority] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Team Stats -->
                <div class="bg-[#0d1117] rounded-2xl p-6 border border-[#30363d]">
                    <h3 class="text-lg font-semibold mb-6 text-[#f0f6fc]">Top Contributors</h3>
                    <div class="space-y-4">
                        @foreach($dashboardData['assigneeStats'] as $stat)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <div class="w-8 h-8 rounded-full bg-[#21262d] flex items-center justify-center border border-[#30363d] text-xs text-[#c9d1d9]">
                                                {{ substr($stat['assignee'], 0, 1) }}
                                            </div>
                                            <!-- Active status indicator -->
                                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0d1117] {{ $stat['is_active'] ? 'bg-emerald-500' : 'bg-[#6e7681]' }}"></div>
                                        </div>
                                        <div class="text-sm">
                                            <div class="font-medium text-[#c9d1d9]">{{ $stat['assignee'] }}</div>
                                            <div class="text-xs text-[#8b949e]">{{ $stat['completed'] }} / {{ $stat['total'] }} tasks</div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-bold text-[#f0f6fc]">{{ $stat['percentage'] }}%</div>
                                </div>
                                <div class="w-full bg-[#21262d] h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-1000" style="width: {{ $stat['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- All Transactions Modal -->
    @if($showAllTransactions && $isBusinessBoard)
        <div class="modal-overlay" wire:click.self="toggleAllTransactions">
            <div class="modal-content budget-modal" wire:click.stop style="max-width: 800px;">
                <div class="modal-header">
                    <h2>All Transactions</h2>
                    <button type="button" wire:click="toggleAllTransactions" class="modal-close">&times;</button>
                </div>

                <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="space-y-3">
                        @forelse($dashboardData['recentExpenses'] ?? [] as $expense)
                            <div class="flex items-center justify-between p-4 hover:bg-[#21262d] rounded-xl transition-colors border border-[#30363d]">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#21262d] flex items-center justify-center text-xl border border-[#30363d]">
                                        🧾
                                    </div>
                                    <div>
                                        <div class="font-medium text-base text-[#c9d1d9]">{{ $expense['category'] }}</div>
                                        <div class="text-sm text-[#8b949e]">{{ $expense['description'] ?? 'No description' }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-mono font-bold text-base text-rose-400">-${{ number_format($expense['amount'], 2) }}</div>
                                    <div class="text-sm text-[#8b949e]">{{ \Carbon\Carbon::parse($expense['created_at'])->format('M d, Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-[#8b949e]">
                                <div class="text-4xl mb-3">
                                    <svg class="w-12 h-12 mx-auto text-[#8b949e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <div>No transactions found</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" wire:click="toggleAllTransactions" class="btn-cancel">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>