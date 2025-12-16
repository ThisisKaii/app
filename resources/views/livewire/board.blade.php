<div wire:poll.5s>
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
                                 @can('update', $group)
                                     draggable="true"
                                     style="cursor: grab; display: flex; flex-direction: column; gap: 10px; background-color: #161b22; border: 1px solid #30363d; padding: 12px; border-radius: 6px; margin-bottom: 10px;"
                                 @else
                                     draggable="false"
                                     style="cursor: not-allowed; display: flex; flex-direction: column; gap: 10px; background-color: #161b22; border: 1px solid #30363d; padding: 12px; border-radius: 6px; margin-bottom: 10px; opacity: 0.9;"
                                 @endcan
                                 class="task-card group-card" 
                                 data-group-id="{{ $group->id }}">
                                
                                <!-- Group Title -->
                                <div class="group-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <h3 style="margin: 0; font-size: 1rem; color: #f0f6fc; font-weight: 600;">{{ $group->title }}</h3>
                                    @can('update', $group)
                                    <button class="btn-icon" style="color: #8b949e;" wire:click="$dispatch('open-group-modal', { groupId: {{ $group->id }} })">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    @endcan
                                </div>

                                <!-- Tasks List (Types) -->
                                <div class="group-tasks" style="display: flex; flex-direction: column; gap: 6px;">
                                    @foreach($group->tasks as $task)
                                        <div class="task-item" 
                                             wire:key="task-{{ $task->id }}"
                                             @if($canEdit)
                                                 wire:click.stop="$dispatch('open-task-modal', { taskId: {{ $task->id }} })"
                                             @else
                                                 wire:click.stop="takeTask({{ $task->id }})"
                                                 title="Click to take this task"
                                             @endif
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

                                <!-- Add Task Button - Only for Owner/Admin -->
                                @can('createTask', $board)
                                <button class="add-subitem-btn" 
                                        wire:click="$dispatch('open-task-modal', { groupId: {{ $group->id }} })"
                                        style="background: none; border: 1px dashed #30363d; color: #8b949e; padding: 6px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; width: 100%; text-align: center; margin-top: 4px;">
                                    + Add Item
                                </button>
                                @endcan
                            </div>
                        @endforeach
                        
                        <!-- New Group Button - Only for Owner/Admin -->
                        @can('createTask', $board)
                        <button class="add-card" wire:click="$dispatch('open-group-modal', { status: '{{ $statusKey }}' })">
                            <span>+</span> New Title
                        </button>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modals -->
    @livewire('add-modal', ['boardId' => $board->id], key('add-modal')) 

    <script>
    // Initialize Drag and Drop (Global Delegation)
    if (!window.boardDnDInitialized) {
        window.boardDnDInitialized = true;

        // Global State
        window.dragState = {
            isDragging: false,
            draggedElement: null,
            draggedGroupId: null,
            sourceStatus: null
        };

        // --- Event Listeners (Document Level) ---
        
        document.addEventListener('dragstart', function(e) {
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

            // Visual feedback
            setTimeout(() => card.classList.add('dragging'), 0);
        });

        document.addEventListener('dragend', function(e) {
            const card = e.target.closest('.group-card');
            if (card) {
                card.classList.remove('dragging');
            }
            
            // Clean up visual cues
            document.querySelectorAll('.kanban-column').forEach(col => {
                col.style.backgroundColor = '';
            });

            window.dragState.isDragging = false;
            window.dragState.draggedElement = null;
            window.dragState.draggedGroupId = null;
        });

        document.addEventListener('dragover', function(e) {
            if (!window.dragState.isDragging) return;
            
            const column = e.target.closest('.kanban-column');
            if (column) {
                e.preventDefault(); // Allow drop
                column.style.backgroundColor = 'rgba(88, 166, 255, 0.05)';
            }
        });

        document.addEventListener('dragenter', function(e) {
            if (!window.dragState.isDragging) return;
            const column = e.target.closest('.kanban-column');
            if (column) {
                column.style.backgroundColor = 'rgba(88, 166, 255, 0.1)';
            }
        });

        document.addEventListener('dragleave', function(e) {
            const column = e.target.closest('.kanban-column');
            if (column && !column.contains(e.relatedTarget)) {
                column.style.backgroundColor = '';
            }
        });

        document.addEventListener('drop', function(e) {
            if (!window.dragState.isDragging) return;
            const column = e.target.closest('.kanban-column');
            if (!column) return;

            e.preventDefault();
            column.style.backgroundColor = '';

            const groupId = window.dragState.draggedGroupId;
            const cardsContainer = column.querySelector('.cards-container');
            const newStatus = cardsContainer.dataset.status;

            // Calculate new order
            const afterElement = getDragAfterElement(cardsContainer, e.clientY);
            const allCards = Array.from(cardsContainer.querySelectorAll('.group-card:not(.dragging)'));
            let newOrder = afterElement ? allCards.indexOf(afterElement) : allCards.length;

            // Call Livewire Component
            // Use the root element's wire:id
            const root = document.getElementById('board-view').closest('[wire\\:id]');
            const component = root ? Livewire.find(root.getAttribute('wire:id')) : null;
            
            if (component) {
                component.call('updateGroupStatus', groupId, newStatus, newOrder);
            } else {
                console.error('Livewire component not found');
            }
        });
    }

    // Scalar Helper
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