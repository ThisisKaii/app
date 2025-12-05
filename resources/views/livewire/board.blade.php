<!-- Board View -->
<div class="container">
    <div id="board-view" class="kanban-container">
        <!-- Todo Column -->
        <div class="kanban-column">
            <div class="column-header">
                <span class="status-badge todo"></span>
                To do
                <span class="section-count">{{ $task->where('status', 'to_do')->count() }}</span>
            </div>
            <div class="cards-container" data-status="to_do">
                @livewire('edit-modal', ['board' => $board, 'status' => 'to_do'])

            </div>
        </div> <!-- CLOSE TODO COLUMN -->

        <!-- In review Column -->
        <div class="kanban-column">
            <div class="column-header">
                <span class="status-badge review"></span>
                In review
                <span class="section-count">{{ $task->where('status', 'in_review')->count() }}</span>
            </div>
            <div class="cards-container" data-status="in_review">
                @livewire('edit-modal', ['board' => $board, 'status' => 'in_review'])
            </div>
        </div> <!-- CLOSE IN REVIEW COLUMN -->

        <!-- In progress Column -->
        <div class="kanban-column">
            <div class="column-header">
                <span class="status-badge progress"></span>
                In progress
                <span class="section-count">{{ $task->where('status', 'in_progress')->count() }}</span>
            </div>
            <div class="cards-container" data-status="in_progress">
                @livewire('edit-modal', ['board' => $board, 'status' => 'in_progress'])
            </div>
        </div> <!-- CLOSE IN PROGRESS COLUMN -->

        <!-- Published Column -->
        <div class="kanban-column">
            <div class="column-header">
                <span class="status-badge published"></span>
                Published
                <span class="section-count">{{ $task->where('status', 'published')->count() }}</span>
            </div>
            <div class="cards-container" data-status="published">
                @livewire('edit-modal', ['board' => $board, 'status' => 'published'])

            </div>
        </div> <!-- CLOSE PUBLISHED COLUMN -->
    </div> <!-- CLOSE KANBAN CONTAINER -->
</div> <!-- CLOSE CONTAINER -->

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.view-tab');
        const views = {
            table: document.getElementById('table-view'),
            board: document.getElementById('board-view'),
            tasks: document.getElementById('tasks-view')
        };

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                Object.values(views).forEach(v => v.style.display = 'none');
                const viewToShow = views[tab.dataset.view];
                if (viewToShow) {
                    viewToShow.style.display = tab.dataset.view === 'board' ? 'flex' : 'block';
                }
            });
        });
    });

    let draggedTaskId = null;
    let draggedElement = null;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        console.log("Initial Drag & Drop Setup");
        initDragAndDrop();
    });

    // Wait for Livewire to be available before setting up hooks
    document.addEventListener('livewire:initialized', () => {
        console.log("Livewire Initialized - Setting up Drag & Drop");
        initDragAndDrop();

        // Set up the morph hook
        Livewire.hook('morph.updated', ({ el, component }) => {
            console.log("Livewire Component Updated - Reinitializing Drag & Drop");
            initDragAndDrop();
        });
    });

    function initDragAndDrop() {
        const cards = document.querySelectorAll('.task-card');
        const columns = document.querySelectorAll('.kanban-column');

        console.log(`Found ${cards.length} cards and ${columns.length} columns`);

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
        draggedTaskId = this.dataset.taskId;
        draggedElement = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('taskId', draggedTaskId);
    }

    function handleDragEnd(e) {
        if (draggedElement) {
            draggedElement.classList.remove('dragging');
        }
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
    }

    function handleDragLeave(e) {
        this.style.backgroundColor = '';
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!draggedTaskId) {
            console.error('No task ID found');
            return false;
        }

        const column = e.currentTarget;
        const cardsContainer = column.querySelector('.cards-container');

        if (!cardsContainer) {
            console.error('Error: cards container not found.');
            return false;
        }

        column.style.backgroundColor = '';

        const newStatus = cardsContainer.dataset.status;
        const taskId = draggedTaskId;

        if (!taskId) {
            console.error('Error: Task ID not found.');
            return false;
        }

        const afterElement = getDragAfterElement(cardsContainer, e.clientY);
        const allCards = Array.from(cardsContainer.querySelectorAll('.task-card'));
        let newOrder = 0;

        if (afterElement) {
            newOrder = allCards.indexOf(afterElement);
        } else {
            newOrder = allCards.length;
        }

        const updateUrlBase = "{{ url('/todobido') }}";
        const url = `${updateUrlBase}/${taskId}`;

        console.log('Updating task:', taskId, 'to status:', newStatus, 'order:', newOrder);

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
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Task updated successfully - reloading page');
                    location.reload();
                } else {
                    console.error('Update failed!');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                location.reload();
            });

        draggedTaskId = null;
        draggedElement = null;

        return false;
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
</script>