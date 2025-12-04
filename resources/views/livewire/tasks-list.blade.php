<div class="tasklist-container">
    <!-- Header Tabs -->
    <div style="padding: 1rem 2rem; background-color: #161b22; border-bottom: 1px solid #21262d;">
        <div style="display: flex; gap: 0.5rem; margin: 0; padding: 0;">
            <button 
                wire:click="switchView('individual')" 
                style="padding: 0.5rem 1rem; 
                       color: {{ $view === 'individual' ? '#f0f6fc' : '#8b949e' }}; 
                       background-color: {{ $view === 'individual' ? '#30363d' : 'transparent' }}; 
                       border: 1px solid {{ $view === 'individual' ? '#58a6ff' : '#30363d' }}; 
                       border-radius: 6px; 
                       font-size: 0.875rem; 
                       display: flex; 
                       align-items: center; 
                       gap: 0.5rem; 
                       cursor: pointer; 
                       transition: all 0.2s;
                       font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;"> 
                       My Tasks
            </button>

            <button 
                wire:click="switchView('teams')"  
                style="padding: 0.5rem 1rem; 
                       color: {{ $view === 'teams' ? '#f0f6fc' : '#8b949e' }}; 
                       background-color: {{ $view === 'teams' ? '#30363d' : 'transparent' }}; 
                       border: 1px solid {{ $view === 'teams' ? '#58a6ff' : '#30363d' }}; 
                       border-radius: 6px; 
                       font-size: 0.875rem; 
                       display: flex; 
                       align-items: center; 
                       gap: 0.5rem; 
                       cursor: pointer; 
                       transition: all 0.2s;
                       font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;"> 
                       Team Overview
            </button>
        </div>
    </div>

    <!-- Views Content -->
    <div style="padding: 0; width: 100%;">
        @if ($view === 'individual')
            @livewire('individual', ['boardId' => $boardId], key('individual-' . $boardId))
        @endif

        @if ($view === 'teams')
            @livewire('teams', ['boardId' => $boardId], key('teams-' . $boardId))
        @endif
    </div>
</div>