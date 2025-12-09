<!-- resources/views/livewire/dashboard.blade.php -->

<div class="container dashboard-container" x-data="dashboardCharts(@js($dashboardData), @js($isBusinessBoard))" x-init="init()">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-left">
            <div class="board-badge">
                @if($isBusinessBoard)
                    💼 Business
                @else
                    📊 Task
                @endif
            </div>
            <div>
                <h1 class="dashboard-title">{{ $board->title }}</h1>
                <p class="dashboard-subtitle">Dashboard Overview</p>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="date-filter">
            <div class="filter-group">
                <button wire:click="setDateRange('all')" class="filter-btn {{ $dateRange === 'all' ? 'active' : '' }}">
                    All Time
                </button>
                <button wire:click="setDateRange('week')" class="filter-btn {{ $dateRange === 'week' ? 'active' : '' }}">
                    This Week
                </button>
                <button wire:click="setDateRange('month')" class="filter-btn {{ $dateRange === 'month' ? 'active' : '' }}">
                    This Month
                </button>
                <button wire:click="setDateRange('quarter')" class="filter-btn {{ $dateRange === 'quarter' ? 'active' : '' }}">
                    This Quarter
                </button>
            </div>
        </div>
    </div>

    @if($isBusinessBoard)
        <!-- ==================== BUSINESS BOARD DASHBOARD ==================== -->
        
        @if(isset($dashboardData['noBudget']) && $dashboardData['noBudget'])
            <div class="empty-state">
                <div class="empty-icon">💰</div>
                <h2>No Budget Created Yet</h2>
                <p class="empty-description">{{ $dashboardData['message'] }}</p>
                <a href="{{ route('boards.show', $board->id) }}" class="btn-primary">
                    Go to Board
                </a>
            </div>
        @else
            <!-- Financial Metrics -->
            <div class="metrics-grid">
                <div class="metric-card card">
                    <div class="metric-icon">💵</div>
                    <div class="metric-content">
                        <div class="metric-label">Total Budget</div>
                        <div class="metric-value">${{ number_format($dashboardData['totalBudget'], 2) }}</div>
                    </div>
                </div>
                
                <div class="metric-card card">
                    <div class="metric-icon">📊</div>
                    <div class="metric-content">
                        <div class="metric-label">Allocated</div>
                        <div class="metric-value info">${{ number_format($dashboardData['totalAllocated'], 2) }}</div>
                        <div class="metric-subtitle">{{ $dashboardData['allocatedPercent'] }}% of budget</div>
                    </div>
                </div>
                
                <div class="metric-card card">
                    <div class="metric-icon">💸</div>
                    <div class="metric-content">
                        <div class="metric-label">Spent</div>
                        <div class="metric-value {{ $dashboardData['spentPercent'] > 80 ? 'warning' : 'success' }}">
                            ${{ number_format($dashboardData['totalSpent'], 2) }}
                        </div>
                        <div class="metric-subtitle">{{ $dashboardData['spentPercent'] }}% of budget</div>
                    </div>
                </div>
                
                <div class="metric-card card">
                    <div class="metric-icon">💰</div>
                    <div class="metric-content">
                        <div class="metric-label">Remaining</div>
                        <div class="metric-value {{ $dashboardData['remaining'] < 0 ? 'danger' : 'success' }}">
                            ${{ number_format(abs($dashboardData['remaining']), 2) }}
                        </div>
                        @if($dashboardData['remaining'] < 0)
                            <div class="metric-subtitle danger">Over Budget!</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Budget Health -->
            <div class="section card">
                <div class="section-header">
                    <h3 class="section-title">Budget Health</h3>
                </div>
                <div class="budget-health">
                    @if($dashboardData['budgetHealth'] === 'on_track')
                        <div class="health-status on-track">
                            <span class="health-icon">✅</span>
                            <div class="health-content">
                                <div class="health-title">On Track</div>
                                <div class="health-description">Spending is within budget limits</div>
                            </div>
                        </div>
                    @elseif($dashboardData['budgetHealth'] === 'warning')
                        <div class="health-status warning">
                            <span class="health-icon">⚠️</span>
                            <div class="health-content">
                                <div class="health-title">Warning</div>
                                <div class="health-description">80%+ of budget spent</div>
                            </div>
                        </div>
                    @else
                        <div class="health-status danger">
                            <span class="health-icon">🚨</span>
                            <div class="health-content">
                                <div class="health-title">Over Budget</div>
                                <div class="health-description">Exceeded allocated budget</div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="health-stats">
                        <div class="health-stat">
                            <div class="stat-value">{{ $dashboardData['categoriesAtRisk'] }}</div>
                            <div class="stat-label">At Risk</div>
                        </div>
                        <div class="divider"></div>
                        <div class="health-stat">
                            <div class="stat-value">{{ $dashboardData['categoriesOverBudget'] }}</div>
                            <div class="stat-label">Over Budget</div>
                        </div>
                        <div class="divider"></div>
                        <div class="health-stat">
                            <div class="stat-value">${{ number_format($dashboardData['burnRate'], 2) }}</div>
                            <div class="stat-label">Burn Rate/Day</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <!-- Category Allocation Pie Chart -->
                <div class="chart-section card">
                    <div class="section-header">
                        <h3 class="section-title">Budget Allocation</h3>
                    </div>
                    <div class="chart-container" id="categoryPieChart"></div>
                </div>
                
                <!-- Estimated vs Spent Bar Chart -->
                <div class="chart-section card">
                    <div class="section-header">
                        <h3 class="section-title">Estimated vs Spent</h3>
                    </div>
                    <div class="chart-container" id="budgetComparisonChart"></div>
                </div>
            </div>

            <!-- Monthly Spending Trend -->
            <div class="chart-section card">
                <div class="section-header">
                    <h3 class="section-title">Monthly Spending Trend</h3>
                </div>
                <div class="chart-container" id="monthlyTrendChart"></div>
            </div>

            <!-- Status Distribution & Top Spending -->
            <div class="charts-grid">
                <!-- Status Distribution -->
                <div class="chart-section card">
                    <div class="section-header">
                        <h3 class="section-title">Status Distribution</h3>
                    </div>
                    <div class="chart-container" id="statusPieChart"></div>
                </div>
                
                <!-- Top Spending Categories -->
                <div class="list-section card">
                    <div class="section-header">
                        <h3 class="section-title">Top Spending Categories</h3>
                    </div>
                    <div class="list-content">
                        @forelse($dashboardData['topSpending'] as $category)
                            <div class="list-item">
                                <div class="list-item-icon">
                                    <div class="category-dot" style="background-color: {{ $category->color ?? '#3b82f6' }}"></div>
                                </div>
                                <div class="list-item-content">
                                    <div class="list-item-title">{{ $category->title }}</div>
                                    <div class="list-item-meta">
                                        ${{ number_format($category->amount_estimated, 2) }} estimated · 
                                        {{ number_format($category->getProgressPercentage(), 0) }}% spent
                                    </div>
                                </div>
                                <div class="list-item-value {{ $category->isOverBudget() ? 'danger' : '' }}">
                                    ${{ number_format($category->getTotalSpent(), 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="empty-list">
                                <p>No categories yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent & Largest Expenses -->
            <div class="charts-grid">
                <!-- Recent Expenses -->
                <div class="list-section card">
                    <div class="section-header">
                        <h3 class="section-title">
                            Recent Expenses
                            <span class="badge">{{ $dashboardData['totalExpenses'] }}</span>
                        </h3>
                    </div>
                    <div class="list-content">
                        @forelse($dashboardData['recentExpenses'] as $expense)
                            <div class="list-item">
                                <div class="list-item-icon">
                                    <div class="expense-icon">📝</div>
                                </div>
                                <div class="list-item-content">
                                    <div class="list-item-title">{{ $expense['category'] }}</div>
                                    <div class="list-item-meta">
                                        {{ $expense['description'] ?? 'No description' }} · 
                                        {{ \Carbon\Carbon::parse($expense['created_at'])->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="list-item-value">${{ number_format($expense['amount'], 2) }}</div>
                            </div>
                        @empty
                            <div class="empty-list">
                                <p>No expenses yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Largest Expenses -->
                <div class="list-section card">
                    <div class="section-header">
                        <h3 class="section-title">Largest Expenses</h3>
                    </div>
                    <div class="list-content">
                        @forelse($dashboardData['largestExpenses'] as $expense)
                            <div class="list-item">
                                <div class="list-item-icon">
                                    <div class="expense-icon">💰</div>
                                </div>
                                <div class="list-item-content">
                                    <div class="list-item-title">{{ $expense['category'] }}</div>
                                    <div class="list-item-meta">{{ $expense['description'] ?? 'No description' }}</div>
                                </div>
                                <div class="list-item-value danger">${{ number_format($expense['amount'], 2) }}</div>
                            </div>
                        @empty
                            <div class="empty-list">
                                <p>No expenses yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
        
    @else
        <!-- ==================== NORMAL BOARD DASHBOARD ==================== -->
        
        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card card">
                <div class="metric-icon">📋</div>
                <div class="metric-content">
                    <div class="metric-label">Total Tasks</div>
                    <div class="metric-value">{{ $dashboardData['totalTasks'] }}</div>
                </div>
            </div>
            
            <div class="metric-card card">
                <div class="metric-icon">✅</div>
                <div class="metric-content">
                    <div class="metric-label">Completed</div>
                    <div class="metric-value success">{{ $dashboardData['completedTasks'] }}</div>
                    <div class="metric-subtitle">{{ $dashboardData['completionRate'] }}% completion rate</div>
                </div>
            </div>
            
            <div class="metric-card card">
                <div class="metric-icon">🔄</div>
                <div class="metric-content">
                    <div class="metric-label">In Progress</div>
                    <div class="metric-value info">{{ $dashboardData['inProgressTasks'] }}</div>
                </div>
            </div>
            
            <div class="metric-card card">
                <div class="metric-icon">⚠️</div>
                <div class="metric-content">
                    <div class="metric-label">Overdue</div>
                    <div class="metric-value {{ $dashboardData['overdueTasks'] > 0 ? 'danger' : '' }}">
                        {{ $dashboardData['overdueTasks'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Status Distribution -->
            <div class="chart-section card">
                <div class="section-header">
                    <h3 class="section-title">Status Distribution</h3>
                </div>
                <div class="chart-container" id="statusChart"></div>
            </div>
            
            <!-- Priority Breakdown -->
            <div class="chart-section card">
                <div class="section-header">
                    <h3 class="section-title">Priority Breakdown</h3>
                </div>
                <div class="chart-container" id="priorityChart"></div>
            </div>
        </div>

        <!-- Weekly Completion Trend -->
        <div class="chart-section card">
            <div class="section-header">
                <h3 class="section-title">Weekly Completion Trend</h3>
            </div>
            <div class="chart-container" id="weeklyTrendChart"></div>
        </div>

        <!-- Timeline & Team Activity -->
        <div class="charts-grid">
            <!-- Due Date Timeline -->
            <div class="list-section card">
                <div class="section-header">
                    <h3 class="section-title">Upcoming Deadlines</h3>
                </div>
                <div class="list-content">
                    <div class="list-item">
                        <div class="list-item-icon">
                            <div class="deadline-icon">📅</div>
                        </div>
                        <div class="list-item-content">
                            <div class="list-item-title">Due This Week</div>
                        </div>
                        <div class="list-item-value">{{ $dashboardData['dueThisWeek'] }}</div>
                    </div>
                    <div class="list-item">
                        <div class="list-item-icon">
                            <div class="deadline-icon">📆</div>
                        </div>
                        <div class="list-item-content">
                            <div class="list-item-title">Due Next Week</div>
                        </div>
                        <div class="list-item-value">{{ $dashboardData['dueNextWeek'] }}</div>
                    </div>
                    <div class="list-item">
                        <div class="list-item-icon">
                            <div class="deadline-icon">👤</div>
                        </div>
                        <div class="list-item-content">
                            <div class="list-item-title">Unassigned Tasks</div>
                        </div>
                        <div class="list-item-value {{ $dashboardData['unassignedTasks'] > 0 ? 'danger' : '' }}">
                            {{ $dashboardData['unassignedTasks'] }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Team Activity -->
            <div class="list-section card">
                <div class="section-header">
                    <h3 class="section-title">Top Contributors</h3>
                </div>
                <div class="list-content">
                    @forelse($dashboardData['assigneeStats'] as $stat)
                        <div class="list-item">
                            <div class="list-item-icon">
                                <div class="contributor-avatar">
                                    {{ substr($stat['assignee'], 0, 1) }}
                                </div>
                            </div>
                            <div class="list-item-content">
                                <div class="list-item-title">{{ $stat['assignee'] }}</div>
                                <div class="list-item-meta">{{ $stat['completed'] }} completed / {{ $stat['total'] }} total</div>
                            </div>
                            <div class="list-item-value success">
                                {{ $stat['total'] > 0 ? round(($stat['completed'] / $stat['total']) * 100) : 0 }}%
                            </div>
                        </div>
                    @empty
                        <div class="empty-list">
                            <p>No assigned tasks yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="list-section card">
            <div class="section-header">
                <h3 class="section-title">Recent Activity</h3>
            </div>
            <div class="list-content">
                @forelse($dashboardData['recentTasks'] as $task)
                    <div class="list-item">
                        <div class="list-item-icon">
                            <div class="task-icon">
                                @if($task->status === 'published') ✅
                                @elseif($task->status === 'in_progress') 🔄
                                @elseif($task->status === 'in_review') 👁️
                                @else 📝
                                @endif
                            </div>
                        </div>
                        <div class="list-item-content">
                            <div class="list-item-title">{{ $task->title }}</div>
                            <div class="list-item-meta">
                                <span class="status-badge 
                                    @if($task->status === 'published') success
                                    @elseif($task->status === 'in_progress') info
                                    @elseif($task->status === 'in_review') warning
                                    @else '' @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                                @if($task->priority)
                                    <span class="priority-badge {{ $task->priority }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                @endif
                                @if($task->assignee)
                                    <span class="assignee">· {{ $task->assignee->name }}</span>
                                @endif
                                @if($task->due_date)
                                    <span class="due-date">· {{ $task->due_date->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="list-item-meta time">
                            {{ $task->updated_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="empty-list">
                        <p>No tasks yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>

<!-- ApexCharts Library -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
function dashboardCharts(data, isBusinessBoard) {
    return {
        data: data,
        isBusinessBoard: isBusinessBoard,
        charts: {},
        
        init() {
            setTimeout(() => {
                this.renderCharts();
            }, 100);
            
            // Re-render on Livewire updates
            Livewire.on('budget-updated', () => {
                setTimeout(() => {
                    this.destroyCharts();
                    this.renderCharts();
                }, 200);
            });
            
            Livewire.on('category-updated', () => {
                setTimeout(() => {
                    this.destroyCharts();
                    this.renderCharts();
                }, 200);
            });
        },
        
        destroyCharts() {
            Object.keys(this.charts).forEach(key => {
                if (this.charts[key]) {
                    this.charts[key].destroy();
                }
            });
            this.charts = {};
        },
        
        renderCharts() {
            if (this.isBusinessBoard && !this.data.noBudget) {
                this.renderBusinessCharts();
            } else if (!this.isBusinessBoard) {
                this.renderTaskCharts();
            }
        },
        
        renderBusinessCharts() {
            // Category Pie Chart
            const categoryData = this.data.categoryBreakdown || [];
            if (categoryData.length > 0 && document.querySelector('#categoryPieChart')) {
                this.charts.categoryPie = new ApexCharts(document.querySelector('#categoryPieChart'), {
                    series: categoryData.map(c => c.estimated),
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: categoryData.map(c => c.title),
                    colors: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return val.toFixed(0) + "%"
                        }
                    }
                });
                this.charts.categoryPie.render();
            }
            
            // Budget Comparison Bar Chart
            if (categoryData.length > 0 && document.querySelector('#budgetComparisonChart')) {
                const top5 = categoryData.slice(0, 5);
                this.charts.budgetComparison = new ApexCharts(document.querySelector('#budgetComparisonChart'), {
                    series: [
                        {
                            name: 'Estimated',
                            data: top5.map(c => c.estimated)
                        },
                        {
                            name: 'Spent',
                            data: top5.map(c => c.spent)
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 5
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: top5.map(c => c.title)
                    },
                    colors: ['#3b82f6', '#ef4444'],
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                return '$' + val.toFixed(0)
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                });
                this.charts.budgetComparison.render();
            }
            
            // Monthly Trend Line Chart
            const monthlyData = this.data.monthlyTrend || [];
            if (monthlyData.length > 0 && document.querySelector('#monthlyTrendChart')) {
                this.charts.monthlyTrend = new ApexCharts(document.querySelector('#monthlyTrendChart'), {
                    series: [{
                        name: 'Spent',
                        data: monthlyData.map(m => m.spent)
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        zoom: {
                            enabled: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: monthlyData.map(m => m.month)
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                return '$' + val.toFixed(0)
                            }
                        }
                    },
                    colors: ['#ef4444'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1
                        }
                    }
                });
                this.charts.monthlyTrend.render();
            }
            
            // Status Pie Chart
            const statusData = this.data.statusDistribution || {};
            if (document.querySelector('#statusPieChart')) {
                const statusValues = [
                    statusData.draft || 0,
                    statusData.pending || 0,
                    statusData.approved || 0,
                    statusData.rejected || 0,
                    statusData.completed || 0
                ];
                
                this.charts.statusPie = new ApexCharts(document.querySelector('#statusPieChart'), {
                    series: statusValues,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['Draft', 'Pending', 'Approved', 'Rejected', 'Completed'],
                    colors: ['#6b7280', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6'],
                    legend: {
                        position: 'bottom'
                    }
                });
                this.charts.statusPie.render();
            }
        },
        
        renderTaskCharts() {
            // Status Distribution
            const statusData = this.data.statusDistribution || {};
            if (document.querySelector('#statusChart')) {
                const statusValues = [
                    statusData.to_do || 0,
                    statusData.in_review || 0,
                    statusData.in_progress || 0,
                    statusData.published || 0
                ];
                
                this.charts.status = new ApexCharts(document.querySelector('#statusChart'), {
                    series: statusValues,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['To Do', 'In Review', 'In Progress', 'Published'],
                    colors: ['#6b7280', '#f59e0b', '#3b82f6', '#10b981'],
                    legend: {
                        position: 'bottom'
                    }
                });
                this.charts.status.render();
            }
            
            // Priority Breakdown
            const priorityData = this.data.priorityBreakdown || {};
            if (document.querySelector('#priorityChart')) {
                const priorityValues = [
                    priorityData.high || 0,
                    priorityData.medium || 0,
                    priorityData.low || 0,
                    priorityData.none || 0
                ];
                
                this.charts.priority = new ApexCharts(document.querySelector('#priorityChart'), {
                    series: [{
                        name: 'Tasks',
                        data: priorityValues
                    }],
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 5,
                            horizontal: false,
                            distributed: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: ['High', 'Medium', 'Low', 'None']
                    },
                    colors: ['#ef4444', '#f59e0b', '#3b82f6', '#6b7280'],
                    legend: {
                        show: false
                    }
                });
                this.charts.priority.render();
            }
            
            // Weekly Trend
            const weeklyData = this.data.weeklyTrend || [];
            if (weeklyData.length > 0 && document.querySelector('#weeklyTrendChart')) {
                this.charts.weeklyTrend = new ApexCharts(document.querySelector('#weeklyTrendChart'), {
                    series: [{
                        name: 'Tasks Completed',
                        data: weeklyData.map(w => w.completed)
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        zoom: {
                            enabled: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: weeklyData.map(w => w.week)
                    },
                    colors: ['#10b981'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1
                        }
                    }
                });
                this.charts.weeklyTrend.render();
            }
        }
    }
}
</script>