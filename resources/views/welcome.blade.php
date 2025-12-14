<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TodoBido - Smart Task Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0d1117;
            color: #c9d1d9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background gradient */
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(88, 166, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 40% 60%, rgba(52, 211, 153, 0.1) 0%, transparent 50%);
            z-index: -1;
            animation: pulse 8s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            0% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 1.5rem 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(13, 17, 23, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(48, 54, 61, 0.5);
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #f0f6fc;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #58a6ff, #a371f7);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: white;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .nav-link.secondary {
            color: #c9d1d9;
            border: 1px solid transparent;
        }

        .nav-link.secondary:hover {
            color: #f0f6fc;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link.primary {
            background: linear-gradient(135deg, #58a6ff, #a371f7);
            color: white;
            border: none;
        }

        .nav-link.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(88, 166, 255, 0.3);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 8rem 2rem 4rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(88, 166, 255, 0.1);
            border: 1px solid rgba(88, 166, 255, 0.3);
            border-radius: 100px;
            font-size: 0.875rem;
            color: #58a6ff;
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease-out;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            color: #f0f6fc;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease-out 0.1s both;
        }

        .hero h1 .gradient-text {
            background: linear-gradient(135deg, #58a6ff, #a371f7, #3fb950);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.25rem;
            color: #8b949e;
            max-width: 600px;
            line-height: 1.7;
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            animation: fadeInUp 0.6s ease-out 0.3s both;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #58a6ff, #a371f7);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(88, 166, 255, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #c9d1d9;
            border: 1px solid #30363d;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #58a6ff;
            color: #f0f6fc;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .features-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #f0f6fc;
            margin-bottom: 1rem;
        }

        .features-header p {
            font-size: 1.1rem;
            color: #8b949e;
            max-width: 500px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: rgba(22, 27, 34, 0.8);
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: #58a6ff;
            box-shadow: 0 10px 40px rgba(88, 166, 255, 0.1);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 1.5rem;
        }

        .feature-icon.blue { background: rgba(88, 166, 255, 0.15); color: #58a6ff; }
        .feature-icon.purple { background: rgba(163, 113, 247, 0.15); color: #a371f7; }
        .feature-icon.green { background: rgba(63, 185, 80, 0.15); color: #3fb950; }
        .feature-icon.yellow { background: rgba(248, 184, 3, 0.15); color: #f8b803; }
        .feature-icon.red { background: rgba(248, 81, 73, 0.15); color: #f85149; }
        .feature-icon.teal { background: rgba(52, 211, 153, 0.15); color: #34d399; }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #f0f6fc;
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: #8b949e;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 3rem 2rem;
            border-top: 1px solid #21262d;
            color: #8b949e;
            font-size: 0.9rem;
        }

        footer a {
            color: #58a6ff;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav {
                padding: 1rem 1.5rem;
            }

            .hero-cta {
                flex-direction: column;
                width: 100%;
                max-width: 300px;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>

    <!-- Navigation -->
    <nav>
        <a href="/" class="logo">
            <div class="logo-icon">T</div>
            <span>TodoBido</span>
        </a>
        <div class="nav-links">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-link primary">Boards List</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link secondary">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link primary">Get Started</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-badge">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Smart Task Management
        </div>
        <h1>
            Organize Your Work,<br>
            <span class="gradient-text">Achieve More Together</span>
        </h1>
        <p>
            TodoBido helps teams and individuals manage tasks, track budgets, and collaborate seamlessly. 
            Stay organized with our intuitive Kanban boards and powerful analytics.
        </p>
        <div class="hero-cta">
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary">
                    Start Free Today
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    Sign In
                </a>
            @else
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                    Go to Boards List
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @endguest
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-header">
            <h2>Everything You Need</h2>
            <p>Powerful features to help you manage your work efficiently</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <h3>Kanban Boards</h3>
                <p>Visualize your workflow with drag-and-drop Kanban boards. Move tasks through stages effortlessly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3>Team Collaboration</h3>
                <p>Invite team members, assign tasks, and track everyone's progress in real-time.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon green">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Budget Tracking</h3>
                <p>Create business boards with budget categories and expense tracking to manage finances.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon yellow">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3>Analytics Dashboard</h3>
                <p>Get insights into your productivity with beautiful charts and progress tracking.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon red">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Due Date Reminders</h3>
                <p>Never miss a deadline with due date tracking and overdue task highlighting.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon teal">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3>Role-Based Access</h3>
                <p>Control who can do what with owner, admin, and member roles for each board.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} TodoBido. Built with Laravel & Livewire.</p>
    </footer>
</body>
</html>
