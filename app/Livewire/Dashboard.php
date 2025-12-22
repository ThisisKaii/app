<?php

namespace App\Livewire;

use App\Models\Board;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Gate;

/**
 * Analytics dashboard component displaying statistics for both Normal and Business boards.
 * For Normal boards: task metrics, completion rates, team activity, and trends.
 * For Business boards: budget metrics, spending analysis, and expense tracking.
 */
class Dashboard extends Component
{
    public $board;
    public $isBusinessBoard = false;

    public $dateRange = 'all';
    public $startDate;
    public $endDate;

    public $dashboardData = [];

    public $showAllTransactions = false;

    public function mount(Board $board)
    {
        Gate::authorize('viewTasks', $board);

        $this->board = $board;
        $this->isBusinessBoard = $board->list_type === 'Business';

        $this->setDateRange('all');
        $this->loadDashboardData();
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;

        switch ($range) {
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek();
                $this->endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth();
                $this->endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $this->startDate = Carbon::now()->startOfQuarter();
                $this->endDate = Carbon::now()->endOfQuarter();
                break;
            default:
                $this->startDate = null;
                $this->endDate = null;
        }

        $this->loadDashboardData();
    }

    #[On('budget-updated')]
    #[On('category-updated')]
    public function loadDashboardData()
    {
        if ($this->isBusinessBoard) {
            $this->loadBusinessDashboard();
        } else {
            $this->loadNormalDashboard();
        }

        $this->dispatch('dashboard-updated', data: $this->dashboardData);
    }

    protected function loadNormalDashboard()
    {
        $tasks = $this->board->tasks()->with(['assignee', 'users', 'tags'])->get();

        // Apply date filter if set
        if ($this->startDate && $this->endDate) {
            $tasks = $tasks->filter(function ($task) {
                return $task->created_at->between($this->startDate, $this->endDate);
            });
        }

        // Basic metrics
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'published')->count();
        // Combine To Do and In Progress into "Active" tasks
        $activeTasks = $tasks->whereIn('status', ['to_do', 'in_progress'])->count();
        $overdueTasks = $tasks->filter(function ($task) {
            return $task->due_date && $task->due_date->isPast() && $task->status !== 'published';
        })->count();

        // Completion rate
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Status distribution
        $statusDistribution = [
            'to_do' => $tasks->where('status', 'to_do')->count(),
            'in_review' => $tasks->where('status', 'in_review')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'published' => $tasks->where('status', 'published')->count(),
        ];

