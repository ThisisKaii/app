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
                       cursor: {{ $hasBoardMembers ? 'pointer' : 'not-allowed' }}; 
                       opacity: {{ $hasBoardMembers ? '1' : '0.5' }};
                       transition: all 0.2s;
                       font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;"
                {{ !$hasBoardMembers ? 'disabled' : '' }}> 
                       Team Overview
                       @if(!$hasBoardMembers)
                           <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20">
                               <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                           </svg>
                       @endif
            </button>
        </div>
        
        @if(!$hasBoardMembers && $view === 'individual')
            <div style="margin-top: 0.75rem; padding: 0.5rem 1rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; color: #ef4444; font-size: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 14px; height: 14px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                Add team members to unlock Team Overview
            </div>
        @endif
    </div>

    <!-- Views Content -->
    <div style="padding: 0; width: 100%;">
        @if ($view === 'individual')
            @livewire('individual', ['boardId' => $boardId], key('individual-' . $boardId))
        @endif

        @if ($view === 'teams' && $hasBoardMembers)
            @livewire('teams', ['boardId' => $boardId], key('teams-' . $boardId))
        @endif
    </div>
</div>