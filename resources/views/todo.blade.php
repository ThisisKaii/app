<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @vite(['resources/css/todo.css', 'resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Todobido</title>
    @livewireStyles
</head>

<body>
    <nav class="navbar">
        <div class="container-fluid">
            <p class="title"><b>Todobido</b></p>
            <div class="ms-auto">
                @guest
                    <a class="button text-decoration-none me-3" href="{{ route('login') }}"
                        style="color: #c9d1d9; padding: 0.375rem 1.25rem;">Log in</a>
                    <a class="button text-decoration-none me-3" href="{{ route('register') }}"
                        style="color: #c9d1d9; padding: 0.375rem 1.25rem; border: 1px solid #3E3E3A; border-radius: 0.25rem;">Register</a>
                @endguest

                @auth
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="button text-decoration-none logouts"
                            style="color: #c9d1d9; padding: 0.375rem 1.25rem; border: 1px solid #3E3E3A; border-radius: 0.25rem; cursor: pointer;">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>
    @if(session('success'))
        <div id="toast-success"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;">
            <span style="font-size: 1.5rem;">✓</span>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0; margin-left: 0.5rem; line-height: 1;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div id="toast-error"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #ef4444; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;">
            <span style="font-size: 1.5rem;">✕</span>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0; margin-left: 0.5rem; line-height: 1;">&times;</button>
        </div>
    @endif
    <main class="container-fluid">
        <div class="main-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="project-title">
                        <div class="project-icon"></div>
                        {{ $board->title }}
                    </div>
                    <div class="view-tabs">
                        <a href="#" class="view-tab" data-view="table">
                            <span>📊</span> Table
                        </a>
                        <a href="#" class="view-tab active" data-view="board">
                            <span>📋</span> Board by Status
                        </a>
                        <a href="#" class="view-tab" data-view="tasks">
                            <span>✓</span> My tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div id="table-view" class="table-view">
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
                    @foreach($tasks as $task)
                        <tr class="data-row">
                            <td> {{$task->title}}</td>
                            <td> {{str_replace('_', ' ', $task->status)}}</td>
                            <td> {{$task->type}}</td>
                            <td> {{$task->priority}}</td>
                            <td> {{$task->assignee ? $task->assignee->name : 'Unassigned'}}</td>
                            <td> {{$task->due_date ? $task->due_date->format('M d, Y') : 'No due date'}}</td>
                            <td> Dev: To be made</td>
                            <td> {{$task->url ? $task->url : 'No URL'}}</td>
                        </tr>
                    @endforeach
                </thead>
                <tbody>
                    <tr class="new-row">
                        <td colspan="8">
                            <span>+ New page</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Board View -->
        <div class="container">
            <div id="board-view" class="kanban-container" style="display: flex;">
                <!-- Todo Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge todo"></span>
                        To do
                    </div>
                    <div class="cards-container" data-status="to_do">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'to_do'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'to_do'])

                    </div>
                </div> <!-- CLOSE TODO COLUMN -->

                <!-- In review Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge review"></span>
                        In review
                    </div>
                    <div class="cards-container" data-status="in_review">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'in_review'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'in_review'])
                    </div>
                </div> <!-- CLOSE IN REVIEW COLUMN -->

                <!-- In progress Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge progress"></span>
                        In progress
                    </div>
                    <div class="cards-container" data-status="in_progress">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'in_progress'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'in_progress'])
                    </div>
                </div> <!-- CLOSE IN PROGRESS COLUMN -->

                <!-- Published Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge published"></span>
                        Published
                    </div>
                    <div class="cards-container" data-status="published">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'published'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'published'])

                    </div>
                </div> <!-- CLOSE PUBLISHED COLUMN -->
            </div> <!-- CLOSE KANBAN CONTAINER -->
        </div> <!-- CLOSE CONTAINER -->
        <!-- My Tasks View -->
        <div id="tasks-view" class="tasks-view">
            <div class="tasks-controls">
                <button class="edit-filters-btn">
                    <span>☰</span> Edit filters
                </button>
                <button class="new-page-btn">
                    <span>+</span> New page
                </button>
            </div>
            <div class="tasks-list">
                <div class="empty-state">
                    <p>No tasks assigned to you yet</p>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        // View switching functionality
        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const viewName = tab.dataset.view;

                document.querySelectorAll('.view-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                document.getElementById('table-view').style.display = 'none';
                document.getElementById('board-view').style.display = 'none';
                document.getElementById('tasks-view').classList.remove('active');

                if (viewName === 'table') {
                    document.getElementById('table-view').style.display = 'block';
                } else if (viewName === 'board') {
                    document.getElementById('board-view').style.display = 'flex';
                } else if (viewName === 'tasks') {
                    document.getElementById('tasks-view').classList.add('active');
                }
            });
        });

        // Drag and Drop functionality
        let draggedCard = null;

        document.addEventListener('DOMContentLoaded', () => {
            initDragAndDrop();
        });

        function initDragAndDrop() {
            const cards = document.querySelectorAll('.task-card');
            const columns = document.querySelectorAll('.kanban-column');

            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            columns.forEach(column => {
                column.addEventListener('dragover', handleDragOver);
                column.addEventListener('drop', handleDrop);
                column.addEventListener('dragleave', handleDragLeave);
            });
        }

        function handleDragStart(e) {
            draggedCard = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDragEnd(e) {
            this.classList.remove('dragging');
            document.querySelectorAll('.kanban-column').forEach(c => {
                c.style.backgroundColor = '';
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            if (e.currentTarget.classList.contains('kanban-column')) {
                e.currentTarget.style.backgroundColor = 'rgba(88, 166, 255, 0.05)';
            }

            return false;
        }

        function handleDragLeave(e) {
            if (e.currentTarget.classList.contains('kanban-column')) {
                e.currentTarget.style.backgroundColor = '';
            }
        }

        function handleDragLeave(e) {
            this.style.backgroundColor = '';
        }


        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!draggedCard) return false;

            const column = e.currentTarget;
            const cardsContainer = column.querySelector('.cards-container');

            if (!cardsContainer) {
                alert('Error: cards container not found.');
                return false;
            }

            const afterElement = getDragAfterElement(cardsContainer, e.clientY);
            const addButton = cardsContainer.querySelector('.add-card');

            // Insert the card at the right position
            if (afterElement == null) {
                // Drop at the end (before add button)
                if (addButton) {
                    cardsContainer.insertBefore(draggedCard, addButton);
                } else {
                    cardsContainer.appendChild(draggedCard);
                }
            } else {
                cardsContainer.insertBefore(draggedCard, afterElement);
            }

            column.style.backgroundColor = '';

            const newStatus = cardsContainer.dataset.status;
            const taskId = draggedCard.dataset.taskId;

            if (!taskId) {
                alert('Error: Task ID not found.');
                return false;
            }

            // Calculate new order based on position
            const allCards = Array.from(cardsContainer.querySelectorAll('.task-card'));
            const newOrder = allCards.indexOf(draggedCard);

            console.log('Task ID:', taskId, 'New Status:', newStatus, 'New Order:', newOrder);

            const updateUrlBase = "{{ url('/todo') }}";
            const url = `${updateUrlBase}/${taskId}`;

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: newStatus,
                    new_order: newOrder
                })
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Success:', data);
                    if (data.success) {
                        // Reload page to show correct order from database
                        location.reload();
                    } else {
                        alert('Update failed!');
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error: ' + error.message);
                    location.reload();
                });

            return false;
        }

        // Helper function to find the card after the mouse position
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;

                if (offset < 0 && offset > closest.offset) {
                    return {
                        offset: offset,
                        element: child
                    };
                } else {
                    return closest;
                }
            }, {
                offset: Number.NEGATIVE_INFINITY
            }).element;
        }

        Livewire.on('task-created', () => {
            location.reload(); // Manual reload
        });
    </script>
</body>

</html>