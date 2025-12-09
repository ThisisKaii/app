<!-- Modern Budget Board View -->
<div>
    <!-- Main Content -->
    <div class="container">
        <!-- Budget Summary Header -->
        <div class="budget-summary-card">
            <div class="budget-summary-header">
                <h2 class="budget-summary-title">💰 Budget Overview</h2>
                <button class="btn-primary" wire:click="openBudgetModal" type="button">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Budget
                </button>
            </div>

            <div class="budget-stats-grid">
                <div class="budget-stat-card">
                    <div class="stat-label">💵 Total Budget</div>
                    <div class="stat-value total-budget">${{ number_format($budget->total_budget, 2) }}</div>
                </div>

                <div class="budget-stat-card">
                    <div class="stat-label">📊 Allocated</div>
                    <div class="stat-value allocated">${{ number_format($totalAllocated, 2) }}</div>
                    <div class="stat-percentage">
                        @if($budget->total_budget > 0)
                            {{ number_format(($totalAllocated / $budget->total_budget) * 100, 1) }}% of total
                        @else
                            0% of total
                        @endif
                    </div>
                </div>

                <div class="budget-stat-card">
                    <div class="stat-label">💸 Spent</div>
                    <div class="stat-value spent">${{ number_format($totalSpent, 2) }}</div>
                    <div class="stat-percentage">
                        @if($budget->total_budget > 0)
                            {{ number_format(($totalSpent / $budget->total_budget) * 100, 1) }}% of total
                        @else
                            0% of total
                        @endif
                    </div>
                </div>

                <div class="budget-stat-card">
                    <div class="stat-label">💰 Remaining</div>
                    <div class="stat-value {{ $remaining < 0 ? 'over-budget' : 'remaining' }}">
                        ${{ number_format(abs($remaining), 2) }}
                    </div>
                    @if($remaining < 0)
                        <div class="stat-warning">⚠️ Over Budget!</div>
                    @endif
                </div>
            </div>

            <!-- Modern Progress Bar -->
            @if($budget->total_budget > 0)
                <div class="budget-progress-container">
                    <div class="budget-progress-bar">
                        <div class="progress-fill allocated-fill"
                            style="width: {{ min(100, ($totalAllocated / $budget->total_budget) * 100) }}%">
                        </div>
                        <div class="progress-fill spent-fill"
                            style="width: {{ min(100, ($totalSpent / $budget->total_budget) * 100) }}%">
                        </div>
                    </div>
                    <div class="progress-legend">
                        <span class="legend-item">
                            <span class="legend-color allocated-color"></span> Allocated
                        </span>
                        <span class="legend-item">
                            <span class="legend-color spent-color"></span> Spent
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Budget Categories Board (Kanban Style) -->
        <div id="budget-board" class="kanban-container">
            <!-- Draft Column -->
            <div class="kanban-column" data-status="draft">
                <div class="column-header">
                    <span class="status-badge draft-badge"></span>
                    📝 Draft
                    <span class="section-count">{{ $categories->where('status', 'draft')->count() }}</span>
                </div>
                <div class="cards-container" data-status="draft">
                    @livewire('budget-category-modal', ['budget' => $budget, 'status' => 'draft', 'board' => $board], key('category-modal-draft'))
                </div>
            </div>

            <!-- Pending Column -->
            <div class="kanban-column" data-status="pending">
                <div class="column-header">
                    <span class="status-badge pending-badge"></span>
                    ⏳ Pending
                    <span class="section-count">{{ $categories->where('status', 'pending')->count() }}</span>
                </div>
                <div class="cards-container" data-status="pending">
                    @livewire('budget-category-modal', ['budget' => $budget, 'status' => 'pending', 'board' => $board], key('category-modal-pending'))
                </div>
            </div>

            <!-- Approved Column -->
            <div class="kanban-column" data-status="approved">
                <div class="column-header">
                    <span class="status-badge approved-badge"></span>
                    ✅ Approved
                    <span class="section-count">{{ $categories->where('status', 'approved')->count() }}</span>
                </div>
                <div class="cards-container" data-status="approved">
                    @livewire('budget-category-modal', ['budget' => $budget, 'status' => 'approved', 'board' => $board], key('category-modal-approved'))
                </div>
            </div>

            <!-- Rejected Column -->
            <div class="kanban-column" data-status="rejected">
                <div class="column-header">
                    <span class="status-badge rejected-badge"></span>
                    ❌ Rejected
                    <span class="section-count">{{ $categories->where('status', 'rejected')->count() }}</span>
                </div>
                <div class="cards-container" data-status="rejected">
                    @livewire('budget-category-modal', ['budget' => $budget, 'status' => 'rejected', 'board' => $board], key('category-modal-rejected'))
                </div>
            </div>

            <!-- Completed Column -->
            <div class="kanban-column" data-status="completed">
                <div class="column-header">
                    <span class="status-badge completed-badge"></span>
                    🎉 Completed
                    <span class="section-count">{{ $categories->where('status', 'completed')->count() }}</span>
                </div>
                <div class="cards-container" data-status="completed">
                    @livewire('budget-category-modal', ['budget' => $budget, 'status' => 'completed', 'board' => $board], key('category-modal-completed'))
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Edit Modal - Separate from main container -->
    @if($showBudgetModal)
        <div class="modal-overlay" wire:click.self="closeBudgetModal">
            <div class="modal-content budget-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>💰 Edit Total Budget</h2>
                    <button type="button" wire:click="closeBudgetModal" class="modal-close">&times;</button>
                </div>

                <form wire:submit.prevent="saveBudget">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Total Budget <span class="required">*</span></label>
                            <div class="input-with-prefix">
                                <span class="input-prefix">$</span>
                                <input type="text" 
                                       wire:model.live="totalBudget" 
                                       wire:keyup.debounce.500ms="updatedTotalBudget"
                                       required 
                                       placeholder="0.00"
                                       autofocus
                                       class="@error('totalBudget') border-error @enderror">
                            </div>
                            @error('totalBudget')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="budget-summary-info">
                            <p><strong>📊 Current Allocated:</strong> ${{ number_format($totalAllocated, 2) }}</p>
                            <p><strong>💸 Current Spent:</strong> ${{ number_format($totalSpent, 2) }}</p>
                            @if($totalAllocated > 0 || $totalSpent > 0)
                                <div class="budget-warning">
                                    <strong>⚠️ Important:</strong> Your new budget must cover all allocated and spent amounts.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" wire:click="closeBudgetModal" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveBudget">💾 Save Budget</span>
                            <span wire:loading wire:target="saveBudget">
                                <span class="loading-spinner"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif
