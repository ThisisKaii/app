<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\budget_task;
use App\Models\Budgets;

class budgetTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budgets = Budgets::first();

        budget_task::create([
            'budget_id' => $budgets->id,
            'title' => 'Design Phase',
            'status' => 'draft',
        ]);

    }
}
