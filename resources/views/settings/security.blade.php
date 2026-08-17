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
            class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
            Admin Settings
        </a>
        <a href="{{ route('settings.notifications') }}"
            class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
            Notification Settings
        </a>
        <a href="{{ route('settings.security') }}"
            class="px-5 py-3 text-[14px] font-semibold border-b-2 border-[var(--color-dp-primary)] text-[var(--color-dp-primary)] -mb-px transition-all">
            Security Locker
        </a>
    </div>
</div>

{{-- Two Row List --}}
<div class="dp-card p-0 overflow-hidden divide-y divide-[var(--color-dp-border)]">

    {{-- Row 1: Security --}}
    <a href="{{ route('settings.security.list') }}"
        class="group flex items-center justify-between px-6 py-5 hover:bg-[var(--color-dp-primary-light)] transition-all duration-200">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-[var(--color-dp-primary-light)] group-hover:bg-white/70 flex items-center justify-center shrink-0 transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div>
                <p class="text-[15px] font-bold text-[#1a1a24] group-hover:text-[var(--color-dp-primary)] transition-colors">Security</p>
                <p class="text-[13px] text-[var(--color-dp-text-muted)] mt-0.5">Securely store your saved logins and passwords</p>
            </div>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-text-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="shrink-0 group-hover:stroke-[var(--color-dp-primary)] group-hover:translate-x-0.5 transition-all">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
    </a>

    {{-- Row 2: High Security --}}
    <a href="{{ route('settings.security.high') }}"
        class="group flex items-center justify-between px-6 py-5 hover:bg-[var(--color-dp-primary-light)] transition-all duration-200">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-50 group-hover:bg-white/70 flex items-center justify-center shrink-0 transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <p class="text-[15px] font-bold text-[#1a1a24] group-hover:text-[var(--color-dp-primary)] transition-colors">High Security</p>
                <p class="text-[13px] text-[var(--color-dp-text-muted)] mt-0.5">Advanced protection for critical credentials and keys</p>
            </div>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-text-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="shrink-0 group-hover:stroke-[var(--color-dp-primary)] group-hover:translate-x-0.5 transition-all">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
    </a>

</div>

@endsection
