<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $board = Board::first();
        $user = User::first();

        // To Do tasks
        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Homepage Redesign',
            'status' => 'to_do',
            'priority' => 'high',
            'type' => 'Design',
            'description' => 'Redesign the homepage with new branding',
            'order' => 1,
        ]);

        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Mobile Navigation',
            'status' => 'to_do',
            'priority' => 'medium',
            'type' => 'Development',
            'description' => 'Fix mobile menu responsiveness',
            'order' => 2,
        ]);

        // In Review tasks
        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Contact Form',
            'status' => 'in_review',
            'priority' => 'medium',
            'type' => 'Development',
            'due_date' => now()->addDays(3),
            'assignee_id' => $user->id,
            'description' => 'Add validation to contact form',
            'order' => 1,
        ]);

        // In Progress tasks
        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Blog Section',
            'status' => 'in_progress',
            'priority' => 'high',
            'type' => 'Development',
            'assignee_id' => $user->id,
            'due_date' => now()->addDays(7),
            'description' => 'Build blog listing and single post pages',
            'order' => 1,
        ]);

        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Newsletter Integration',
            'status' => 'in_progress',
            'priority' => 'low',
            'type' => 'Development',
            'due_date' => now()->addDays(10),
            'description' => 'Connect Mailchimp API',
            'order' => 2,
        ]);

        // Published tasks
        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Landing Page',
            'status' => 'published',
            'priority' => 'high',
            'type' => 'Design',
            'assignee_id' => $user->id,
            'completed_at' => now()->subDays(2),
            'description' => 'Create landing page for product launch',
            'order' => 1,
        ]);

        Task::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'title' => 'Logo Design',
            'status' => 'published',
            'priority' => 'medium',
            'type' => 'Design',
            'completed_at' => now()->subDays(5),
            'description' => 'Design new company logo',
            'order' => 2,
        ]);
    }
}