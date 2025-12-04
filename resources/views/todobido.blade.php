<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/todo.css', 'resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Todobido</title>
    @livewireStyles

</head>

<body>
    @if(session('success'))
        <div id="toast-success"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;">
            <span style="font-size: 1.5rem;">✓</span>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0; margin-left: 0.5rem; line-height: 1;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div id="toast-error"
            style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #ef4444; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;">
            <span style="font-size: 1.5rem;">✕</span>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()"
                style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0; margin-left: 0.5rem; line-height: 1;">&times;</button>
        </div>
    @endif
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Project Hub</h2>
        </div>

        <nav class="sidebar-nav">
            <a class="sidebar-link active" data-view="board">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                <span>Board</span>
            </a>

            <a class="sidebar-link" data-view="table">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>Table</span>
            </a>

            <a class="sidebar-link" data-view="tasks">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Tasks</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-email">{{Auth::user()->email}}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button class="sidebar-toggle sidebar-open" id="sidebarToggle">
        <div class="burger-icon">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </div>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <h1 class="demo-header">Welcome to Your Workspace</h1>
        @livewire('views', ['board' => $board])
    </div>
    
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            sidebarToggle.classList.toggle('sidebar-open');
            mainContent.classList.toggle('expanded');
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('collapsed');
            sidebarToggle.classList.remove('sidebar-open');
            mainContent.classList.add('expanded');
        });

        // Select all sidebar links
        const sidebarLinks = document.querySelectorAll('.sidebar-link');

        sidebarLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // prevent default anchor behavior

                // Remove active from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));

                // Add active to the clicked link
                this.classList.add('active');

                // Emit Livewire event to change view
                const view = this.dataset.view;
                Livewire.dispatch('board-change', { viewName: view });
            });
        });

    </script>
    @livewireScripts
</body>

</html>