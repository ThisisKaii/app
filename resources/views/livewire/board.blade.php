<!-- Board View -->
<div class="container">
    <div id="board-view" class="kanban-container">
        <!-- Todo Column -->
        <div class="kanban-column" data-status="to_do">
            <div class="column-header">
                <span class="status-badge todo"></span>
                To do
                <span class="section-count">{{ $task->where('status', 'to_do')->count() }}</span>
            </div>
            <div class="cards-container" data-status="to_do">
                @livewire('edit-modal', ['board' => $board, 'status' => 'to_do'], key('edit-modal-to_do'))
            </div>
        </div>

        <!-- In review Column -->
        <div class="kanban-column" data-status="in_review">
            <div class="column-header">
                <span class="status-badge review"></span>
                In review
                <span class="section-count">{{ $task->where('status', 'in_review')->count() }}</span>
            </div>
            <div class="cards-container" data-status="in_review">
                @livewire('edit-modal', ['board' => $board, 'status' => 'in_review'], key('edit-modal-in_review'))
            </div>
        </div>

        <!-- In progress Column -->
        <div class="kanban-column" data-status="in_progress">
            <div class="column-header">
                <span class="status-badge progress"></span>
                In progress
                <span class="section-count">{{ $task->where('status', 'in_progress')->count() }}</span>
            </div>
            <div class="cards-container" data-status="in_progress">
                @livewire('edit-modal', ['board' => $board, 'status' => 'in_progress'], key('edit-modal-in_progress'))
            </div>
        </div>

        <!-- Published Column -->
        <div class="kanban-column" data-status="published">
            <div class="column-header">
                <span class="status-badge published"></span>
                Published
                <span class="section-count">{{ $task->where('status', 'published')->count() }}</span>
            </div>
            <div class="cards-container" data-status="published">
                @livewire('edit-modal', ['board' => $board, 'status' => 'published'], key('edit-modal-published'))
            </div>
        </div>
    </div>
</div>

<script>
// Store global state
window.dragState = {
    isDragging: false,
    draggedElement: null,
    draggedTaskId: null,
    sourceStatus: null
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeDragAndDrop();
});

// Reinitialize after Livewire updates
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', () => {
        setTimeout(() => {
            console.log('Reinitializing after Livewire morph');
            initializeDragAndDrop();
        }, 50);
    });
});

function initializeDragAndDrop() {
    const boardView = document.getElementById('board-view');
    if (!boardView) {
        console.log('Board view not found');
        return;
    }

    // Use event delegation on the board container
    boardView.removeEventListener('dragstart', handleDragStart);
    boardView.removeEventListener('dragend', handleDragEnd);
    boardView.addEventListener('dragstart', handleDragStart);
    boardView.addEventListener('dragend', handleDragEnd);

    // Set up columns
    const columns = boardView.querySelectorAll('.kanban-column');
    console.log(`Found ${columns.length} columns`);
    
    columns.forEach(column => {
        column.removeEventListener('dragover', handleDragOver);
        column.removeEventListener('drop', handleDrop);
        column.removeEventListener('dragleave', handleDragLeave);
        
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragleave', handleDragLeave);
    });

    // Make all current cards draggable
    const cards = boardView.querySelectorAll('.task-card');
    console.log(`Found ${cards.length} cards`);
    cards.forEach(card => {
        card.setAttribute('draggable', 'true');
    });
}

function handleDragStart(e) {
    // Check if the dragged element is a task card
    const card = e.target.closest('.task-card');
    if (!card) return;
    
    e.stopPropagation();
    
    window.dragState.isDragging = true;
    window.dragState.draggedElement = card;
    window.dragState.draggedTaskId = card.dataset.taskId;
    
    // Find the source column
    const sourceContainer = card.closest('.cards-container');
    window.dragState.sourceStatus = sourceContainer ? sourceContainer.dataset.status : null;
    
    card.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', window.dragState.draggedTaskId);
    
    console.log('Drag started:', {
        taskId: window.dragState.draggedTaskId,
        sourceStatus: window.dragState.sourceStatus
    });
}

function handleDragEnd(e) {
    const card = e.target.closest('.task-card');
    if (!card) return;
    
    card.classList.remove('dragging');
    
    // Reset column backgrounds
    document.querySelectorAll('.kanban-column').forEach(col => {
        col.classList.remove('drag-over');
        col.style.backgroundColor = '';
    });
    
    console.log('Drag ended');
    
    // Reset state after a short delay
    setTimeout(() => {
        window.dragState.isDragging = false;
        window.dragState.draggedElement = null;
        window.dragState.draggedTaskId = null;
        window.dragState.sourceStatus = null;
    }, 100);
}

function handleDragOver(e) {
    if (!window.dragState.isDragging) return;
    
    e.preventDefault();
    e.stopPropagation();
    e.dataTransfer.dropEffect = 'move';
    
    const column = e.currentTarget;
    column.style.backgroundColor = 'rgba(88, 166, 255, 0.1)';
}

function handleDragLeave(e) {
    // Only clear if we're actually leaving the column
    if (e.currentTarget.contains(e.relatedTarget)) return;
    e.currentTarget.style.backgroundColor = '';
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const column = e.currentTarget;
    column.style.backgroundColor = '';
    
    if (!window.dragState.draggedTaskId) {
        console.log('No task being dragged');
        return;
    }
    
    const cardsContainer = column.querySelector('.cards-container');
    if (!cardsContainer) {
        console.log('No cards container found');
        return;
    }
    
    const newStatus = cardsContainer.dataset.status;
    const taskId = window.dragState.draggedTaskId;
    
    console.log(`Dropping task ${taskId} into ${newStatus}`);
    
    // Calculate new position
    const afterElement = getDragAfterElement(cardsContainer, e.clientY);
    const allCards = Array.from(cardsContainer.querySelectorAll('.task-card:not(.dragging)'));
    let newOrder = 0;
    
    if (afterElement) {
        const afterIndex = allCards.indexOf(afterElement);
        newOrder = afterIndex;
    } else {
        newOrder = allCards.length;
    }
    
    console.log(`Moving to position ${newOrder}`);
    
    // Send update to server
    updateTaskStatus(taskId, newStatus, newOrder);
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

async function updateTaskStatus(taskId, newStatus, newOrder) {
    try {
        const url = `/todobido/${taskId}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        
        console.log('Sending update:', { taskId, newStatus, newOrder });
        
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus,
                new_order: newOrder
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Task updated successfully');
            // Refresh the board to show updated state
            window.location.reload();
        } else {
            console.error('Update failed:', data.message);
            window.location.reload();
        }
    } catch (error) {
        console.error('Error updating task:', error);
        alert('Failed to update task. Please try again.');
        window.location.reload();
    }
}

// Handle card clicks separately from drag events
window.handleCardClick = function(event, taskId, status) {
    // Only trigger if we're not dragging
    if (!window.dragState.isDragging) {
        Livewire.dispatch('open-task-modal', { 
            taskId: taskId,
            status: status 
        });
    }
}
</script>