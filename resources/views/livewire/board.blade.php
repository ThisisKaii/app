<div>
    <!-- Board View -->
    <div class="container">
        <div id="board-view" class="kanban-container">
            @foreach(['to_do' => 'To do', 'in_review' => 'In review', 'in_progress' => 'In progress', 'published' => 'Published'] as $statusKey => $statusLabel)
            @php
                $statusClass = match($statusKey) {
                    'to_do' => 'todo',
                    'in_review' => 'review',
                    'in_progress' => 'progress',
                    'published' => 'published',
                    default => 'todo'
                };
            @endphp
                <div class="kanban-column" data-status="{{ $statusKey }}">
                    <div class="column-header">
                        <span class="status-badge {{ $statusClass }}"></span>
                        {{ $statusLabel }}
                        <span class="section-count">{{ $groups->where('status', $statusKey)->count() }}</span>
                    </div>
                    
                    <div class="cards-container" data-status="{{ $statusKey }}">
                        @foreach($groups->where('status', $statusKey) as $group)
                            <!-- Group Card (Draggable) -->
                            <div wire:key="group-{{ $group->id }}" 
                                 draggable="true" 
                                 class="task-card group-card" 
                                 data-group-id="{{ $group->id }}"
                                 style="cursor: grab; display: flex; flex-direction: column; gap: 10px; background-color: #161b22; border: 1px solid #30363d; padding: 12px; border-radius: 6px; margin-bottom: 10px;">
                                
                                <!-- Group Title -->
                                <div class="group-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <h3 style="margin: 0; font-size: 1rem; color: #f0f6fc; font-weight: 600;">{{ $group->title }}</h3>
                                    <button class="btn-icon" style="color: #8b949e;" wire:click="$dispatch('open-group-modal', { groupId: {{ $group->id }} })">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                </div>

                                <!-- Tasks List (Types) -->
                                <div class="group-tasks" style="display: flex; flex-direction: column; gap: 6px;">
                                    @foreach($group->tasks as $task)
                                        <div class="task-item" 
                                             wire:key="task-{{ $task->id }}"
                                             wire:click.stop="$dispatch('open-task-modal', { taskId: {{ $task->id }} })"
                                             style="background: rgba(255,255,255,0.03); padding: 8px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border: 1px solid transparent;"
                                             onmouseover="this.style.borderColor='#30363d'"
                                             onmouseout="this.style.borderColor='transparent'">
                                            
                                            <span class="task-type" style="font-size: 0.9rem; color: #c9d1d9;">
                                                {{ $task->type ?: 'No Type' }}
                                            </span>

                                            <!-- Assignees Avatars -->
                                            <div class="task-assignees" style="display: flex; gap: -4px;">
                                                @foreach($task->users->take(3) as $user)
                                                    <div class="user-avatar-sm" title="{{ $user->name }}"
                                                         style="width: 20px; height: 20px; border-radius: 50%; background: #30363d; color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 1px solid #161b22; margin-left: -6px;">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                @endforeach
                                                @if($task->users->count() > 3)
                                                    <div class="user-avatar-sm" style="width: 20px; height: 20px; border-radius: 50%; background: #21262d; color: #8b949e; display: flex; align-items: center; justify-content: center; font-size: 9px; border: 1px solid #161b22; margin-left: -6px;">
                                                        +{{ $task->users->count() - 3 }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Add Task Button -->
                                <button class="add-subitem-btn" 
                                        wire:click="$dispatch('open-task-modal', { groupId: {{ $group->id }} })"
                                        style="background: none; border: 1px dashed #30363d; color: #8b949e; padding: 6px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; width: 100%; text-align: center; margin-top: 4px;">
                                    + Add Item
                                </button>
                            </div>
                        @endforeach
                        
                        <!-- New Group Button -->
                        <button class="add-card" wire:click="$dispatch('open-group-modal', { status: '{{ $statusKey }}' })">
                            <span>+</span> New Title
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modals -->
    @livewire('add-modal', ['boardId' => $board->id], key('add-modal')) 

    <script>
    // Store global state
    window.dragState = {
        isDragging: false,
        draggedElement: null,
        draggedGroupId: null,
        sourceStatus: null
    };

    document.addEventListener('livewire:initialized', () => {
        initializeDragAndDrop();
    });

    function initializeDragAndDrop() {
        const boardView = document.getElementById('board-view');
        if (!boardView) return;

        // Clean up existing listeners to avoid duplicates if any
        boardView.removeEventListener('dragstart', handleDragStart);
        boardView.removeEventListener('dragend', handleDragEnd);
        boardView.removeEventListener('dragover', handleDragOver);
        boardView.removeEventListener('drop', handleDrop);
        boardView.removeEventListener('dragenter', handleDragEnter);
        boardView.removeEventListener('dragleave', handleDragLeave);

        // Attach listeners to the container (delegation)
        boardView.addEventListener('dragstart', handleDragStart);
        boardView.addEventListener('dragend', handleDragEnd);
        boardView.addEventListener('dragover', handleDragOver);
        boardView.addEventListener('drop', handleDrop);
        boardView.addEventListener('dragenter', handleDragEnter);
        boardView.addEventListener('dragleave', handleDragLeave);
    }

    function handleDragStart(e) {
        const card = e.target.closest('.group-card');
        if (!card) return;
        
        // Required for Firefox
        e.dataTransfer.setData('text/plain', card.dataset.groupId);
        e.dataTransfer.effectAllowed = 'move';
        
        window.dragState.isDragging = true;
        window.dragState.draggedElement = card;
        window.dragState.draggedGroupId = card.dataset.groupId;
        
        const sourceContainer = card.closest('.cards-container');
        window.dragState.sourceStatus = sourceContainer ? sourceContainer.dataset.status : null;
        
        setTimeout(() => {
            card.classList.add('dragging');
        }, 0);
    }

    function handleDragEnd(e) {
        const card = e.target.closest('.group-card');
        if (card) {
            card.classList.remove('dragging');
        }
        
        document.querySelectorAll('.kanban-column').forEach(col => {
            col.style.backgroundColor = '';
        });
        
        window.dragState.isDragging = false;
        window.dragState.draggedElement = null;
        window.dragState.draggedGroupId = null;
    }

    function handleDragOver(e) {
        if (!window.dragState.isDragging) return;
        e.preventDefault(); // Allow drop
        
        const column = e.target.closest('.kanban-column');
        if (column) {
            column.style.backgroundColor = 'rgba(88, 166, 255, 0.05)';
        }
    }

    function handleDragEnter(e) {
        if (!window.dragState.isDragging) return;
        const column = e.target.closest('.kanban-column');
        if (column) {
            column.style.backgroundColor = 'rgba(88, 166, 255, 0.1)';
        }
    }

    function handleDragLeave(e) {
        const column = e.target.closest('.kanban-column');
        // Only clear if we are leaving the column (and not entering a child)
        if (column && !column.contains(e.relatedTarget)) {
            column.style.backgroundColor = '';
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        
        const column = e.target.closest('.kanban-column');
        if (!column) return;
        
        column.style.backgroundColor = '';
        
        if (!window.dragState.draggedGroupId) return;
        
        const cardsContainer = column.querySelector('.cards-container');
        const newStatus = cardsContainer.dataset.status;
        const groupId = window.dragState.draggedGroupId;
        
        // Calculate position
        const afterElement = getDragAfterElement(cardsContainer, e.clientY);
        const allCards = Array.from(cardsContainer.querySelectorAll('.group-card:not(.dragging)'));
        let newOrder = afterElement ? allCards.indexOf(afterElement) : allCards.length;
        
        // Optimistic UI update (optional, but let's trust Livewire for now)
        @this.call('updateGroupStatus', groupId, newStatus, newOrder);
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.group-card:not(.dragging)')];
        
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
</div>