<?php

namespace Database\Seeders;

use App\Models\BudgetCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Budgets;

class budgetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budgets = Budgets::first();

        BudgetCategory::create([
            'budget_id' => $budgets->id,
            'title' => 'Design Phase',
            'status' => 'draft',
        ]);

    }
}
