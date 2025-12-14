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

        // 1. Group: Homepage Redesign (To Do)
        $group1 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Homepage Redesign',
            'status' => 'to_do',
            'order' => 1
        ]);

        $task1 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group1->id,
            'user_id' => $user->id,
            'title' => 'Design', // Legacy
            'type' => 'Design',
            'priority' => 'high',
            'description' => 'Redesign the homepage with new branding',
            'order' => 1,
            'status' => 'to_do'
        ]);
        $task1->users()->attach($user->id);


        // 2. Group: Mobile Navigation (To Do)
        $group2 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Mobile Navigation',
            'status' => 'to_do',
            'order' => 2
        ]);

        $task2 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group2->id,
            'user_id' => $user->id,
            'title' => 'Development',
            'type' => 'Development',
            'priority' => 'medium',
            'description' => 'Fix mobile menu responsiveness',
            'order' => 1,
            'status' => 'to_do'
        ]);
        $task2->users()->attach($user->id);


        // 3. Group: Contact Form (In Review)
        $group3 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Contact Form',
            'status' => 'in_review',
            'order' => 1
        ]);

        $task3 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group3->id,
            'user_id' => $user->id,
            'title' => 'Development',
            'type' => 'Development',
            'priority' => 'medium',
            'due_date' => now()->addDays(3),
            'description' => 'Add validation to contact form',
            'order' => 1,
            'status' => 'in_review'
        ]);
        $task3->users()->attach($user->id);


        // 4. Group: Blog Section (In Progress)
        $group4 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Blog Section',
            'status' => 'in_progress',
            'order' => 1
        ]);

        $task4 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group4->id,
            'user_id' => $user->id,
            'title' => 'Development',
            'type' => 'Development',
            'priority' => 'high',
            'due_date' => now()->addDays(7),
            'description' => 'Build blog listing and single post pages',
            'order' => 1,
            'status' => 'in_progress'
        ]);
        $task4->users()->attach($user->id);


        // 5. Group: Newsletter Integration (In Progress)
        $group5 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Newsletter Integration',
            'status' => 'in_progress',
            'order' => 2
        ]);

        $task5 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group5->id,
            'user_id' => $user->id,
            'title' => 'Development',
            'type' => 'Development',
            'priority' => 'low',
            'due_date' => now()->addDays(10),
            'description' => 'Connect Mailchimp API',
            'order' => 1,
            'status' => 'in_progress'
        ]);
        $task5->users()->attach($user->id);


        // 6. Group: Landing Page (Published)
        $group6 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Landing Page',
            'status' => 'published',
            'order' => 1
        ]);

        $task6 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group6->id,
            'user_id' => $user->id,
            'title' => 'Design',
            'type' => 'Design',
            'priority' => 'high',
            'completed_at' => now()->subDays(2),
            'description' => 'Create landing page for product launch',
            'order' => 1,
            'status' => 'published'
        ]);
        $task6->users()->attach($user->id);


        // 7. Group: Logo Design (Published)
        $group7 = \App\Models\TaskGroup::create([
            'board_id' => $board->id,
            'title' => 'Logo Design',
            'status' => 'published',
            'order' => 2
        ]);

        $task7 = Task::create([
            'board_id' => $board->id,
            'group_id' => $group7->id,
            'user_id' => $user->id,
            'title' => 'Design',
            'type' => 'Design',
            'priority' => 'medium',
            'completed_at' => now()->subDays(5),
            'description' => 'Design new company logo',
            'order' => 1,
            'status' => 'published'
        ]);
        $task7->users()->attach($user->id);
    }
}