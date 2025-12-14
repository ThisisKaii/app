<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Board;
use App\Models\Budgets;
use App\Models\BudgetCategory;
use App\Models\Expenses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DashboardTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get the first user in the database (the one you're logged in as)
        $user = User::first();
        
        if (!$user) {
            $this->command->error("No users found in the database. Please register first.");
            return;
        }
        
        $this->command->info("Creating board for user: " . $user->email);

        // 2. Create Business Board
        $board = Board::create([
            'title' => 'Dashboard Test Board',
            'user_id' => $user->id,
            'list_type' => 'Business',
        ]);

        // Add user as board member (required to see board in lists)
        $board->members()->attach($user->id, ['role' => 'owner']);

        // 3. Create Budget
        $budget = Budgets::create([
            'board_id' => $board->id,
            'total_budget' => 50000,
        ]);

        // 4. Create Categories
        $categories = [];
        
        // Completed Category (Expenses count towards "Used")
        $categories['operations'] = BudgetCategory::create([
            'budget_id' => $budget->id,
            'title' => 'Operations',
            'amount_estimated' => 20000,
            'status' => 'completed',
            'order' => 1,
        ]);

        // Approved Category (Counts towards "Allocated" but expenses might not count towards "Used" depending on logic, 
        // strictly speaking expenses usually count if they exist, but our recent logic update to Dashbaord.php line 267 says:
        // $totalSpent = $categories->where('status', 'completed')->...
        // So expenses in 'Approved' won't show in "Total Spent" yet. 
        // To test the "Recent Transactions" list though, we might want expenses everywhere.
        // But for the KPI cards ("Used"), they only sum from Completed categories. 
        // I will add expenses to 'Operations' mostly to test the charts/KPIs.
        
        $categories['marketing'] = BudgetCategory::create([
            'budget_id' => $budget->id,
            'title' => 'Marketing',
            'amount_estimated' => 15000,
            'status' => 'completed', // Make this completed so expenses show up in main stats
            'order' => 2,
        ]);

        $categories['software'] = BudgetCategory::create([
            'budget_id' => $budget->id,
            'title' => 'Software Licenses',
            'amount_estimated' => 5000,
            'status' => 'approved',
            'order' => 3,
        ]);

        $categories['pending_stuff'] = BudgetCategory::create([
            'budget_id' => $budget->id,
            'title' => 'Future Expansion',
            'amount_estimated' => 10000,
            'status' => 'pending',
            'order' => 4,
        ]);

        // 5. Create Expenses with Varied Dates
        $now = Carbon::now();
        
        $expensesData = [
            // Current Week (Today/Yesterday)
            ['cat' => 'operations', 'amt' => 1200, 'desc' => 'Office Rent - Current Mth', 'date' => $now->copy()->subHours(2)],
            ['cat' => 'operations', 'amt' => 150, 'desc' => 'Utilities', 'date' => $now->copy()->subDays(1)],
            ['cat' => 'marketing', 'amt' => 2500, 'desc' => 'Q4 Ad Campaign Start', 'date' => $now->copy()->subDays(2)],
            
            // Last Week (Same Month)
            ['cat' => 'operations', 'amt' => 300, 'desc' => 'Cleaning Services', 'date' => $now->copy()->subDays(8)],
            ['cat' => 'marketing', 'amt' => 800, 'desc' => 'Social Media Ads', 'date' => $now->copy()->subDays(9)],
            ['cat' => 'operations', 'amt' => 50, 'desc' => 'Coffee Supplies', 'date' => $now->copy()->subDays(10)],

            // Last Month
            ['cat' => 'operations', 'amt' => 1200, 'desc' => 'Office Rent - Prev Mth', 'date' => $now->copy()->subMonths(1)->startOfMonth()->addDays(2)],
            ['cat' => 'marketing', 'amt' => 1500, 'desc' => 'Print Materials', 'date' => $now->copy()->subMonths(1)->addDays(10)],
            ['cat' => 'operations', 'amt' => 450, 'desc' => 'Equipment Repair', 'date' => $now->copy()->subMonths(1)->addDays(15)],

            // 2 Months Ago
            ['cat' => 'marketing', 'amt' => 3000, 'desc' => 'Agency Retainer', 'date' => $now->copy()->subMonths(2)->addDays(5)],
            
            // 3 Months Ago (Previous Quarter)
            ['cat' => 'operations', 'amt' => 1200, 'desc' => 'Office Rent - 3 Mths Ago', 'date' => $now->copy()->subMonths(3)->addDays(1)],
            ['cat' => 'marketing', 'amt' => 1000, 'desc' => 'Legacy Campaign', 'date' => $now->copy()->subMonths(3)->addDays(15)],
            
            // Older
            ['cat' => 'operations', 'amt' => 5000, 'desc' => 'Security Deposit', 'date' => $now->copy()->subMonths(5)],
        ];

        foreach ($expensesData as $data) {
            $catKey = $data['cat'];
            if (isset($categories[$catKey])) {
                Expenses::create([
                    'budget_category_id' => $categories[$catKey]->id,
                    'amount' => $data['amt'],
                    'description' => $data['desc'],
                    'created_at' => $data['date'],
                    'updated_at' => $data['date'],
                ]);
            }
        }

        $this->command->info("Dashboard Test Data Seeded!");
        $this->command->info("Board ID: " . $board->id);
    }
}
