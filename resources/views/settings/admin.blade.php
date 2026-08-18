@extends('layouts.app')

@section('header_title', 'Settings')
@section('header_subtitle', 'Manage your account and preferences.')

@section('content')

    {{-- Settings Tabs --}}
    <div class="mb-6">
        <div class="flex gap-1 border-b border-[var(--color-dp-border)]">
            <a href="{{ route('settings.profile') }}"
                class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
                Profile Settings
            </a>
            <a href="{{ route('settings.admin') }}"
                class="px-5 py-3 text-[14px] font-semibold border-b-2 border-[var(--color-dp-primary)] text-[var(--color-dp-primary)] -mb-px transition-all">
                Admin Settings
            </a>
            <a href="{{ route('settings.notifications') }}"
                class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
                Notification Settings
            </a>
            <a href="{{ route('settings.security') }}"
                class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
                Security Locker
            </a>
        </div>
    </div>

    {{-- Success / Error alerts --}}
    @if (session('success'))
        <div
            class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-2xl text-[14px] font-medium">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="premium-shell-banner premium-shell-banner--compact mb-6">
        <div>
            <p class="premium-shell-kicker">Settings</p>
            <h2 class="premium-shell-title">Account controls with cleaner context.</h2>
            <p class="premium-shell-copy">Credentials, SMTP, and preferences remain functionally the same while the admin page picks up the premium dashboard style.</p>
        </div>
    </div>

    {{-- Admin Settings Card --}}
    <div class="dp-card mb-6">
        {{-- Section Header --}}
        <div class="flex items-center gap-3 pb-5 mb-6 border-b border-[var(--color-dp-border)]">
            <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            <h2 class="text-[18px] font-bold text-[#1a1a24]">Current Access</h2>
        </div>

        {{-- Current Info Banner --}}
        <div
            class="bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-2xl px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-[var(--color-dp-primary)] flex items-center justify-center shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            <div>
                <p class="text-[13px] font-bold text-[#1a1a24]">Current Admin Username</p>
                <p class="text-[15px] font-bold text-[var(--color-dp-primary)]">{{ $admin->username }}</p>
            </div>
            <div class="ml-auto">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-[12px] font-bold">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Active
                </span>
            </div>
        </div>
    </div>

    {{-- Update Credentials Card --}}
    <div class="dp-card">
        {{-- Section Header --}}
        <div class="flex items-center gap-3 pb-5 mb-6 border-b border-[var(--color-dp-border)]">
            <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <h2 class="text-[18px] font-bold text-[#1a1a24]">Admin Credentials</h2>
        </div>

        <form id="adminCredentialsForm" action="{{ route('settings.admin.update') }}" method="POST" class="space-y-5" autocomplete="off">
            @csrf

            {{-- New Username --}}
            <div>
                <label
                    class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username', $admin->username) }}"
                    autocomplete="username"
                    class="w-full bg-[var(--color-dp-primary-light)] border {{ $errors->has('username') ? 'border-red-400' : 'border-[var(--color-dp-primary)]/20' }} rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all"
                    placeholder="Enter username">
                @error('username')
                    <p class="text-red-500 text-[12px] mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-[13px] text-[var(--color-dp-text-muted)] -mt-1">
                Current password is the one you use on Sign In. New password is the one you want to set. Leave new password empty to keep the same login password.
            </p>

            <div>
                <label
                    class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Current
                    Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" value=""
                        autocomplete="off"
                        class="w-full bg-[var(--color-dp-primary-light)] border {{ $errors->has('current_password') ? 'border-red-400' : 'border-[var(--color-dp-primary)]/20' }} rounded-xl px-4 pr-12 py-3 text-[14px] text-[#1a1a24] font-medium outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all"
                        placeholder="Enter your current Sign In password">
                    <button type="button" onclick="togglePass('current_password', 'eye1')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye1" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-red-500 text-[12px] mt-1.5 font-medium">{{ $message }}</p>
                @enderror
                <p id="currentPasswordClientError" class="text-red-500 text-[12px] mt-1.5 font-medium hidden"></p>
            </div>

            {{-- New Password --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">New
                        Password <span class="text-gray-400 font-normal">(leave empty to keep current)</span></label>
                    <div class="relative">
                        <input type="password" name="new_password" id="new_password" value=""
                            autocomplete="off"
                            class="w-full bg-[var(--color-dp-primary-light)] border {{ $errors->has('new_password') ? 'border-red-400' : 'border-[var(--color-dp-primary)]/20' }} rounded-xl px-4 pr-12 py-3 text-[14px] text-[#1a1a24] font-medium outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all"
                            placeholder="Type the new Sign In password">
                        <button type="button" onclick="togglePass('new_password', 'eye2')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eye2" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="text-red-500 text-[12px] mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                    <p id="newPasswordClientError" class="text-red-500 text-[12px] mt-1.5 font-medium hidden"></p>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Confirm
                        New Password</label>
                    <div class="relative">
                        <input type="password" name="new_password_confirmation" id="confirm_password" value=""
                            autocomplete="off"
                            class="w-full bg-[var(--color-dp-primary-light)] border {{ $errors->has('new_password') ? 'border-red-400' : 'border-[var(--color-dp-primary)]/20' }} rounded-xl px-4 pr-12 py-3 text-[14px] text-[#1a1a24] font-medium outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all"
                            placeholder="Confirm new password">
                        <button type="button" onclick="togglePass('confirm_password', 'eye3')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eye3" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    <p id="confirmPasswordClientError" class="text-red-500 text-[12px] mt-1.5 font-medium hidden"></p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 pt-6 border-t border-[var(--color-dp-border)]">
                <a href="{{ route('settings.admin') }}"
                    class="px-6 py-3 text-[14px] font-semibold text-[var(--color-dp-text-muted)] border border-[var(--color-dp-border)] rounded-xl hover:bg-gray-50 transition-all">
                    Reset
                </a>
                <button type="submit"
                    class="dp-btn-primary px-7 py-3 text-[14px] font-bold rounded-xl shadow-[0_4px_14px_0_rgba(92,65,201,0.30)] hover:shadow-[0_6px_20px_rgba(92,65,201,0.40)] hover:-translate-y-0.5 transition-all duration-300">
                    Update Credentials
                </button>
            </div>
        </form>
    </div>

    {{-- SMTP Settings --}}
    <div class="dp-card mt-6 relative overflow-hidden" data-smtp-root>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between pb-5 mb-6 border-b border-[var(--color-dp-border)]">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-[18px] font-bold text-[#1a1a24]">SMTP Settings</h2>
                    <p class="text-[13px] text-[var(--color-dp-text-muted)]">Configure email delivery settings for DevOS.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button type="button" id="smtpTestBtn"
                    class="dp-btn-primary px-5 py-2.5 text-[13px] font-bold rounded-xl shadow-[0_4px_14px_0_rgba(92,65,201,0.25)] hover:shadow-[0_6px_20px_rgba(92,65,201,0.35)] hover:-translate-y-0.5 transition-all duration-300 inline-flex items-center gap-2 disabled:opacity-60 disabled:hover:translate-y-0 disabled:cursor-not-allowed">
                    <span id="smtpTestSpinner" class="hidden inline-flex items-center">
                        <x-hourglass-loader size="xs" />
                    </span>
                    <span id="smtpTestBtnLabel">Test SMTP</span>
                </button>
                <div class="flex items-center gap-3 pl-1">
                    <span id="smtpStatusLabel" class="text-[13px] font-semibold {{ $smtp['configured'] ? 'text-[var(--color-dp-primary)]' : 'text-[var(--color-dp-text-muted)]' }}">
                        {{ $smtp['configured'] ? 'SMTP Ready' : 'SMTP Not Fully Configured' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">SMTP
                        Host</label>
                    <input type="text" value="{{ $smtp['host'] }}" readonly
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">SMTP
                        Port</label>
                    <input type="text" value="{{ $smtp['port'] }}" readonly
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">SMTP
                        Username</label>
                    <input type="text" value="" readonly placeholder="Configured securely"
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">SMTP
                        Password</label>
                    <input type="password" value="" readonly placeholder="Configured securely" autocomplete="off"
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Encryption</label>
                    <select disabled
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none appearance-none">
                        <option value="TLS" @selected($smtp['encryption'] === 'TLS')>TLS</option>
                        <option value="SSL" @selected($smtp['encryption'] === 'SSL')>SSL</option>
                        <option value="None" @selected($smtp['encryption'] === 'None')>None</option>
                    </select>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">From
                        Email</label>
                    <input type="email" value="{{ $smtp['from_address'] }}" readonly
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">From
                        Name</label>
                    <input type="text" value="{{ $smtp['from_name'] }}" readonly
                        class="w-full bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-xl px-4 py-3 text-[14px] text-[#1a1a24] font-medium outline-none">
                </div>
            </div>
        </div>
    </div>

    <div id="smtpTestEmailModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-smtp-modal-close></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 p-6 mx-4">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-[18px] font-bold text-[#1a1a24]">Send test email</h3>
                <button type="button" data-smtp-modal-close class="text-gray-400 hover:text-gray-600">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <p class="text-[13px] text-[var(--color-dp-text-muted)] mb-4">SMTP connection successful. Enter a recipient email to send a DevOS verification code preview.</p>
            <form id="smtpTestEmailForm" class="space-y-4">
                <div>
                    <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Recipient email</label>
                    <input type="email" id="smtpTestRecipient" required
                        placeholder="name@example.com"
                        class="w-full px-4 py-2.5 text-[14px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[var(--color-dp-primary)] focus:ring-1 focus:ring-[var(--color-dp-primary)] transition-all">
                    <p id="smtpTestRecipientError" class="text-red-500 text-[12px] mt-1.5 font-medium hidden"></p>
                </div>
                <div class="flex items-center gap-3 justify-end pt-2">
                    <button type="button" data-smtp-modal-close
                        class="px-5 py-2.5 text-[14px] font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Cancel</button>
                    <button type="submit" id="smtpSendTestBtn"
                        class="px-5 py-2.5 text-[14px] font-semibold text-white bg-[var(--color-dp-primary)] hover:bg-opacity-90 rounded-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center gap-2">
                        Send email
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Audit Log --}}
    <div class="dp-card mt-6">
        <div class="flex items-center justify-between gap-4 pb-5 mb-6 border-b border-[var(--color-dp-border)]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                        <path d="M14 3v5h5M16 13H8M16 17H8M10 9H8" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-[18px] font-bold text-[#1a1a24]">Audit Log</h2>
                    <p class="text-[13px] text-[var(--color-dp-text-muted)]">View login, logout, and failed attempt history in DevOS.</p>
                </div>
            </div>
            <a href="{{ route('audit-log.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-[13px] font-semibold text-white bg-[var(--color-dp-primary)] rounded-xl hover:bg-opacity-90 transition-all">
                Open Audit Log
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl border border-green-100">
            <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
            <p class="text-[13px] text-green-800 font-medium">Login audit logging is enabled. Events are saved automatically.</p>
        </div>
    </div>

    {{-- Backup & Recovery (Static) --}}
    <div class="dp-card mt-6 relative overflow-hidden">
        <div class="absolute top-4 right-5">
            <span
                class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider bg-orange-100 text-orange-600 rounded-lg">Coming
                Soon</span>
        </div>
        <div class="flex items-center gap-3 pb-5 mb-6 border-b border-[var(--color-dp-border)]">
            <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="9" ry="3" />
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                </svg>
            </div>
            <div>
                <h2 class="text-[18px] font-bold text-[#1a1a24]">Backup & Recovery</h2>
                <p class="text-[13px] text-[var(--color-dp-text-muted)]">Manage DevOS data backups and recovery options.
                </p>
            </div>
        </div>

        <div
            class="p-5 border border-[var(--color-dp-primary)]/20 bg-[var(--color-dp-primary-light)] rounded-2xl opacity-60 pointer-events-none">
            <h3 class="text-[15px] font-bold text-[#1a1a24] mb-1">Database Backup</h3>
            <p class="text-[13px] text-gray-600 mb-6">Keep a secure backup of your DevOS data.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Last
                        Backup</label>
                    <p class="text-[14px] font-medium text-gray-500">No backup available</p>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Backup
                        Frequency</label>
                    <select
                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-[#1a1a24] font-medium outline-none appearance-none">
                        <option>Daily</option>
                        <option>Weekly</option>
                        <option>Monthly</option>
                        <option>Manual</option>
                    </select>
                </div>
            </div>

            <div class="pt-5 border-t border-[var(--color-dp-primary)]/20">
                <button type="button"
                    class="bg-gray-800 text-white px-5 py-2.5 text-[14px] font-bold rounded-xl flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Backup Now
                </button>
                <p class="text-[12px] text-gray-500 mt-2">Backup functionality coming soon.</p>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            const icon = document.getElementById(iconId);
            icon.innerHTML = isPass ?
                '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>' :
                '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }

        function setClientError(el, message) {
            if (!el) return;
            if (message) {
                el.textContent = message;
                el.classList.remove('hidden');
            } else {
                el.textContent = '';
                el.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('adminCredentialsForm');
            const currentInput = document.getElementById('current_password');
            const newInput = document.getElementById('new_password');
            const confirmInput = document.getElementById('confirm_password');
            const currentErr = document.getElementById('currentPasswordClientError');
            const newErr = document.getElementById('newPasswordClientError');
            const confirmErr = document.getElementById('confirmPasswordClientError');

            const clearPasswordFields = () => {
                [currentInput, newInput, confirmInput].forEach((el) => {
                    if (el) {
                        el.value = '';
                        el.type = 'password';
                    }
                });
            };

            if ({{ session('alert') ? 'true' : 'false' }}) {
                clearPasswordFields();
                setTimeout(clearPasswordFields, 50);
                setTimeout(clearPasswordFields, 400);
            }

            form?.addEventListener('submit', (event) => {
                setClientError(currentErr, '');
                setClientError(newErr, '');
                setClientError(confirmErr, '');
                currentInput?.classList.remove('border-red-400');
                newInput?.classList.remove('border-red-400');
                confirmInput?.classList.remove('border-red-400');

                const currentPass = currentInput?.value || '';
                const newPass = newInput?.value || '';
                const confirmPass = confirmInput?.value || '';

                if (currentPass === '') {
                    event.preventDefault();
                    setClientError(currentErr, 'Enter the password you currently use to Sign In.');
                    currentInput?.classList.add('border-red-400');
                    return;
                }

                if (newPass === '' && confirmPass === '') {
                    return;
                }

                if (newPass.length < 6) {
                    event.preventDefault();
                    setClientError(newErr, 'New password must be at least 6 characters.');
                    newInput?.classList.add('border-red-400');
                    return;
                }

                if (newPass === currentPass) {
                    event.preventDefault();
                    setClientError(newErr, 'New password and current password match. Please choose a different password.');
                    newInput?.classList.add('border-red-400');
                    return;
                }

                if (newPass !== confirmPass) {
                    event.preventDefault();
                    setClientError(confirmErr, 'New password and confirm password do not match.');
                    confirmInput?.classList.add('border-red-400');
                    newInput?.classList.add('border-red-400');
                }
            });
        });

        (function initSmtpSettings() {
            const root = document.querySelector('[data-smtp-root]');
            if (!root) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const testBtn = document.getElementById('smtpTestBtn');
            const testLabel = document.getElementById('smtpTestBtnLabel');
            const testSpinner = document.getElementById('smtpTestSpinner');
            const statusLabel = document.getElementById('smtpStatusLabel');
            const modal = document.getElementById('smtpTestEmailModal');
            const form = document.getElementById('smtpTestEmailForm');
            const recipient = document.getElementById('smtpTestRecipient');
            const recipientError = document.getElementById('smtpTestRecipientError');
            const sendBtn = document.getElementById('smtpSendTestBtn');

            let testing = false;
            let sending = false;
            const alertOk = (title, description) => {
                if (window.DevOSAlert) window.DevOSAlert.success(title, description);
            };
            const alertErr = (title, description) => {
                if (window.DevOSAlert) window.DevOSAlert.error(title, description);
            };

            const jsonHeaders = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            };

            const parseJson = async (res) => {
                const data = await res.json().catch(() => ({}));
                return { status: res.status, body: data };
            };

            const setStatusUi = (configured) => {
                if (statusLabel) {
                    statusLabel.textContent = configured ? 'SMTP Ready' : 'SMTP Not Fully Configured';
                    statusLabel.classList.toggle('text-[var(--color-dp-primary)]', configured);
                    statusLabel.classList.toggle('text-[var(--color-dp-text-muted)]', !configured);
                }
            };

            const setTesting = (on) => {
                testing = on;
                if (testBtn) testBtn.disabled = on;
                testSpinner?.classList.toggle('hidden', !on);
                if (testLabel) testLabel.textContent = on ? 'Testing...' : 'Test SMTP';
            };

            const openModal = () => {
                recipientError?.classList.add('hidden');
                if (recipient) {
                    recipient.value = '';
                    recipient.classList.remove('border-red-400');
                }
                modal?.classList.remove('hidden');
                setTimeout(() => recipient?.focus(), 50);
            };

            const closeModal = () => {
                modal?.classList.add('hidden');
            };

            setStatusUi({{ $smtp['configured'] ? 'true' : 'false' }});

            testBtn?.addEventListener('click', async () => {
                if (testing) return;
                setTesting(true);
                try {
                    const res = await parseJson(await fetch('{{ route('settings.smtp.test') }}', {
                        method: 'POST',
                        headers: jsonHeaders,
                    }));
                    if (res.body.success) {
                        alertOk('SMTP connection successful', res.body.message || 'SMTP connection successful');
                        openModal();
                        return;
                    }
                    alertErr('SMTP test failed', res.body.message || 'SMTP connection failed.');
                } catch {
                    alertErr('SMTP test failed', 'Unable to test the SMTP connection.');
                } finally {
                    setTesting(false);
                }
            });

            modal?.querySelectorAll('[data-smtp-modal-close]').forEach((el) => {
                el.addEventListener('click', closeModal);
            });

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (sending) return;

                const email = (recipient?.value || '').trim();
                recipientError?.classList.add('hidden');
                recipient?.classList.remove('border-red-400');

                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    if (recipientError) {
                        recipientError.textContent = 'Enter a valid recipient email address.';
                        recipientError.classList.remove('hidden');
                    }
                    recipient?.classList.add('border-red-400');
                    return;
                }

                sending = true;
                if (sendBtn) {
                    sendBtn.disabled = true;
                    sendBtn.innerHTML = `${window.DevOSHourglass?.html('xs') || ''} Sending...`;
                }

                try {
                    const res = await parseJson(await fetch('{{ route('settings.smtp.test-email') }}', {
                        method: 'POST',
                        headers: jsonHeaders,
                        body: JSON.stringify({ email }),
                    }));
                    if (res.body.success) {
                        closeModal();
                        alertOk('done successfully :)', res.body.message || 'Test verification email sent successfully.');
                        return;
                    }
                    alertErr('Email not sent', res.body.message || 'Unable to send the test email.');
                    if (res.body.errors?.email?.[0] && recipientError) {
                        recipientError.textContent = res.body.errors.email[0];
                        recipientError.classList.remove('hidden');
                        recipient?.classList.add('border-red-400');
                    }
                } catch {
                    alertErr('Email not sent', 'Unable to send the test email.');
                } finally {
                    sending = false;
                    if (sendBtn) {
                        sendBtn.disabled = false;
                        sendBtn.textContent = 'Send email';
                    }
                }
            });
        })();
    </script>

@endsection
