<!-- Board View -->
        <div class="container">
            <div id="board-view" class="kanban-container">
                <!-- Todo Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge todo"></span>
                        To do
                    </div>
                    <div class="cards-container" data-status="to_do">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'to_do'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'to_do'])

                    </div>
                </div> <!-- CLOSE TODO COLUMN -->

                <!-- In review Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge review"></span>
                        In review
                    </div>
                    <div class="cards-container" data-status="in_review">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'in_review'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'in_review'])
                    </div>
                </div> <!-- CLOSE IN REVIEW COLUMN -->

                <!-- In progress Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge progress"></span>
                        In progress
                    </div>
                    <div class="cards-container" data-status="in_progress">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'in_progress'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'in_progress'])
                    </div>
                </div> <!-- CLOSE IN PROGRESS COLUMN -->

                <!-- Published Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <span class="status-badge published"></span>
                        Published
                    </div>
                    <div class="cards-container" data-status="published">
                        @livewire('edit-modal', ['board' => $board, 'status' => 'published'])

                        @livewire('add-modal', ['boardId' => $board->id, 'status' => 'published'])

                    </div>
                </div> <!-- CLOSE PUBLISHED COLUMN -->
            </div> <!-- CLOSE KANBAN CONTAINER -->
        </div> <!-- CLOSE CONTAINER -->