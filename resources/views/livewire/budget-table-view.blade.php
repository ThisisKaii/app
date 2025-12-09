<div class="table-container" wire:poll.10s.keep-alive>
    <!-- Budget Summary Header -->
    <div class="budget-summary-header-compact">
        <div class="summary-stats">
            <div class="summary-stat">
                <span class="summary-label">Total Budget:</span>
                <span class="summary-value">${{ number_format($budget->total_budget, 2) }}</span>
            </div>
            <div class="summary-stat">
                <span class="summary-label">Allocated:</span>
                <span class="summary-value allocated">${{ number_format($totalAllocated, 2) }}</span>
            </div>
            <div class="summary-stat">
                <span class="summary-label">Spent:</span>
                <span class="summary-value spent">${{ number_format($totalSpent, 2) }}</span>
            </div>
            <div class="summary-stat">
                <span class="summary-label">Remaining:</span>
                <span class="summary-value {{ $remaining < 0 ? 'over-budget' : 'remaining' }}">
                    ${{ number_format(abs($remaining), 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Table Header with Actions -->
    <div class="table-header">
        <h2 class="table-title">Budget Categories</h2>
        <div class="table-stats">
            <button class="filter-btn" wire:click="toggleFilters">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>

            <button class="add-task-btn" wire:click="openCategoryModal()">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Category
            </button>

            <span class="stat-badge">
                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                {{ count($categories) }} {{ Str::plural('category', count($categories)) }}
            </span>
        </div>
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
        <div class="filter-panel active">
            <div class="filter-content">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" class="filter-input" placeholder="Search categories..."
                        wire:model.live.debounce.300ms="searchFilter">
                </div>

                <button class="clear-filters-btn" wire:click="clearFilters">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    @endif

    <!-- Table -->
    @if(count($categories) > 0)
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Estimated</th>
                        <th>Spent</th>
                        <th>Remaining</th>
                        <th>Progress</th>
                        <th>Expenses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        @php
                            $totalSpent = $category->getTotalSpent();
                            $remaining = $category->getRemainingBudget();
                            $progress = $category->getProgressPercentage();
                        @endphp
                        <tr wire:key="category-{{ $category->id }}" class="table-row">
                            <td>
                                <div class="category-title-cell">
                                    <strong>{{ $category->title }}</strong>
                                    @if($category->description)
                                        <small class="category-description">{{ Str::limit($category->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="status-text">{{ ucfirst($category->status) }}</span>
                            </td>
                            <td>
                                <span class="amount-text estimated">${{ number_format($category->amount_estimated, 2) }}</span>
                            </td>
                            <td>
                                <span class="amount-text spent">${{ number_format($totalSpent, 2) }}</span>
                            </td>
                            <td>
                                <span class="amount-text {{ $remaining < 0 ? 'over-budget' : 'remaining' }}">
                                    ${{ number_format(abs($remaining), 2) }}
                                </span>
                            </td>
                            <td>
                                <div class="progress-cell">
                                    <div class="progress-bar-mini">
                                        <div class="progress-fill-mini" style="width: {{ min(100, $progress) }}%; 
                                            background-color: {{ $progress > 100 ? '#ef4444' : ($progress > 80 ? '#f59e0b' : '#22c55e') }}">
                                        </div>
                                    </div>
                                    <span class="progress-percentage">{{ number_format($progress, 0) }}%</span>
                                </div>
                            </td>
                            <td>
                                <button class="expense-count-btn" wire:click="openCategoryModal({{ $category->id }})">
                                    {{ $category->expenses->count() }} {{ Str::plural('item', $category->expenses->count()) }}
                                </button>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit-btn" wire:click="openCategoryModal({{ $category->id }})"
                                        title="Edit">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn add-btn" wire:click="openExpenseModal({{ $category->id }})"
                                        title="Add Expense">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <h3 class="empty-title">No categories found</h3>
            <p class="empty-text">
                @if($statusFilter || $searchFilter)
                    Try adjusting your filters or create a new category.
                @else
                    Create your first budget category to get started.
                @endif
            </p>
        </div>
    @endif

    <!-- Category Modal (same as budget-category-modal but simplified) -->
    @if($showCategoryModal)
        <div class="modal-overlay" wire:click.self="closeCategoryModal">
            <div class="modal-content budget-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>{{ $categoryId ? 'Edit Category' : 'New Category' }}</h2>
                    <button type="button" wire:click="closeCategoryModal" class="modal-close">&times;</button>
                </div>

                <form wire:submit.prevent="saveCategory">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Category Title <span class="required">*</span></label>
                            <input type="text" wire:model="categoryTitle" required>
                            @error('categoryTitle')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Estimated Amount <span class="required">*</span></label>
                                <div class="input-with-prefix">
                                    <span class="input-prefix">$</span>
                                    <input type="number" wire:model="amountEstimated" step="0.01" min="0" required>
                                </div>
                                @error('amountEstimated')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select wire:model="categoryStatus">
                                    <option value="draft">Draft</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="categoryDescription" rows="3"></textarea>
                        </div>

                        @if($categoryId)
                            @php
                                $category = \App\Models\BudgetCategory::with('expenses')->find($categoryId);
                            @endphp

                            @if($category && $category->expenses->count() > 0)
                                <div class="expenses-section">
                                    <div class="expenses-header">
                                        <h3>Expenses ({{ $category->expenses->count() }})</h3>
                                        <button type="button" class="btn-sm btn-primary"
                                            wire:click.prevent="openExpenseModal({{ $categoryId }})">
                                            Add Expense
                                        </button>
                                    </div>

                                    <div class="expenses-list">
                                        @foreach($category->expenses as $expense)
                                            <div class="expense-item" wire:key="expense-{{ $expense->id }}">
                                                <div class="expense-info">
                                                    <span class="expense-amount">${{ number_format($expense->amount, 2) }}</span>
                                                    @if($expense->description)
                                                        <span class="expense-desc">{{ $expense->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="expense-actions">
                                                    <button type="button" class="btn-icon"
                                                        wire:click.prevent="openExpenseModal({{ $categoryId }}, {{ $expense->id }})">
                                                        <svg width="16" height="16" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn-icon btn-danger"
                                                        wire:click.prevent="confirmDeleteExpense({{ $expense->id }})">
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
                            @endif
                        @endif
                    </div>

                    <div class="modal-footer">
                        @if($categoryId)
                            <button type="button" wire:click="confirmDeleteCategory({{ $categoryId }})" class="btn-delete">
                                Delete Category
                            </button>
                        @else
                            <div></div>
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
    @endif

    <!-- Expense Modal -->
    @if($showExpenseModal)
        <div class="modal-overlay" wire:click.self="closeExpenseModal">
            <div class="modal-content small-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>{{ $expenseId ? 'Edit Expense' : 'New Expense' }}</h2>
                    <button type="button" wire:click="closeExpenseModal" class="modal-close">&times;</button>
                </div>

                <form wire:submit.prevent="saveExpense">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Amount <span class="required">*</span></label>
                            <div class="input-with-prefix">
                                <span class="input-prefix">$</span>
                                <input type="number" wire:model="expenseAmount" step="0.01" min="0" required autofocus>
                            </div>
                            @error('expenseAmount')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" wire:model="expenseDescription"
                                placeholder="e.g., Brush, CPU, Thermal Paste">
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
    @endif

    <!-- Delete Modals (same as before) -->
    @if($showDeleteCategoryModal)
        <div class="modal-overlay delete-overlay" wire:click.self="closeDeleteCategoryModal">
            <div class="delete-confirmation" wire:click.stop>
                <h2>Confirm Delete</h2>
                <p>Are you sure you want to delete this category? All expenses will also be deleted.</p>
                <div class="delete-actions">
                    <button type="button" wire:click="deleteCategory" class="btn-confirm-delete">
                        <span wire:loading.remove wire:target="deleteCategory">Yes, Delete</span>
                        <span wire:loading wire:target="deleteCategory">Deleting...</span>
                    </button>
                    <button type="button" wire:click="closeDeleteCategoryModal" class="btn-cancel-delete">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteExpenseModal)
        <div class="modal-overlay delete-overlay" wire:click.self="closeDeleteExpenseModal">
            <div class="delete-confirmation" wire:click.stop>
                <h2>Confirm Delete</h2>
                <p>Are you sure you want to delete this expense?</p>
                <div class="delete-actions">
                    <button type="button" wire:click="deleteExpense" class="btn-confirm-delete">
                        <span wire:loading.remove wire:target="deleteExpense">Yes, Delete</span>
                        <span wire:loading wire:target="deleteExpense">Deleting...</span>
                    </button>
                    <button type="button" wire:click="closeDeleteExpenseModal" class="btn-cancel-delete">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
