<div class="container" wire:poll.5s.keep-alive>
    <!-- Individual View (Personal Task List) -->
        <div class="personal-tasks-wrapper">
            <div class="personal-tasks-header">
                <div>
                    <h2 class="personal-tasks-title">My Workspace</h2>
                    <p class="personal-tasks-subtitle">{{ $myTasks->count() }} tasks assigned to you</p>
                </div>
                <div class="tasks-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $myTasks->where('status', 'to_do')->count() }}</div>
                        <div class="stat-label">To Do</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $myTasks->where('status', 'in_progress')->count() }}</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $myTasks->where('status', 'published')->count() }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>

            <div class="personal-tasks-content">
                @php
                    // Group tasks by their TaskGroup (Parent Title)
                    $groupedTasks = $myTasks->groupBy('group_id');
                @endphp

                @forelse($groupedTasks as $groupId => $tasks)
                    @php 
                        $group = $tasks->first()->group;
                        // Determine if this group has any active tasks (not published)
                        $hasActive = $tasks->contains(fn($t) => $t->status !== 'published');
                        $cardClass = $hasActive ? 'group-active' : 'group-completed opacity-75';
                    @endphp

                    <div class="task-group-card {{ $cardClass }}" style="margin-bottom: 1.5rem; background: #161b22; border: 1px solid #30363d; border-radius: 8px; overflow: hidden;">
                        <!-- Group Header (Main Title) -->
                        <div class="group-header" style="padding: 12px 16px; background: #0d1117; border-bottom: 1px solid #30363d; display: flex; justify-content: space-between; align-items: center;">
                            <div class="group-info">
                                <h3 style="margin: 0; font-size: 1rem; color: #f0f6fc; font-weight: 600;">
                                    {{ $group ? $group->title : 'Uncategorized Tasks' }}
                                </h3>
                                @if($group && $group->status)
                                    <span style="font-size: 0.75rem; color: #8b949e; margin-left: 8px;">
                                        {{ ucfirst(str_replace('_', ' ', $group->status)) }}
                                    </span>
                                @endif
                            </div>
                            <!-- Progress: Active / Total -->
                            <div class="group-progress" style="display: flex; align-items: center; gap: 12px; font-size: 0.8rem; color: #8b949e;">
                                @if($group)
                                    <span>{{ $group->published_tasks_count }}/{{ $group->tasks_count }} Done</span>
                                    
                                    @if($group->published_tasks_count === $group->tasks_count && $group->status !== 'published')
                                        <button wire:click="markGroupComplete({{ $group->id }})" 
                                                class="mark-complete-btn"
                                                style="background: rgba(63, 185, 80, 0.1); color: #3fb950; border: 1px solid rgba(63, 185, 80, 0.4); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer; transition: all 0.2s;"
                                                onmouseover="this.style.background='rgba(63, 185, 80, 0.2)'"
                                                onmouseout="this.style.background='rgba(63, 185, 80, 0.1)'">
                                            Mark Done
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Subtasks List (Types) -->
                        <div class="group-items">
                            @foreach($tasks as $task)
                                <div class="task-item-row" wire:key="task-{{ $task->id }}" 
                                     style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #21262d; {{ $loop->last ? 'border-bottom: none;' : '' }} background: {{ $task->status === 'published' ? 'rgba(22, 27, 34, 0.5)' : 'transparent' }};">
                                    
                                    <!-- Checkbox (Radio Style) -->
                                    <div class="task-checkbox" style="margin-right: 12px;">
                                        <input type="checkbox" 
                                               wire:click="toggleComplete({{ $task->id }})"
                                               {{ $task->status === 'published' ? 'checked' : '' }}
                                               style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #30363d; appearance: none; cursor: pointer; position: relative; background: {{ $task->status === 'published' ? '#3fb950' : 'transparent' }}; box-shadow: inset 0 0 0 2px #161b22;">
                                    </div>
                                    
                                    <!-- Task Content -->
                                    <div class="task-content" style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span style="color: {{ $task->status === 'published' ? '#8b949e' : '#c9d1d9' }}; text-decoration: {{ $task->status === 'published' ? 'line-through' : 'none' }}; font-weight: 500;">
                                                {{ $task->title }} <!-- Wait, Title is "Main Task" in user words, but Code says Group Title is Main. Here we show Task "Type/Name" -->
                                            </span>
                                            
                                            <!-- Type Badge -->
                                            @if($task->type)
                                                <span style="padding: 1px 6px; border-radius: 99px; background: rgba(56, 139, 253, 0.1); color: #58a6ff; font-size: 0.7rem;">
                                                    {{ $task->type }}
                                                </span>
                                            @endif
                                            
                                            <!-- Priority Badge -->
                                            @if($task->priority === 'high' && $task->status !== 'published')
                                                <span style="color: #f85149; font-size: 0.75rem; border: 1px solid rgba(248, 81, 73, 0.3); padding: 0 4px; border-radius: 4px;">
                                                    High
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Meta (Due Date, Description) -->
                                        @if($task->due_date || $task->description)
                                            <div style="font-size: 0.75rem; color: #8b949e; display: flex; gap: 12px;">
                                                @if($task->due_date)
                                                    <span style="{{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'published' ? 'color: #f85149;' : '' }}">
                                                        📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                                    </span>
                                                @endif
                                                @if($task->description)
                                                    <span>📝 {{ Str::limit($task->description, 40) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="text-align: center; padding: 3rem; color: #8b949e;">
                        <span style="font-size: 2rem; display: block; margin-bottom: 1rem;">🏝️</span>
                        No tasks assigned. You're all caught up!
                    </div>
                @endforelse

                <style>
                    /* Custom Radio-style Checkbox Checkmark */
                    input[type="checkbox"] {
                        display: grid;
                        place-content: center;
                    }
                    input[type="checkbox"]::before {
                        content: "";
                        width: 10px;
                        height: 10px;
                        transform: scale(0);
                        transition: 120ms transform ease-in-out;
                        box-shadow: inset 1em 1em white;
                        transform-origin: center;
                        clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
                    }
                    input[type="checkbox"]:checked::before {
                        transform: scale(1);
                    }
                    /* Remove the old text-based checkmark */
                    input[type="checkbox"]:checked::after {
                        content: none;
                    }

                    .task-item-row:hover {
                        background: rgba(255,255,255,0.02) !important;
                    }
                </style>
            </div>
        </div>
</div