</div>

<script>
    // Store global state for budget categories
    window.budgetDragState = {
        isDragging: false,
        draggedElement: null,
        draggedCategoryId: null,
        sourceStatus: null
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        initializeBudgetDragAndDrop();
    });

    // Reinitialize after Livewire updates
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', () => {
            setTimeout(() => {
                console.log('Reinitializing budget drag and drop after Livewire morph');
                initializeBudgetDragAndDrop();
            }, 50);
        });
    });

    function initializeBudgetDragAndDrop() {
        const budgetBoard = document.getElementById('budget-board');
        if (!budgetBoard) {
            console.log('Budget board not found');
            return;
        }

        // Use event delegation on the board container
        budgetBoard.removeEventListener('dragstart', handleBudgetDragStart);
        budgetBoard.removeEventListener('dragend', handleBudgetDragEnd);
        budgetBoard.addEventListener('dragstart', handleBudgetDragStart);
        budgetBoard.addEventListener('dragend', handleBudgetDragEnd);

        // Set up columns
        const columns = budgetBoard.querySelectorAll('.kanban-column');
        console.log(`Found ${columns.length} budget columns`);

        columns.forEach(column => {
            column.removeEventListener('dragover', handleBudgetDragOver);
            column.removeEventListener('drop', handleBudgetDrop);
            column.removeEventListener('dragleave', handleBudgetDragLeave);

            column.addEventListener('dragover', handleBudgetDragOver);
            column.addEventListener('drop', handleBudgetDrop);
            column.addEventListener('dragleave', handleBudgetDragLeave);
        });

        // Make all current category cards draggable
        const cards = budgetBoard.querySelectorAll('.budget-category-card');
        console.log(`Found ${cards.length} budget category cards`);
        cards.forEach(card => {
            card.setAttribute('draggable', 'true');
        });
    }

    function handleBudgetDragStart(e) {
        // Check if the dragged element is a budget category card
        const card = e.target.closest('.budget-category-card');
        if (!card) return;

        e.stopPropagation();

        window.budgetDragState.isDragging = true;
        window.budgetDragState.draggedElement = card;
        window.budgetDragState.draggedCategoryId = card.dataset.categoryId;

        // Find the source column
        const sourceContainer = card.closest('.cards-container');
        window.budgetDragState.sourceStatus = sourceContainer ? sourceContainer.dataset.status : null;

        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', window.budgetDragState.draggedCategoryId);

        console.log('Budget drag started:', {
            categoryId: window.budgetDragState.draggedCategoryId,
            sourceStatus: window.budgetDragState.sourceStatus
        });
    }

    function handleBudgetDragEnd(e) {
        const card = e.target.closest('.budget-category-card');
        if (!card) return;

        card.classList.remove('dragging');

        // Reset column backgrounds
        document.querySelectorAll('.kanban-column').forEach(col => {
            col.classList.remove('drag-over');
            col.style.backgroundColor = '';
        });

        console.log('Budget drag ended');

        // Reset state after a short delay
        setTimeout(() => {
            window.budgetDragState.isDragging = false;
            window.budgetDragState.draggedElement = null;
            window.budgetDragState.draggedCategoryId = null;
            window.budgetDragState.sourceStatus = null;
        }, 100);
    }

    function handleBudgetDragOver(e) {
        if (!window.budgetDragState.isDragging) return;

        e.preventDefault();
        e.stopPropagation();
        e.dataTransfer.dropEffect = 'move';

        const column = e.currentTarget;
        column.style.backgroundColor = 'rgba(88, 166, 255, 0.1)';
    }

    function handleBudgetDragLeave(e) {
        // Only clear if we're actually leaving the column
        if (e.currentTarget.contains(e.relatedTarget)) return;
        e.currentTarget.style.backgroundColor = '';
    }

    function handleBudgetDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        const column = e.currentTarget;
        column.style.backgroundColor = '';

        if (!window.budgetDragState.draggedCategoryId) {
            console.log('No budget category being dragged');
            return;
        }

        const cardsContainer = column.querySelector('.cards-container');
        if (!cardsContainer) {
            console.log('No cards container found');
            return;
        }

        const newStatus = cardsContainer.dataset.status;
        const categoryId = window.budgetDragState.draggedCategoryId;

        console.log(`Dropping budget category ${categoryId} into ${newStatus}`);

        // Calculate new position
        const afterElement = getBudgetDragAfterElement(cardsContainer, e.clientY);
        const allCards = Array.from(cardsContainer.querySelectorAll('.budget-category-card:not(.dragging)'));
        let newOrder = 0;

        if (afterElement) {
            const afterIndex = allCards.indexOf(afterElement);
            newOrder = afterIndex;
        } else {
            newOrder = allCards.length;
        }

        console.log(`Moving to position ${newOrder}`);

        // Send update to server
        updateBudgetCategoryStatus(categoryId, newStatus, newOrder);
    }

    function getBudgetDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.budget-category-card:not(.dragging)')];

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

    async function updateBudgetCategoryStatus(categoryId, newStatus, newOrder) {
        try {
            const url = `/todobido/budget-category/${categoryId}`;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            console.log('Sending budget category update:', { categoryId, newStatus, newOrder });

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
                console.log('Budget category updated successfully');
                // Refresh the board to show updated state
                window.location.reload();
            } else {
                console.error('Update failed:', data.message);
                window.location.reload();
            }
        } catch (error) {
            console.error('Error updating budget category:', error);
            alert('Failed to update budget category. Please try again.');
            window.location.reload();
        }
    }

    // Handle card clicks separately from drag events
    window.handleBudgetCardClick = function (event, categoryId, status) {
        // Only trigger if we're not dragging
        if (!window.budgetDragState.isDragging) {
            Livewire.dispatch('open-category-modal-' + status, {
                categoryId: categoryId
            });
        }
    }
    
    // Format currency input as user types
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[wire\\:model="totalBudget"]')) {
            const input = e.target;
            let value = input.value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limit to 2 decimal places
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }
            
            input.value = value;
        }
    });
</script>