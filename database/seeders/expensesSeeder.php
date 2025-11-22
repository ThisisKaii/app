<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\budget_task;
use App\Models\expenses;

class expensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budget_tasl = budget_task::first();

        expenses::create([
            'budget_task_id' => $budget_tasl->id,
            'amount' => 500,
            'description' => 'Initial expense',
        ]);

        expenses::create([
            'budget_task_id' => $budget_tasl->id,
            'amount' => 300,
            'description' => 'Secondary expense',
        ]);

    }
}
