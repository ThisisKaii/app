<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        // Create tags
        $urgent = Tag::create([
            'name' => 'Urgent',
            'color' => '#EF4444', // Red
        ]);

        $bug = Tag::create([
            'name' => 'Bug',
            'color' => '#F59E0B', // Orange
        ]);

        $feature = Tag::create([
            'name' => 'Feature',
            'color' => '#10B981', // Green
        ]);

        $documentation = Tag::create([
            'name' => 'Documentation',
            'color' => '#3B82F6', // Blue
        ]);

        $tasks = Task::all();
        
        if ($tasks->count() > 0) {
            $tasks[0]->tags()->attach([$urgent->id, $feature->id]);
            $tasks[1]->tags()->attach([$bug->id]);
            $tasks[2]->tags()->attach([$feature->id]);
            $tasks[3]->tags()->attach([$urgent->id, $bug->id]);
        }
    }
}