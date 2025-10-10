<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @vite(['resources/css/todo.css'])
    <title>Todobido</title>
</head>

<body>
    <nav class="navbar">
        <div class="container-fluid">
            <p class = "title"><b>Todobido</b></p>
            <div class="ms-auto">
                <a class="button text-decoration-none me-3" href="{{ route('login') }}" style="color: #c9d1d9; padding: 0.375rem 1.25rem;">Log in</a>
                <a class="button text-decoration-none me-3" href="{{ route('register') }}" style="color: #c9d1d9; padding: 0.375rem 1.25rem; border: 1px solid #3E3E3A; border-radius: 0.25rem;">Register</a>
            </div>
        </div>
    </nav>

    <main class="container-fluid">
        <div class="main-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="project-title">
                        <div class="project-icon">🌐</div>
                        Website Drafts Project
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
                <div class="toolbar">
                    <button class="toolbar-btn">☰</button>
                    <button class="toolbar-btn">↕</button>
                    <button class="toolbar-btn">⚡</button>
                    <button class="toolbar-btn">🔍</button>
                    <button class="toolbar-btn">⋯</button>
                    <button class="new-btn" onclick="addNewCard()">New ▼</button>
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
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge todo"></span>
                        To do
                    </div>
                    <div class="cards-container" data-status="todo">
                        <div class="task-card" draggable="true">
                            <div class="task-card-title">Homepage Redesign</div>
                            <div class="task-card-meta">Priority: High</div>
                        </div>
                        <div class="task-card" draggable="true">
                            <div class="task-card-title">Mobile Navigation</div>
                            <div class="task-card-meta">Priority: Medium</div>
                        </div>
                    </div>
                    <button class="add-card" onclick="addCardToColumn('todo')">
                        <span>+</span>
                        New page
                    </button>
                </div>

                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge review"></span>
                        In review
                    </div>
                    <div class="cards-container" data-status="review">
                        <div class="task-card" draggable="true">
                            <div class="task-card-title">Contact Form</div>
                            <div class="task-card-meta">Priority: Medium</div>
                        </div>
                    </div>
                    <button class="add-card review" onclick="addCardToColumn('review')">
                        <span>+</span>
                        New page
                    </button>
                </div>

                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge progress"></span>
                        In progress
                    </div>
                    <div class="cards-container" data-status="progress">
                        <div class="task-card" draggable="true">
                            <div class="task-card-title">Blog Section</div>
                            <div class="task-card-meta">Priority: High</div>
                        </div>
                    </div>
                    <button class="add-card inprogress" onclick="addCardToColumn('progress')">
                        <span>+</span>
                        New page
                    </button>
                </div>

                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge published"></span>
                        Published
                    </div>
                    <div class="cards-container" data-status="published">
                        <div class="task-card" draggable="true">
                            <div class="task-card-title">Landing Page</div>
                            <div class="task-card-meta">Completed</div>
                        </div>
                    </div>
                    <button class="add-card published" onclick="addCardToColumn('published')">
                        <span>+</span>
                        New page
                    </button>
                </div>
            </div>
        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
            const containers = document.querySelectorAll('.cards-container');
            const columns = document.querySelectorAll('.kanban-column');

            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            containers.forEach(container => {
                container.addEventListener('dragover', handleDragOver);
                container.addEventListener('drop', handleDrop);
                container.addEventListener('dragleave', handleDragLeave);
            });

            columns.forEach(column => {
                column.addEventListener('dragover', handleDragOver);
                column.addEventListener('drop', handleDropColumn);
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
            document.querySelectorAll('.cards-container').forEach(c => {
                c.style.backgroundColor = '';
            });
            document.querySelectorAll('.kanban-column').forEach(c => {
                c.style.backgroundColor = '';
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            // Highlight both column and cards-container
            if (e.currentTarget.classList.contains('kanban-column')) {
                e.currentTarget.style.backgroundColor = 'rgba(88, 166, 255, 0.05)';
            } else if (e.currentTarget.classList.contains('cards-container')) {
                e.currentTarget.style.backgroundColor = 'rgba(88, 166, 255, 0.1)';
            }

            return false;
        }

        function handleDragLeave(e) {
            // Clear background for both column and container
            if (e.currentTarget.classList.contains('kanban-column') ||
                e.currentTarget.classList.contains('cards-container')) {
                e.currentTarget.style.backgroundColor = '';
            }
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();

            if (draggedCard) {
                this.appendChild(draggedCard);
                this.style.backgroundColor = '';
            }

            return false;
        }

        function handleDropColumn(e) {
            e.preventDefault();
            e.stopPropagation();

            if (draggedCard && e.currentTarget.classList.contains('kanban-column')) {
                // Find the cards-container inside this column
                const cardsContainer = e.currentTarget.querySelector('.cards-container');

                if (cardsContainer) {
                    const newStatus = cardsContainer.dataset.status;
                    const taskId = draggedCard.dataset.taskId;

                    // Move card visually
                    cardsContainer.appendChild(draggedCard);
                    e.currentTarget.style.backgroundColor = '';
                }
            }

            return false;
        }

        function addCardToColumn(status) {
            const title = prompt('Enter card title:');
            if (title) {
                const container = document.querySelector(`.cards-container[data-status="${status}"]`);
                const card = document.createElement('div');
                card.className = 'task-card';
                card.draggable = true;
                card.innerHTML = `
                    <div class="task-card-title">${title}</div>
                    <div class="task-card-meta">Priority: Medium</div>
                `;

                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);

                container.appendChild(card);
            }
        }

        function addNewCard() {
            const title = prompt('Enter card title:');
            if (title) {
                const container = document.querySelector('.cards-container[data-status="todo"]');
                const card = document.createElement('div');
                card.className = 'task-card';
                card.draggable = true;
                card.innerHTML = `
                    <div class="task-card-title">${title}</div>
                    <div class="task-card-meta">Priority: Medium</div>
                `;

                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);

                container.appendChild(card);
            }
        }
    </script>
</body>

</html>