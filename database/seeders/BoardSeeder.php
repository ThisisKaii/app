<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\User;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        // Create sample boards
        Board::create([
            'user_id' => $user->id,
            'title' => 'Website Project'
        ]);

        Board::create([
            'user_id' => $user->id,
            'title' => 'Bug Tracker'
        ]);

        Board::create([
            'user_id' => $user->id,
            'title' => 'Marketing Tasks'
        ]);
    }
    }
