<div id="table-view" class="table-view">
    <h3>Table View - Tasks: {{ count($tasks) }}</h3>
    <div style="background: yellow; padding: 20px; border: 5px solid red;">
        <h1 style="color: black; font-size: 32px;">TABLE COMPONENT RENDERED</h1>
        <p style="color: black; font-size: 24px;">Tasks count: {{ is_array($tasks) ? count($tasks) : $tasks->count() }}
        </p>

        @if($tasks && count($tasks) > 0)
            <p style="color: green; font-size: 20px;">Tasks exist!</p>

            <div style="background: white; padding: 10px; margin: 10px 0;">
                @foreach($tasks as $index => $task)
                    <div style="border: 1px solid black; padding: 10px; margin: 5px 0;">
                        <strong>Task {{ $index + 1 }}:</strong> {{ $task->title ?? 'No title' }}
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: red; font-size: 20px;">No tasks found!</p>
        @endif
    </div>
    @if(count($tasks) > 0)
        <table class="data-table">
                <thead>
                    <tr>
                        <th>📝 Draft Title</th>
                        <th>✨ Status</th>
                        <th>🎯 Type</th>
                        <th>🎨 Priority</th>
                        <th>👤 Assignee</th>
                        <th>📅 Due date</th>
                        <th>🏷️ Tags</th>
                        <th>🔗 URL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                                    <tr class=" data-row">
                              <td>{{ $task->title }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($task->status)) }}</td>
                        <td>{{ $task->type ?? 'N/A' }}</td>
                        <td>{{ $task->priority ?? 'N/A' }}</td>
                               <td>{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</td>
                        <td>{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</td>
                        <td>Dev: To be made</td>
                        <td>{{ $task->url ? $task->url : 'No URL' }}</td>
                        </tr>
                    @endforeach
            <tr class="new-row">
                <td colspan="8">
                    <span>+ New page</span>
                </td>
            </tr>
            </tbody>
        </table>
    @else
        <div style="padding: 2rem; text-align: center;">
            <p>No tasks found for this board.</p>
        </div>
    @endif
</div>