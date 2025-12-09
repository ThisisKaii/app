
<div wire:poll.10s>
    <!-- Main Content -->
    <div class="container">
        <!-- Budget Summary Header -->
        <div class="budget-summary-card">
            <div class="budget-summary-header">
                <h2 class="budget-summary-title">Budget Overview</h2>
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
                    <div class="stat-label">Total Budget</div>
                    <div class="stat-value total-budget">${{ number_format($budget->total_budget, 2) }}</div>
                </div>

                <div class="budget-stat-card">
                    <div class="stat-label">Allocated</div>
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
                    <div class="stat-label">Spent</div>
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
                    <div class="stat-label">Remaining</div>
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
                <div class="budget-board-container" wire:poll.10s>
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
            @php
                $statusConfig = [
                    'draft' => ['label' => 'Draft', 'badge' => 'draft-badge'],
                    'pending' => ['label' => 'Pending', 'badge' => 'pending-badge'],
                    'approved' => ['label' => 'Approved', 'badge' => 'approved-badge'],
                    'rejected' => ['label' => 'Rejected', 'badge' => 'rejected-badge'],
                    'completed' => ['label' => 'Completed', 'badge' => 'completed-badge'],
                ];
            @endphp

            @foreach($statusConfig as $status => $config)
                <div class="kanban-column" data-status="{{ $status }}">
                    <div class="column-header">
                        <span class="status-badge {{ $config['badge'] }}"></span>
                        {{ $config['label'] }}
                        <span class="section-count">{{ $categories->where('status', $status)->count() }}</span>
                    </div>
                    <div class="cards-container" data-status="{{ $status }}">
                        @foreach($categories->where('status', $status) as $category)
                             @php
                                $totalSpent = $category->getTotalSpent();
                                $remaining = $category->getRemainingBudget();
                                $progress = $category->getProgressPercentage();
                            @endphp

                            <div wire:key="category-{{ $category->id }}" class="budget-category-card" draggable="true"
                                data-category-id="{{ $category->id }}"
                                wire:click.stop="openCategoryModal({{ $category->id }}, '{{ $category->status }}')">

                                <div class="category-card-header">
                                    <h3 class="category-title">{{ $category->title }}</h3>
                                </div>

                                <div class="category-budget-info">
                                    <div class="budget-row">
                                        <span class="budget-label">Estimated:</span>
                                        <span class="budget-amount estimated">${{ number_format($category->amount_estimated, 2) }}</span>
                                    </div>
                                    <div class="budget-row">
                                        <span class="budget-label">Spent:</span>
                                        <span class="budget-amount spent">${{ number_format($totalSpent, 2) }}</span>
                                    </div>
                                    <div class="budget-row">
                                        <span class="budget-label">Remaining:</span>
                                        <span class="budget-amount {{ $remaining < 0 ? 'over-budget' : 'remaining' }}">
                                            ${{ number_format(abs($remaining), 2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="category-progress">
                                    <div class="progress-bar-small">
                                        <div class="progress-fill-small"
                                            style="width: {{ min(100, $progress) }}%; 
                                                background-color: {{ $progress > 100 ? '#ef4444' : ($progress > 80 ? '#f59e0b' : '#22c55e') }}">
                                        </div>
                                    </div>
                                    <span class="progress-text">{{ number_format($progress, 0) }}%</span>
                                </div>

                                <div class="category-footer">
                                    <span class="expense-count">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ $category->expenses->count() }} {{ Str::plural('expense', $category->expenses->count()) }}
                                    </span>

                                    @if($category->description)
                                        <span class="has-description" title="{{ $category->description }}">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Add New Category Button -->
                        <button class="add-card" wire:click.stop="openCategoryModal(null, '{{ $status }}')">
                            <span>+</span> New Category
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Budget Edit Modal -->
    @if($showBudgetModal)
    @teleport('body')
        <div class="modal-overlay" wire:click.self="closeBudgetModal">
            <div class="modal-content budget-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>Edit Total Budget</h2>
                    <button type="button" wire:click="closeBudgetModal" class="modal-close">&times;</button>
                </div>

                <form wire:submit.prevent="saveBudget">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Total Budget <span class="required">*</span></label>
                            <div class="input-with-prefix">
                                <span class="input-prefix">$</span>
                                <input type="text" 
                                       wire:model.live.debounce.500ms="totalBudget" 
                                       required 
                                       placeholder="0.00"
                                       autofocus
                                       class="@error('totalBudget') border-error @enderror">
                            </div>
                            @error('totalBudget')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="budget-summary-info" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #e2e8f0;">
                            <p><strong>Current Allocated:</strong> ${{ number_format($totalAllocated, 2) }}</p>
                            <p><strong>Current Spent:</strong> ${{ number_format($totalSpent, 2) }}</p>
                            
                            @if(is_numeric($totalBudget) && $totalBudget < $totalAllocated)
                                <div class="budget-warning" style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; margin-top: 10px; padding: 10px; border-radius: 6px;">
                                    <strong>⚠️ Warning:</strong> Your budget is lower than the currently allocated amount of ${{ number_format($totalAllocated, 2) }}.
                                </div>
                            @elseif(is_numeric($totalBudget) && $totalBudget < $totalSpent)
                                <div class="budget-warning" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; margin-top: 10px; padding: 10px; border-radius: 6px;">
                                    <strong>⛔ Error:</strong> Budget cannot be less than what has already been spent.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" wire:click="closeBudgetModal" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveBudget">Save Budget</span>
                            <span wire:loading wire:target="saveBudget">
                                <span class="loading-spinner"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endteleport
    @endif

    <!-- Category Modal -->
    @if($showCategoryModal)
    @teleport('body')
        <div class="modal-overlay" wire:click.self="closeCategoryModal">
            <div class="modal-content budget-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>{{ $categoryId ? '✏️ Edit Category' : '➕ New Category' }}</h2>
                    <button type="button" wire:click="closeCategoryModal" class="modal-close">&times;</button>
                </div>

                <form wire:submit.prevent="saveCategory">
                    <div class="modal-body">
                        <!-- Title -->
                        <div class="form-group">
                            <label>Category Title <span class="required">*</span></label>
                            <input type="text" wire:model="categoryTitle" required
                                placeholder="e.g., Hardware Cleaning, Marketing">
                            @error('categoryTitle')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Amount and Status -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Estimated Amount <span class="required">*</span></label>
                                <div class="input-with-prefix">
                                    <span class="input-prefix">$</span>
                                    <input type="number" wire:model="amountEstimated" step="0.01" min="0" required
                                        placeholder="0.00">
                                </div>
                                @error('amountEstimated')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select wire:model="categoryStatus">
                                    <option value="draft">Draft</option>
                                    <option value="pending">⏳ Pending</option>
                                    <option value="approved">✅ Approved</option>
                                    <option value="rejected">❌ Rejected</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="categoryDescription" rows="3"
                                placeholder="Add details about this category..."></textarea>
                        </div>

                        <!-- Expenses List (if editing) -->
                        @if($categoryId)
                            @php
                                // We can use the loaded categories from the component to find this one to avoid extra query
                                // But finding it again is safer for freshness or just use relation
                                $currentCategory = $categories->find($categoryId);
                            @endphp

                            @if($currentCategory && $currentCategory->expenses->count() > 0)
                                <div class="expenses-section">
                                    <div class="expenses-header">
                                        <h3>Expenses</h3>
                                        <button type="button" class="btn-sm btn-primary"
                                            wire:click.prevent="openExpenseModal({{ $categoryId }})">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            Add Expense
                                        </button>
                                    </div>

                                    <div class="expenses-list">
                                        @foreach($currentCategory->expenses as $expense)
                                            <div class="expense-item" wire:key="expense-{{ $expense->id }}">
                                                <div class="expense-info">
                                                    <span class="expense-amount">${{ number_format($expense->amount, 2) }}</span>
                                                    @if($expense->description)
                                                        <span class="expense-desc">{{ $expense->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="expense-actions">
                                                    <button type="button" class="btn-icon"
                                                        wire:click.prevent="openExpenseModal({{ $categoryId }}, {{ $expense->id }})"
                                                        title="Edit">
                                                        <svg width="16" height="16" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn-icon btn-danger"
                                                        wire:click.prevent="confirmDeleteExpense({{ $expense->id }})" title="Delete">
                                                        <svg width="16" height="16" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="empty-expenses">
                                    <p>No expenses yet</p>
                                    <button type="button" class="btn-sm btn-primary"
                                        wire:click.prevent="openExpenseModal({{ $categoryId }})">
                                        Add First Expense
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer">
                        @if($categoryId)
                            <button type="button" wire:click="confirmDeleteCategory({{ $categoryId }})" class="btn-delete"
                                wire:loading.attr="disabled">
                                Delete Category
                            </button>
                        @endif

                        <div class="footer-actions">
                            <button type="button" wire:click="closeCategoryModal" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                                <span wire:loading.remove
                                    wire:target="saveCategory">{{ $categoryId ? 'Save Changes' : 'Create Category' }}</span>
                                <span wire:loading wire:target="saveCategory">Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endteleport
    @endif

    <!-- Expense Modal -->
    @if($showExpenseModal)
    @teleport('body')
        <div class="modal-overlay" wire:click.self="closeExpenseModal">
            <div class="modal-content small-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>{{ $expenseId ? '✏️ Edit Expense' : '➕ New Expense' }}</h2>
                    <button type="button" wire:click="closeExpenseModal" class="modal-close">&times;</button>
                </div>

                <form wire:submit.prevent="saveExpense">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Amount <span class="required">*</span></label>
                            <div class="input-with-prefix">
                                <span class="input-prefix">$</span>
                                <input type="number" wire:model="expenseAmount" step="0.01" min="0" required
                                    placeholder="0.00" autofocus>
                            </div>
                            @error('expenseAmount')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" wire:model="expenseDescription"
                                placeholder="e.g., Brush, CPU, Thermal Paste">
                            @error('expenseDescription')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" wire:click="closeExpenseModal" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                            <span wire:loading.remove
                                wire:target="saveExpense">{{ $expenseId ? 'Save Changes' : 'Add Expense' }}</span>
                            <span wire:loading wire:target="saveExpense">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endteleport
    @endif

    <!-- Delete Category Confirmation -->
    @if($showDeleteCategoryModal)
    @teleport('body')
        <div class="modal-overlay delete-overlay" wire:click.self="closeDeleteCategoryModal">
            <div class="delete-confirmation" wire:click.stop>
                <h2>⚠️ Confirm Delete</h2>
                <p>Are you sure you want to delete this category? All expenses within it will also be deleted. This action
                    cannot be undone.</p>

                <div class="delete-actions">
                    <button type="button" wire:click="deleteCategory" class="btn-confirm-delete"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteCategory">Yes, Delete</span>
                        <span wire:loading wire:target="deleteCategory">Deleting...</span>
                    </button>
                    <button type="button" wire:click="closeDeleteCategoryModal" class="btn-cancel-delete"
                        wire:loading.attr="disabled">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endteleport
    @endif

    <!-- Delete Expense Confirmation -->
    @if($showDeleteExpenseModal)
    @teleport('body')
        <div class="modal-overlay delete-overlay" wire:click.self="closeDeleteExpenseModal">
            <div class="delete-confirmation" wire:click.stop>
                <h2>⚠️ Confirm Delete</h2>
                <p>Are you sure you want to delete this expense? This action cannot be undone.</p>

                <div class="delete-actions">
                    <button type="button" wire:click="deleteExpense" class="btn-confirm-delete"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteExpense">Yes, Delete</span>
                        <span wire:loading wire:target="deleteExpense">Deleting...</span>
                    </button>
                    <button type="button" wire:click="closeDeleteExpenseModal" class="btn-cancel-delete"
                        wire:loading.attr="disabled">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endteleport
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