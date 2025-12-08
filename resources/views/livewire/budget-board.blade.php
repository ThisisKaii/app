<!-- Modern Budget Board View -->
<div class="container">
    <!-- Budget Summary Header -->
    <div class="budget-summary-card">
        <div class="budget-summary-header">
            <h2 class="budget-summary-title"> Budget Overview</h2>
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
                <div class="stat-label"> Total Budget</div>
                <div class="stat-value total-budget">${{ number_format($budget->total_budget, 2) }}</div>
            </div>

            <div class="budget-stat-card">
                <div class="stat-label"> Allocated</div>
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
                <div class="stat-label"> Spent</div>
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
                <div class="stat-label"> Remaining</div>
                <div class="stat-value {{ $remaining < 0 ? 'over-budget' : 'remaining' }}">
                    ${{ number_format(abs($remaining), 2) }}
                </div>
                @if($remaining < 0)
                    <div class="stat-warning"> Over Budget!</div>
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
                 Draft
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
                 Pending
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
                 Approved
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
                 Rejected
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
                 Completed
                <span class="section-count">{{ $categories->where('status', 'completed')->count() }}</span>
            </div>
            <div class="cards-container" data-status="completed">
                @livewire('budget-category-modal', ['budget' => $budget, 'status' => 'completed', 'board' => $board], key('category-modal-completed'))
            </div>
        </div>
    </div>
</div>

<!-- Budget Edit Modal -->
@if($showBudgetModal)
    <div class="modal-overlay" wire:click.self="closeBudgetModal">
        <div class="modal-content budget-modal" wire:click.stop>
            <div class="modal-header">
                <h2> Edit Total Budget</h2>
                <button type="button" wire:click="closeBudgetModal" class="modal-close">&times;</button>
            </div>

            <form wire:submit.prevent="saveBudget">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Total Budget <span class="required">*</span></label>
                        <div class="input-with-prefix">
                            <span class="input-prefix">$</span>
                            <input type="number" wire:model="totalBudget" step="0.01" min="0" required
                                placeholder="0.00" autofocus>
                        </div>
                        @error('totalBudget')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="budget-summary-info">
                        <p><strong> Current Allocated:</strong> ${{ number_format($totalAllocated, 2) }}</p>
                        <p><strong> Current Spent:</strong> ${{ number_format($totalSpent, 2) }}</p>
                        @if($totalAllocated > 0 || $totalSpent > 0)
                            <p style="color: #8b949e; font-size: 0.875rem; margin-top: 0.5rem;">
                                 Note: Make sure your new budget covers all allocated and spent amounts.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" wire:click="closeBudgetModal" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveBudget"> Save Budget</span>
                        <span wire:loading wire:target="saveBudget">
                            <span class="loading-spinner"></span> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
