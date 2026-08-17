<aside class="dp-sidebar" id="devosSidebar">
    <div class="dp-sidebar-top">
        <a href="{{ url('/dashboard') }}" class="dp-sidebar-brand" aria-label="DevOS home">
            <div class="dp-sidebar-logo">
                <img src="{{ asset('devos_logo.png') }}" alt="" class="w-full h-full object-cover scale-[1.35]" />
            </div>
            <span class="dp-sidebar-brand-text">DevOS</span>
        </a>
    </div>

    <nav class="dp-sidebar-nav" aria-label="Main navigation">
        <a href="{{ url('/dashboard') }}" class="dp-nav-item {{ request()->is('dashboard') ? 'active' : '' }}" data-nav-label="Dashboard" title="Dashboard">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="14" width="7" height="7" rx="1" />
                <rect x="3" y="14" width="7" height="7" rx="1" />
            </svg>
            <span class="dp-nav-label">Dashboard</span>
        </a>
        <a href="{{ url('/task-daily') }}" class="dp-nav-item {{ request()->is('task-daily') ? 'active' : '' }}" data-nav-label="DailyOps" title="DailyOps">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span class="dp-nav-label">DailyOps</span>
        </a>
        <a href="{{ url('/projects') }}" class="dp-nav-item {{ request()->is('projects*') || request()->is('project-based') ? 'active' : '' }}" data-nav-label="Projects" title="Projects">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
            </svg>
            <span class="dp-nav-label">Projects</span>
        </a>
        <a href="{{ url('/transaction') }}" class="dp-nav-item {{ request()->is('transaction') ? 'active' : '' }}" data-nav-label="Transaction" title="Transaction">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
            <span class="dp-nav-label">Transaction</span>
        </a>
        <a href="{{ route('audit-log.index') }}" class="dp-nav-item {{ request()->is('audit-log*') ? 'active' : '' }}" data-nav-label="Audit Log" title="Audit Log">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                <path d="M14 3v5h5M16 13H8M16 17H8M10 9H8" />
            </svg>
            <span class="dp-nav-label">Audit Log</span>
        </a>
        <a href="{{ url('/settings') }}" class="dp-nav-item {{ request()->is('settings*') ? 'active' : '' }}" data-nav-label="Settings" title="Settings">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
            </svg>
            <span class="dp-nav-label">Settings</span>
        </a>
    </nav>

    <div class="dp-sidebar-footer">
        <div class="dp-logout-swipe" id="logoutSwipe">
            <form id="logoutSwipeForm" action="{{ route('logout') }}" method="POST" class="dp-logout-swipe-form">
                @csrf
            </form>
            <div class="dp-logout-swipe-shell">
                <div class="dp-logout-swipe-start" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                </div>
                <div class="dp-logout-swipe-track" id="logoutSwipeTrack">
                    <span class="dp-logout-swipe-hint">Swipe to logout</span>
                    <div class="dp-logout-swipe-fill" id="logoutSwipeFill"></div>
                    <button type="button" class="dp-logout-swipe-handle" id="logoutSwipeHandle" aria-label="Swipe right to log out">
                        <span class="dp-logout-swipe-arrow" aria-hidden="true">
                            <span class="dp-logout-swipe-arrow-head"></span>
                            <span class="dp-logout-swipe-wing dp-logout-swipe-wing--left"></span>
                            <span class="dp-logout-swipe-wing dp-logout-swipe-wing--right"></span>
                        </span>
                    </button>
                </div>
                <div class="dp-logout-swipe-end" aria-hidden="true"></div>
            </div>
        </div>
    </div>
</aside>