        // Priority breakdown
        $priorityBreakdown = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
            'none' => $tasks->whereNull('priority')->count(),
        ];

        // Tasks by due date
        $today = Carbon::today();
        $dueThisWeek = $tasks->filter(function ($task) use ($today) {
            return $task->due_date && $task->due_date->between($today, $today->copy()->addWeek()) && $task->status !== 'published';
        })->count();

        $dueNextWeek = $tasks->filter(function ($task) use ($today) {
            return $task->due_date && $task->due_date->between(
                $today->copy()->addWeek(),
                $today->copy()->addWeeks(2)
            ) && $task->status !== 'published';
        })->count();

        // Team activity - Using new multi-assignee relationship
        $userTaskCounts = [];
        foreach ($tasks as $task) {
            // Use users() relationship (multi-assignee) if available, fallback to assignee_id
            $taskUsers = $task->users;
            
            if ($taskUsers && $taskUsers->count() > 0) {
                foreach ($taskUsers as $user) {
                    $userId = $user->id;
                    if (!isset($userTaskCounts[$userId])) {
                        $userTaskCounts[$userId] = [
                            'assignee' => $user->name,
                            'is_active' => $user->is_active ?? false,
                            'total' => 0,
                            'completed' => 0,
                        ];
                    }
                    $userTaskCounts[$userId]['total']++;
                    if ($task->status === 'published') {
                        $userTaskCounts[$userId]['completed']++;
                    }
                }
            } elseif ($task->assignee_id && $task->assignee) {
                // Fallback to old single assignee
                $userId = $task->assignee_id;
                if (!isset($userTaskCounts[$userId])) {
                    $userTaskCounts[$userId] = [
                        'assignee' => $task->assignee->name,
                        'is_active' => $task->assignee->is_active ?? false,
                        'total' => 0,
                        'completed' => 0,
                    ];
                }
                $userTaskCounts[$userId]['total']++;
                if ($task->status === 'published') {
                    $userTaskCounts[$userId]['completed']++;
                }
            }
        }
        
        $assigneeStats = collect($userTaskCounts)
            ->map(function ($stat) {
                $stat['percentage'] = $stat['total'] > 0 ? round(($stat['completed'] / $stat['total']) * 100) : 0;
                return $stat;
            })
            ->sortByDesc('completed')
            ->take(5)
            ->values();

        $unassignedTasks = $tasks->whereNull('assignee_id')->count();

        // Weekly completion trend (last 8 weeks)
        $weeklyTrend = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();

            $completed = $tasks->filter(function ($task) use ($weekStart, $weekEnd) {
                return $task->completed_at &&
                    $task->completed_at->between($weekStart, $weekEnd);
            })->count();

            $weeklyTrend[] = [
                'week' => $weekStart->format('M d'),
                'completed' => $completed,
            ];
        }

        // Recommended Task
        $recommendedTasks = $tasks->filter(function ($task) {
            return $task->status !== 'published';
        })->sort(function ($a, $b) {
            // Sort by Priority (High > Medium > Low > None)
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1, null => 0];
            $priorityA = $priorityOrder[$a->priority] ?? 0;
            $priorityB = $priorityOrder[$b->priority] ?? 0;

            if ($priorityA !== $priorityB) {
                return $priorityB <=> $priorityA; // Descending priority
            }

            // Then by Due Date (Sooner is better)
            if ($a->due_date && $b->due_date) {
                return $a->due_date <=> $b->due_date;
            }
            if ($a->due_date) return -1;
            if ($b->due_date) return 1;

            return 0;
        })->take(5);

        // Recent tasks (last 10 updated)
        $recentTasks = $tasks->sortByDesc('updated_at')->take(10)->values();

        $this->dashboardData = [
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'activeTasks' => $activeTasks,
            'overdueTasks' => $overdueTasks,
            'completionRate' => $completionRate,
            'dueThisWeek' => $dueThisWeek,
            'dueNextWeek' => $dueNextWeek,
            'unassignedTasks' => $unassignedTasks,
            'weeklyTrend' => $weeklyTrend,
            'recentTasks' => $recentTasks,
            'recommendedTasks' => $recommendedTasks,
            'statusDistribution' => $statusDistribution,
            'priorityBreakdown' => $priorityBreakdown,
            'assigneeStats' => $assigneeStats,
        ];
    }

    protected function loadBusinessDashboard()
    {
        $budget = $this->board->budgets;

        if (!$budget) {
            $this->dashboardData = [
                'noBudget' => true,
                'message' => 'No budget has been created for this board yet.',
            ];
            return;
        }

        $categories = $budget->budgetCategories()->with('expenses')->get();

        // Apply date filter to expenses if set
        if ($this->startDate && $this->endDate) {
            $categories = $categories->map(function ($category) {
                $category->setRelation('expenses', $category->expenses->filter(function ($expense) {
                    return $expense->created_at->between($this->startDate, $this->endDate);
                }));
                return $category;
            });
        }

        // Basic financial metrics
        // Basic financial metrics
        $totalBudget = $budget->total_budget;
        
        $totalAllocated = $categories->whereIn('status', ['pending', 'approved', 'completed'])
            ->sum('amount_estimated');
            
        $totalSpent = $categories->where('status', 'completed')
            ->sum(function ($category) {
                return $category->expenses->sum('amount');
            });
            
        $remaining = $totalBudget - $totalSpent;

        // Percentages
        $allocatedPercent = $totalBudget > 0 ? round(($totalAllocated / $totalBudget) * 100, 1) : 0;
        $spentPercent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        // Budget health
        $budgetHealth = 'on_track';
        if ($totalSpent > $totalBudget) {
            $budgetHealth = 'over_budget';
        } elseif ($spentPercent > 80) {
            $budgetHealth = 'warning';
        }

        // Categories at risk (over 80% spent)
        $categoriesAtRisk = $categories->filter(function ($category) {
            $spent = $category->expenses->sum('amount');
            $progress = $category->amount_estimated > 0 ? ($spent / $category->amount_estimated) * 100 : 0;
            return $progress > 80 && $progress <= 100;
        })->count();

        // Categories over budget
        $categoriesOverBudget = $categories->filter(function ($category) {
            return $category->expenses->sum('amount') > $category->amount_estimated;
        })->count();

        // Status distribution
        $statusDistribution = [
            'draft' => $categories->where('status', 'draft')->count(),
            'pending' => $categories->where('status', 'pending')->count(),
            'approved' => $categories->where('status', 'approved')->count(),
            'rejected' => $categories->where('status', 'rejected')->count(),
            'completed' => $categories->where('status', 'completed')->count(),
        ];

        // Category breakdown for charts
        $categoryBreakdown = $categories->map(function ($category) {
            return [
                'title' => $category->title,
                'estimated' => (float) $category->amount_estimated,
                'spent' => (float) $category->expenses->sum('amount'),
                'progress' => $category->amount_estimated > 0
                    ? ($category->expenses->sum('amount') / $category->amount_estimated) * 100
                    : 0,
            ];
        })->sortByDesc('spent')->take(10)->values();

        // Top spending categories
        $topSpending = $categories->sortByDesc(function ($category) {
            return $category->expenses->sum('amount');
        })->take(5)->values();

        // Monthly spending trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $monthSpent = 0;
            foreach ($categories as $category) {
                $monthSpent += $category->expenses
                    ->filter(function ($expense) use ($monthStart, $monthEnd) {
                        return $expense->created_at->between($monthStart, $monthEnd);
                    })
                    ->sum('amount');
            }

            $monthlyTrend[] = [
                'month' => $monthStart->format('M'),
                'spent' => (float) $monthSpent,
            ];
        }

        // Calculate Spending Trend (Last Month vs Previous)
        $currentMonthSpent = $monthlyTrend[5]['spent']; // Index 5 is current month
        $lastMonthSpent = $monthlyTrend[4]['spent']; // Index 4 is previous month
        
        $spendingTrend = 0;
        if ($lastMonthSpent > 0) {
            $spendingTrend = round((($currentMonthSpent - $lastMonthSpent) / $lastMonthSpent) * 100, 1);
        } elseif ($currentMonthSpent > 0) {
            $spendingTrend = 100; // 0 to something is 100% increase
        }

        // Recent expenses (last 10)
        $allExpenses = collect();
        foreach ($categories as $category) {
            foreach ($category->expenses as $expense) {
                $allExpenses->push([
                    'id' => $expense->id,
                    'category' => $category->title,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'created_at' => $expense->created_at,
                ]);
            }
        }
        $recentExpenses = $allExpenses->sortByDesc('created_at')->take(10)->values();

        // Largest expenses
        $largestExpenses = $allExpenses->sortByDesc('amount')->take(5)->values();

        // Total expense count
        $totalExpenses = $allExpenses->count();

        // Burn rate (spending per day)
        $oldestExpense = $allExpenses->min('created_at');
        $daysSinceStart = $oldestExpense ? Carbon::parse($oldestExpense)->diffInDays(Carbon::now()) : 1;
        $daysSinceStart = max($daysSinceStart, 1);
        $burnRate = round($totalSpent / $daysSinceStart, 2);

        $this->dashboardData = [
            'noBudget' => false,
            'totalBudget' => $totalBudget,
            'totalAllocated' => $totalAllocated,
            'totalSpent' => $totalSpent,
            'remaining' => $remaining,
            'allocatedPercent' => $allocatedPercent,
            'spentPercent' => $spentPercent,
            'budgetHealth' => $budgetHealth,
            'categoriesAtRisk' => $categoriesAtRisk,
            'categoriesOverBudget' => $categoriesOverBudget,
            'statusDistribution' => $statusDistribution,
            'categoryBreakdown' => $categoryBreakdown,
            'topSpending' => $topSpending,
            'monthlyTrend' => $monthlyTrend,
            'recentExpenses' => $recentExpenses,
            'largestExpenses' => $largestExpenses,
            'totalExpenses' => $totalExpenses,
            'burnRate' => $burnRate,
            'spendingTrend' => $spendingTrend,
        ];
    }

    public function toggleAllTransactions()
    {
        $this->showAllTransactions = !$this->showAllTransactions;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}