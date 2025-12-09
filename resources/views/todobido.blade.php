<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/todo.css', 'resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Todobido</title>

    @livewireStyles

    <style>
        /* Dark Modern Modal Styles */
        .user-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .user-modal.active {
            display: flex;
        }

        .user-modal-content {
            background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 16px;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .user-modal-header {
            padding: 24px 28px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .user-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg,
                    transparent 0%,
                    rgba(59, 130, 246, 0.5) 50%,
                    transparent 100%);
        }

        .user-modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.01em;
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-modal-close {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 20px;
            cursor: pointer;
            color: #94a3b8;
            padding: 6px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .user-modal-close:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }

        .user-modal-body {
            padding: 28px;
        }

        .modal-user-info {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .modal-user-info:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .modal-user-avatar {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .modal-user-avatar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg,
                    transparent 30%,
                    rgba(255, 255, 255, 0.1) 50%,
                    transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .modal-user-details h4 {
            margin: 0 0 6px 0;
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            letter-spacing: -0.01em;
        }

        .modal-user-details p {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 400;
        }

        .modal-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .modal-link {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            text-decoration: none;
            color: #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .modal-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.05);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-link:hover {
            transform: translateX(4px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .modal-link:hover::before {
            opacity: 1;
        }

        .modal-link.dashboard {
            background: rgba(30, 64, 175, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .modal-link.dashboard:hover {
            background: rgba(30, 64, 175, 0.25);
            border-color: rgba(59, 130, 246, 0.4);
            color: #93c5fd;
        }

        .modal-link.profile {
            background: rgba(6, 95, 70, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .modal-link.profile:hover {
            background: rgba(6, 95, 70, 0.25);
            border-color: rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
        }

        .modal-link.logout {
            background: rgba(153, 27, 27, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .modal-link.logout:hover {
            background: rgba(153, 27, 27, 0.25);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            transform: translateX(4px) scale(1.02);
        }

        .modal-divider {
            height: 1px;
            background: linear-gradient(90deg,
                    transparent 0%,
                    rgba(255, 255, 255, 0.1) 50%,
                    transparent 100%);
            margin: 20px 0;
            position: relative;
        }

        .modal-divider::after {
            content: '';
            position: absolute;
            top: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 4px;
            background: linear-gradient(90deg,
                    rgba(99, 102, 241, 0) 0%,
                    #6366f1 50%,
                    rgba(99, 102, 241, 0) 100%);
            border-radius: 2px;
        }

        .user-info-clickable {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 10px;
            padding: 10px 14px;
            border: 1px solid transparent;
            background: rgba(255, 255, 255, 0.03);
        }

        .user-info-clickable:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .user-info-clickable .user-name {
            color: #ffffff;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: -0.01em;
        }

        .user-info-clickable .user-email {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 400;
        }

        /* Add subtle pulse animation to modal on open */
        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            }

            50% {
                box-shadow: 0 20px 60px rgba(99, 102, 241, 0.3);
            }
        }

        .user-modal-content {
            animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                pulseGlow 2s ease-in-out 0.4s 1;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .user-modal-content {
                width: 95%;
                max-width: 380px;
                margin: 20px;
            }

            .user-modal-header,
            .user-modal-body {
                padding: 20px;
            }

            .modal-user-info {
                flex-direction: column;
                text-align: center;
                padding: 24px 20px;
            }

            .modal-user-avatar {
                width: 72px;
                height: 72px;
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    @if(session('success'))
        <div id="toast-success" class="toast toast-success">
            <div class="toast-content">
                <span class="toast-icon">✓</span>
                <span class="toast-message">{{ session('success') }}</span>
                <button onclick="this.closest('.toast').remove()" class="toast-close">&times;</button>
            </div>
            <div class="toast-timer"></div>
        </div>
    @endif

    @if(session('error'))
        <div id="toast-error" class="toast toast-error">
            <div class="toast-content">
                <span class="toast-icon">✕</span>
                <span class="toast-message">{{ session('error') }}</span>
                <button onclick="this.closest('.toast').remove()" class="toast-close">&times;</button>
            </div>
            <div class="toast-timer"></div>
        </div>
    @endif

    <!-- User Modal -->
    <div class="user-modal" id="userModal">
        <div class="user-modal-content">
            <div class="user-modal-header">
                <h3>Account Settings</h3>
                <button type="button" class="user-modal-close" id="closeModal">&times;</button>
            </div>
            <div class="user-modal-body">
                <div class="modal-user-info">
                    <div class="modal-user-avatar">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="modal-user-details">
                        <h4>{{ Auth::user()->name }}</h4>
                        <p>{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="modal-divider"></div>
                <div class="modal-links">
                    <a href="{{ route('dashboard') }}" class="modal-link dashboard">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="modal-link profile">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Profile') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <a href="{{ route('logout') }}" class="modal-link logout"
                            onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ __('Log Out') }}
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Todobido</h2>
        </div>

        <nav class="sidebar-nav">
            @if($board->list_type === 'Business')
                {{-- Business Board Navigation --}}
                <a class="sidebar-link active" data-view="budget">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Budget</span>
                </a>

                <a class="sidebar-link" data-view="table">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Table</span>
                </a>

                <a class="sidebar-link" data-view="members">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Members List</span>
                </a>

                <a class="sidebar-link" data-view="logs">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Activity Log</span>
                </a>
                <a class="sidebar-link" data-view="dashboard">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            @else
                {{-- Normal Board Navigation --}}
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

                <a class="sidebar-link" data-view="members">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Members List</span>
                </a>

                <a class="sidebar-link" data-view="logs">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Activity Log</span>
                </a>
                <a class="sidebar-link" data-view="dashboard">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-info user-info-clickable" id="userInfo">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-email">{{ Auth::user()->email }}</div>
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
        <h1 class="demo-header">{{ $board->title }}</h1>
        @livewire('views', ['board' => $board])
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        const userInfo = document.getElementById('userInfo');
        const userModal = document.getElementById('userModal');
        const closeModal = document.getElementById('closeModal');

        // Toggle sidebar
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
                e.preventDefault();

                const view = this.dataset.view;

                // Check if this link is already active
                if (this.classList.contains('active')) {
                    console.log('Same view clicked, ignoring');
                    return;
                }

                // Remove active from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));

                // Add active to the clicked link
                this.classList.add('active');

                // Emit Livewire event to change view
                Livewire.dispatch('board-change', { viewName: view });
            });
        });

        // Open user modal with animation
        userInfo.addEventListener('click', () => {
            userModal.classList.add('active');
            // Add body class to prevent scrolling
            document.body.style.overflow = 'hidden';
        });

        // Close user modal
        closeModal.addEventListener('click', () => {
            closeModal.style.transform = 'rotate(45deg)';
            setTimeout(() => {
                userModal.classList.remove('active');
                document.body.style.overflow = '';
            }, 200);
        });

        // Close modal when clicking outside
        userModal.addEventListener('click', (e) => {
            if (e.target === userModal) {
                userModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && userModal.classList.contains('active')) {
                userModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toast').forEach(toast => {
                setTimeout(() => toast.remove(), 5000);
            });
        });
    </script>
    @livewireScripts
</body>

</html>