<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Board;
use App\Models\Task;
use App\Models\TaskGroup;
use Carbon\Carbon;

class TodoTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get the first user in the database
        $user = User::first();
        
        if (!$user) {
            $this->command->error("No users found in the database. Please register first.");
            return;
        }
        
        $this->command->info("Creating todo board for user: " . $user->email);

        // 2. Create Normal Todo Board
        $board = Board::create([
            'title' => 'Todo Test Board',
            'user_id' => $user->id,
            'list_type' => 'Normal',
        ]);

        // Add user as board member
        $board->members()->attach($user->id, ['role' => 'owner']);

        $now = Carbon::now();
        
        // 3. Create TaskGroups (the cards shown in Kanban columns)
        // 4 cards per column = 16 total
        $groupsData = [
            // ===== TO DO Column (4) =====
            ['title' => 'Set up CI/CD pipeline', 'status' => 'to_do', 'order' => 1],
            ['title' => 'Write API documentation', 'status' => 'to_do', 'order' => 2],
            ['title' => 'Fix critical security bug', 'status' => 'to_do', 'order' => 3],
            ['title' => 'Mobile responsive design', 'status' => 'to_do', 'order' => 4],

            // ===== IN REVIEW Column (4) =====
            ['title' => 'Code review for auth module', 'status' => 'in_review', 'order' => 1],
            ['title' => 'UI/UX review for dashboard', 'status' => 'in_review', 'order' => 2],
            ['title' => 'Security audit review', 'status' => 'in_review', 'order' => 3],
            ['title' => 'Performance testing review', 'status' => 'in_review', 'order' => 4],

            // ===== IN PROGRESS Column (4) =====
            ['title' => 'Implement budget module', 'status' => 'in_progress', 'order' => 1],
            ['title' => 'Add expense tracking', 'status' => 'in_progress', 'order' => 2],
            ['title' => 'Create analytics charts', 'status' => 'in_progress', 'order' => 3],
            ['title' => 'Update legacy dependencies', 'status' => 'in_progress', 'order' => 4],

            // ===== PUBLISHED (Completed) Column (4) =====
            ['title' => 'Set up project repository', 'status' => 'published', 'order' => 1],
            ['title' => 'Design database schema', 'status' => 'published', 'order' => 2],
            ['title' => 'Create user authentication', 'status' => 'published', 'order' => 3],
            ['title' => 'Build dashboard layout', 'status' => 'published', 'order' => 4],
        ];

        $createdGroups = [];
        foreach ($groupsData as $data) {
            $group = TaskGroup::create([
                'board_id' => $board->id,
                'title' => $data['title'],
                'status' => $data['status'],
                'order' => $data['order'],
                'created_at' => $now->copy()->subDays(rand(1, 30)),
                'updated_at' => $now->copy()->subDays(rand(0, 5)),
            ]);
            $createdGroups[] = $group;
        }

        // 4. Create some Tasks (sub-items within groups) for variety
        $priorities = ['high', 'medium', 'low', null];
        
        foreach ($createdGroups as $index => $group) {
            // Add 1-3 tasks per group
            $taskCount = rand(1, 3);
            for ($i = 1; $i <= $taskCount; $i++) {
                $dueDate = rand(0, 1) ? $now->copy()->addDays(rand(-5, 14)) : null;
                $completedAt = ($group->status === 'published' && rand(0, 1)) 
                    ? $now->copy()->subDays(rand(1, 10)) 
                    : null;
                
                Task::create([
                    'board_id' => $board->id,
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'title' => "Subtask {$i} for {$group->title}",
                    'type' => 'Task',
                    'status' => $group->status,
                    'priority' => $priorities[array_rand($priorities)],
                    'due_date' => $dueDate,
                    'completed_at' => $completedAt,
                    'order' => $i,
                    'created_at' => $now->copy()->subDays(rand(1, 30)),
                    'updated_at' => $now->copy()->subDays(rand(0, 5)),
                ]);
            }
        }

        $this->command->info("Todo Test Data Seeded!");
        $this->command->info("Board ID: " . $board->id);
        $this->command->info("Total TaskGroups (Cards): " . count($groupsData));
    }
}
