<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\Budgets;

class BudgetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $board = Board::first();
        
        Budgets::create([
            'board_id' => $board->id,
            'total_budget' => 10000,
        ]);
    }
}
