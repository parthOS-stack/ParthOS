<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $navbarTitle = trim($__env->yieldContent('header_title'));
        if ($navbarTitle === '') {
            $navbarTitle = match (true) {
                request()->is('dashboard') => 'Dashboard',
                request()->is('task-daily') => 'DailyOps',
                request()->is('projects*') || request()->is('project-based*') => 'Projects',
                request()->is('transaction*') || request()->is('transactions*') => 'Transaction',
                request()->is('audit-log*') => 'Audit Log',
                request()->is('settings*') => 'Settings',
                request()->is('invoice*') => 'Invoices',
                request()->is('cards*') => 'Cards',
                default => 'DevOS',
            };
        }
    @endphp
    <title>{{ $navbarTitle }} · DevOS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback if Vite is not running -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script>
        (function () {
            try {
                if (window.matchMedia('(min-width: 1025px)').matches
                    && localStorage.getItem('devos_sidebar_collapsed') === '1') {
                    document.documentElement.classList.add('sidebar-collapsed-pending');
                }
            } catch (error) {
                // ignore
            }
        })();
    </script>
</head>
<body class="font-sans antialiased">
    <div class="dp-layout">
        <div class="dp-sidebar-overlay" id="sidebarOverlay"></div>
        @include('components.sidebar')

        <!-- Main Content Area -->
        <main class="dp-main">
            <!-- Header -->
            <header class="dp-header">
                <div class="dp-header-left">
                    <button type="button" id="navbarSidebarToggle" class="dp-header-menu" aria-label="Toggle menu" aria-expanded="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="4" y1="6" x2="20" y2="6" />
                            <line x1="4" y1="12" x2="20" y2="12" />
                            <line x1="4" y1="18" x2="20" y2="18" />
                        </svg>
                    </button>
                    <h1 class="dp-header-title">{{ $navbarTitle }}</h1>
                </div>
                <div class="dp-header-right">
                    <div class="dp-search">
                        <input type="text" name="navbar_search" class="dp-search-input" required placeholder="Type to search..." autocomplete="off" aria-label="Search">
                        <div class="dp-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"></path>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"></path>
                            </svg>
                        </div>
                    </div>
                    @php
                        $notifPrefs = app(\App\Services\NotificationSettingsService::class)->publicPrefs();
                    @endphp
                    <script>
                        window.DevOSNotificationPrefs = @json($notifPrefs);
                    </script>
                    @if ($notifPrefs['push_enabled'])
                        @include('components.notification-panel')
                    @endif
                    
                    <!-- Profile Dropdown -->
                    <div class="relative">
                        @php
                            $appAdmin = \Illuminate\Support\Facades\DB::table('admins')->first();
                            $adminAvatar = $appAdmin && $appAdmin->profile_photo ? asset('storage/' . $appAdmin->profile_photo) : 'https://api.dicebear.com/7.x/notionists/svg?seed=devos';
                        @endphp
                        <button id="profile-btn" class="w-[42px] h-[42px] rounded-full overflow-hidden border-2 border-[var(--color-dp-primary)] shadow-sm block focus:outline-none focus:ring-2 focus:ring-[var(--color-dp-primary)] focus:border-transparent transition-all bg-[#f3f1ff]">
                            <img src="{{ $adminAvatar }}" alt="Profile" class="w-full h-full object-cover" />
                        </button>
                        <!-- Active Dot -->
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full z-10 pointer-events-none shadow-sm"></div>

                        <!-- Dropdown Menu -->
                        <div id="profile-dropdown" class="hidden opacity-0 scale-95 origin-top-right absolute right-0 mt-3 w-[280px] bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] border border-gray-100 py-3 z-50 transition-all duration-200">
                            <!-- User Info -->
                            <div class="px-5 py-2 mb-2">
                                <p class="text-[15px] font-bold text-[#1a1a24] mb-0.5">{{ $appAdmin->full_name ?? 'DevOS Admin' }}</p>
                                <p class="text-[13px] text-gray-400">{{ $appAdmin->email ?? 'admin@devos.local' }}</p>
                            </div>

                            <a href="{{ route('settings.profile') }}" class="flex items-center gap-3.5 px-5 py-2.5 text-[14px] font-medium text-[#4a4a5a] hover:text-[var(--color-dp-primary)] hover:bg-gray-50 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                Profile & Password
                            </a>
                            <a href="{{ route('settings.notifications') }}" class="flex items-center gap-3.5 px-5 py-2.5 text-[14px] font-medium text-[#4a4a5a] hover:text-[var(--color-dp-primary)] hover:bg-gray-50 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                Notification
                            </a>
                            <a href="{{ route('settings.security') }}" class="flex items-center gap-3.5 px-5 py-2.5 text-[14px] font-medium text-[#4a4a5a] hover:text-[var(--color-dp-primary)] hover:bg-gray-50 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Security Locker
                            </a>
                            <a href="{{ route('audit-log.index') }}" class="flex items-center gap-3.5 px-5 py-2.5 text-[14px] font-medium text-[#4a4a5a] hover:text-[var(--color-dp-primary)] hover:bg-gray-50 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z" /><path d="M14 3v5h5M16 13H8M16 17H8M10 9H8" /></svg>
                                Audit Log
                            </a>

                            <!-- Appearance Toggle -->
                            <div class="flex items-center justify-between px-5 py-2.5 mt-1 mb-2">
                                <span class="text-[14px] font-medium text-[#4a4a5a]">Appearance</span>
                                <div class="relative inline-flex items-center w-[64px] h-[32px] rounded-full bg-gray-100 p-1 cursor-pointer">
                                    <div class="w-[24px] h-[24px] bg-white rounded-full shadow-sm flex items-center justify-center relative z-10">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a1a24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
                                    </div>
                                    <div class="absolute right-[6px] w-[24px] h-[24px] flex items-center justify-center text-gray-400">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pb-1">
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-[14px] font-bold text-red-600 bg-red-50 hover:bg-red-500 hover:text-white rounded-xl transition-all duration-300 border border-red-100 hover:border-red-600 shadow-sm">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="dp-content">
                @yield('content')
            </div>
        </main>
    </div>

    @include('components.alert-stack')

    <!-- Dropdown Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const profileBtn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            
            if (profileBtn && dropdown) {
                profileBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (dropdown.classList.contains('hidden')) {
                        dropdown.classList.remove('hidden');
                        setTimeout(() => {
                            dropdown.classList.remove('opacity-0', 'scale-95');
                        }, 10);
                    } else {
                        dropdown.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => {
                            dropdown.classList.add('hidden');
                        }, 200);
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!profileBtn.contains(e.target) && !dropdown.contains(e.target) && !dropdown.classList.contains('hidden')) {
                        dropdown.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => {
                            dropdown.classList.add('hidden');
                        }, 200);
                    }
                });
            }
        });
    </script>
</body>
</html>
