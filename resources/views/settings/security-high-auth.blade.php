@extends('layouts.app')

@section('header_title', 'Settings')
@section('header_subtitle', 'Manage your account and preferences.')

@section('content')

{{-- Settings Tabs --}}
<div class="mb-6">
    <div class="flex gap-1 border-b border-[var(--color-dp-border)]">
        <a href="{{ route('settings.profile') }}" class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">Profile Settings</a>
        <a href="{{ route('settings.admin') }}" class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">Admin Settings</a>
        <a href="{{ route('settings.notifications') }}" class="px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">Notification Settings</a>
        <a href="{{ route('settings.security') }}" class="px-5 py-3 text-[14px] font-semibold border-b-2 border-[var(--color-dp-primary)] text-[var(--color-dp-primary)] -mb-px transition-all">Security Locker</a>
    </div>
</div>

{{-- Back Button --}}
<div class="mb-5">
    <a href="{{ route('settings.security') }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[var(--color-dp-text-muted)] hover:text-[var(--color-dp-primary)] transition-colors group">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-0.5 transition-transform"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Security Locker
    </a>
</div>

{{-- Auth Card --}}
<div class="dp-card w-full py-16 text-center">
    
    <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-6">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>

    <h2 class="text-[22px] font-bold text-[#1a1a24] mb-2">High Security</h2>
    <p class="text-[14px] text-[var(--color-dp-text-muted)] mb-8">Enter your security password to continue.</p>

    <form id="high-security-auth-form" onsubmit="unlockHighSecurity(event)" class="max-w-xl mx-auto">
        <div class="mb-6 relative">
            <input type="password" id="high-security-password" placeholder="***************" required 
                class="w-full text-center px-4 py-4 text-[16px] tracking-widest font-medium bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-all placeholder:tracking-normal placeholder:font-normal">
            
            <p id="auth-error" class="absolute -bottom-6 left-0 right-0 text-[13px] font-semibold text-red-500 hidden opacity-0 transition-opacity">Incorrect security password.</p>
        </div>

        <button type="submit" id="unlock-btn" class="w-full py-3.5 text-[15px] font-bold text-white bg-gray-900 hover:bg-black rounded-xl transition-all flex items-center justify-center gap-2">
            Unlock
        </button>
    </form>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';

    function unlockHighSecurity(e) {
        e.preventDefault();
        const pwdInput = document.getElementById('high-security-password');
        const errorMsg = document.getElementById('auth-error');
        const btn = document.getElementById('unlock-btn');
        
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Unlocking...';
        btn.disabled = true;

        fetch('{{ route("settings.security.high.unlock") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ password: pwdInput.value })
        }).then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(res => {
            if (res.status === 200 && res.body.success) {
                if (window.DevOSAlert) window.DevOSAlert.success('done successfully :)', 'High security unlocked.');
                window.DevOSNotifications?.refresh();
                setTimeout(() => window.location.reload(), 350);
            } else {
                if (window.DevOSAlert) window.DevOSAlert.error('Access denied', res.body.message || 'Incorrect security password.');
                window.DevOSNotifications?.refresh();
                // Error
                errorMsg.classList.remove('hidden');
                errorMsg.classList.remove('opacity-0');
                pwdInput.classList.add('border-red-500');
                btn.innerHTML = originalText;
                btn.disabled = false;
                pwdInput.value = '';
                pwdInput.focus();
                
                setTimeout(() => {
                    errorMsg.classList.add('opacity-0');
                    pwdInput.classList.remove('border-red-500');
                    setTimeout(() => errorMsg.classList.add('hidden'), 200);
                }, 3000);
            }
        }).catch(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>

@endsection
