<div>
    <!-- Category Cards -->
    @foreach($categories as $category)
        @php
            $totalSpent = $category->getTotalSpent();
            $remaining = $category->getRemainingBudget();
            $progress = $category->getProgressPercentage();
        @endphp

        <div wire:key="category-{{ $category->id }}" class="budget-category-card"
            wire:click.stop="$dispatch('open-category-modal-{{ $status }}', { categoryId: {{ $category->id }} })">
            
            <!-- Category Header -->
            <div class="category-card-header">
                <h3 class="category-title">{{ $category->title }}</h3>
                <span class="category-id">#{{ $category->id }}</span>
            </div>

            <!-- Budget Info -->
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

            <!-- Progress Bar -->
            <div class="category-progress">
                <div class="progress-bar-small">
                    <div class="progress-fill-small" style="width: {{ min(100, $progress) }}%; 
                        background-color: {{ $progress > 100 ? '#ef4444' : ($progress > 80 ? '#f59e0b' : '#22c55e') }}">
                    </div>
                </div>
                <span class="progress-text">{{ number_format($progress, 0) }}%</span>
            </div>

            <!-- Expenses Count -->
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    @endforeach

    <!-- Add New Category Button -->
    <button class="add-card" wire:click.stop="$dispatch('open-category-modal-{{ $status }}')">
        <span>+</span> New Category
    </button>

    <!-- Category Modal -->
    @if($showCategoryModal)
        <div class="modal-overlay" wire:click.self="closeCategoryModal">
            <div class="modal-content budget-modal" wire:click.stop>
                <div class="modal-header">
                    <h2>{{ $categoryId ? 'Edit Category' : 'New Category' }}</h2>
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
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
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
                                $category = \App\Models\BudgetCategory::with('expenses')->find($categoryId);
                            @endphp

                            @if($category && $category->expenses->count() > 0)
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
                                                        wire:click.prevent="openExpenseModal({{ $categoryId }}, {{ $expense->id }})"
                                                        title="Edit">
                                                        <svg width="16" height="16" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn-icon btn-danger"
                                                        wire:click.prevent="confirmDeleteExpense({{ $expense->id }})"
                                                        title="Delete">
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
    @endif

    <!-- Delete Category Confirmation -->
    @if($showDeleteCategoryModal)
        <div class="modal-overlay delete-overlay" wire:click.self="closeDeleteCategoryModal">
            <div class="delete-confirmation" wire:click.stop>
                <h2>Confirm Delete</h2>
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
    @endif

    <!-- Delete Expense Confirmation -->
    @if($showDeleteExpenseModal)
        <div class="modal-overlay delete-overlay" wire:click.self="closeDeleteExpenseModal">
            <div class="delete-confirmation" wire:click.stop>
                <h2>Confirm Delete</h2>
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
    @endif
</div>