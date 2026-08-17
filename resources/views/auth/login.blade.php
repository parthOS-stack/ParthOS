<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DevOS — Login</title>

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
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        /* Fix for browser autofill background in dark mode */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #13131a inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#0f0f13] min-h-screen flex items-center justify-center p-6 relative">

    <!-- Dark Grid Background -->
    <div
        class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:40px_40px]">
    </div>

    <!-- Subtle glow behind the card -->
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-[var(--color-dp-primary)] rounded-full blur-[120px] opacity-20 pointer-events-none">
    </div>

    <div
        class="w-full max-w-[440px] p-6 sm:p-10 relative overflow-hidden rounded-3xl bg-white/5 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)] text-white">

        <div class="relative z-10 text-center mb-10">
            <div class="relative w-16 h-16 mx-auto mb-8 group">
                <!-- Glowing backdrop animation -->
                <div
                    class="absolute inset-0 bg-blue-500 rounded-xl blur-xl opacity-30 group-hover:opacity-60 transition-opacity duration-700 animate-pulse">
                </div>

                <!-- Logo container -->
                <div
                    class="relative w-full h-full flex items-center justify-center rounded-xl overflow-hidden bg-[#0f0f13] border border-blue-500/30 shadow-[0_0_20px_rgba(59,130,246,0.4)] transition-all duration-500 group-hover:scale-105 group-hover:shadow-[0_0_30px_rgba(59,130,246,0.6)] group-hover:-translate-y-1">
                    <img src="{{ asset('devos_logo.png') }}" alt="Logo"
                        class="w-full h-full object-cover scale-[1.35] transition-transform duration-700 group-hover:scale-[1.45]" />
                </div>
            </div>
            <h2 class="text-3xl font-bold text-white mb-5 tracking-tight">Welcome to DevOS</h2>
            <p class="text-[14px] text-gray-300 leading-relaxed max-w-[300px] mx-auto">Your personal workspace for
                tasks, projects, and everything in between.</p>
        </div>

        @php
            $lockout = $loginLockout ?? session('login_lockout');
        @endphp

        @if (!empty($lockout['is_locked']))
            <div class="relative z-10 mb-5 rounded-2xl border border-red-500/40 bg-red-500/10 px-4 py-4 text-left">
                <p class="text-[14px] font-bold text-red-200">Account blocked for 24 hours</p>
                <p class="mt-1 text-[13px] leading-relaxed text-red-100/90">
                    6 failed login attempts thai gayaa che. Aa account
                    @if (!empty($lockout['locked_until_human']))
                        <strong>{{ $lockout['locked_until_human'] }}</strong> sudhi
                    @else
                        24 hours sudhi
                    @endif
                    blocked che.
                </p>
                <a href="{{ route('password.forgot') }}"
                    class="mt-3 inline-flex items-center gap-2 text-[13px] font-semibold text-white underline decoration-red-300/70 underline-offset-2 hover:text-red-100">
                    Reset Password
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        @elseif (!empty($lockout['show_reset']))
            <div class="relative z-10 mb-5 rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-4 text-left">
                <p class="text-[14px] font-bold text-amber-100">Too many failed attempts</p>
                <p class="mt-1 text-[13px] leading-relaxed text-amber-50/90">
                    {{ $lockout['attempts_remaining'] ?? 0 }} attempt(s) baki che, pachhi account 24 hours mate block thai jashe.
                </p>
                <a href="{{ route('password.forgot') }}"
                    class="mt-3 inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-dp-primary)] px-4 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#4b33a8]">
                    Reset Password
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        @endif

        <form action="{{ url('/secure-access') }}" method="POST" class="relative z-10 flex flex-col gap-5">
            @csrf

            <div>
                <label for="username" class="block text-[13px] font-semibold text-gray-300 mb-2">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                    class="w-full bg-[#13131a] border border-white/10 rounded-xl px-4 py-3.5 text-[14px] text-white outline-none focus:border-blue-500/50 focus:bg-[#1a1a24] focus:ring-1 focus:ring-blue-500/50 transition-all placeholder-gray-600 @if(!empty($lockout['is_locked'])) opacity-60 @endif"
                    placeholder="Enter your username" required autocomplete="off"
                    @if (!empty($lockout['is_locked'])) readonly @endif>
            </div>

            <div>
                <label for="password" class="block text-[13px] font-semibold text-gray-300 mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                        class="w-full bg-[#13131a] border border-white/10 rounded-xl pl-4 pr-12 py-3.5 text-[14px] text-white outline-none focus:border-blue-500/50 focus:bg-[#1a1a24] focus:ring-1 focus:ring-blue-500/50 transition-all placeholder-gray-600 @if(!empty($lockout['is_locked'])) opacity-60 @endif"
                        placeholder="••••••••" required autocomplete="off"
                        @if (!empty($lockout['is_locked'])) disabled @endif>
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-gray-300 transition-colors">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between mt-1 gap-3 flex-wrap">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="remember"
                        class="w-4 h-4 rounded border-white/20 bg-white/5 text-[var(--color-dp-primary)] focus:ring-[var(--color-dp-primary)] focus:ring-offset-[#0f0f13] transition-colors"
                        @if (!empty($lockout['is_locked'])) disabled @endif>
                    <span
                        class="text-[13px] font-medium text-gray-400 group-hover:text-gray-200 transition-colors">Remember
                        me</span>
                </label>
                @if (empty($lockout['show_reset']) && empty($lockout['is_locked']))
                    <a href="{{ route('password.forgot') }}"
                        class="text-[13px] font-semibold text-[var(--color-dp-primary)] hover:text-white transition-colors">Forgot
                        password?</a>
                @endif
            </div>

            <button type="submit" id="submitBtn"
                @if (!empty($lockout['is_locked'])) disabled @endif
                class="relative group w-full mx-auto mt-2 h-[52px] flex items-center justify-center bg-[var(--color-dp-primary)] hover:bg-[#4b33a8] text-white font-semibold rounded-xl transition-all duration-500 overflow-hidden shadow-[0_4px_14px_0_rgba(92,65,201,0.39)] hover:shadow-[0_6px_20px_rgba(92,65,201,0.23)] hover:-translate-y-0.5 active:translate-y-0 @if(!empty($lockout['is_locked'])) opacity-50 cursor-not-allowed hover:translate-y-0 hover:bg-[var(--color-dp-primary)] @endif">
                
                <!-- Text -->
                <span id="btnText" class="flex items-center gap-2 transition-all duration-300">
                    Sign In
                    <svg class="w-4 h-4 text-white/70 group-hover:text-white group-hover:translate-x-1.5 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </span>

                <!-- Loader -->
                <span id="btnLoader" class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300">
                    <x-hourglass-loader size="sm" />
                </span>
            </button>
        </form>

    </div>

    <script>
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                const btn = document.getElementById('submitBtn');
                const btnText = document.getElementById('btnText');
                const btnLoader = document.getElementById('btnLoader');
                
                if(btn && btnText && btnLoader) {
                    // Transform button to a circle
                    btn.style.width = '52px';
                    btn.style.borderRadius = '50%';
                    btn.classList.add('pointer-events-none');
                    
                    // Hide text
                    btnText.style.opacity = '0';
                    btnText.style.transform = 'scale(0.8)';
                    
                    // Show spinner
                    btnLoader.style.opacity = '1';
                }
            });
        }

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    eyeIcon.classList.add('hidden');
                    eyeOffIcon.classList.remove('hidden');
                } else {
                    eyeIcon.classList.remove('hidden');
                    eyeOffIcon.classList.add('hidden');
                }
            });
        }
    </script>

    @include('components.alert-stack')
    @if ($errors->any())
        <div id="devosAlertFlash"
             data-type="error"
             data-title="Login failed"
             data-description="{{ $errors->first() }}"
             hidden></div>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.DevOSAlert) window.DevOSAlert.init();
        });
    </script>
</body>
</html>
