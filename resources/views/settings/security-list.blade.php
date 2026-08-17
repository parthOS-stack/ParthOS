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

{{-- Security Locker Card --}}
<div class="dp-card">
    {{-- Section Header --}}
    <div class="flex items-center justify-between pb-5 mb-6 border-b border-[var(--color-dp-border)]">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <h2 class="text-[18px] font-bold text-[#1a1a24]">Security Locker</h2>
                <p class="text-[13px] text-[var(--color-dp-text-muted)]">Securely store your saved logins and passwords.</p>
            </div>
        </div>
        <button onclick="openModal('add')" class="flex items-center gap-2 px-4 py-2 bg-[var(--color-dp-primary)] text-white text-[13px] font-semibold rounded-xl hover:bg-opacity-90 transition-all">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New
        </button>
    </div>

    {{-- Locker Entries --}}
    <div class="space-y-3">
        @forelse($credentials as $credential)
        <div class="group relative flex items-center justify-between px-5 py-4 bg-[var(--color-dp-bg)] border border-[var(--color-dp-border)] rounded-2xl hover:border-[var(--color-dp-primary)]/30 hover:bg-[var(--color-dp-primary-light)] transition-all duration-200">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-[14px] font-bold text-[#1a1a24]">{{ $credential->name }}</p>
                        @if($credential->is_pinned)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="text-amber-500"><path d="M16 3H8C6.9 3 6 3.9 6 5V13L4 16V17H11V22L12 23L13 22V17H20V16L18 13V5C18 3.9 17.1 3 16 3Z"/></svg>
                        @endif
                    </div>
                    <p class="text-[12px] text-[var(--color-dp-text-muted)]">{{ $credential->login_id }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="relative w-32 bg-gray-100 rounded-lg px-3 py-1 flex items-center justify-between">
                        <span class="text-[13px] font-medium tracking-widest text-gray-500 overflow-hidden whitespace-nowrap" id="pass-display-{{ $credential->id }}">••••••••••••</span>
                    </div>
                    <button type="button" onclick="togglePassword({{ $credential->id }}, this)" class="text-gray-400 hover:text-[var(--color-dp-primary)] transition-colors p-1" title="Show/Hide">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button type="button" onclick="copyPassword({{ $credential->id }}, this)" class="text-gray-400 hover:text-[var(--color-dp-primary)] transition-colors p-1" title="Copy">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </div>

                {{-- Kebab Menu --}}
                <div class="relative dropdown-container">
                    <button type="button" onclick="toggleDropdown({{ $credential->id }})" class="text-gray-400 hover:text-[#1a1a24] p-1 transition-colors">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div id="dropdown-{{ $credential->id }}" class="devos-action-menu dailyops-task-actions-menu hidden absolute right-0 mt-2 z-10">
                        <button type="button" onclick="openModal('edit', {{ $credential->toJson() }})" class="dailyops-task-action">Edit</button>
                        <button type="button" onclick="togglePin({{ $credential->id }})" class="dailyops-task-action">{{ $credential->is_pinned ? 'Unpin' : 'Pin' }}</button>
                        <div class="dailyops-action-divider"></div>
                        <button type="button" onclick="deleteCredential({{ $credential->id }})" class="dailyops-task-action dailyops-task-action-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="py-10 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <p class="text-[14px] font-semibold text-gray-700">No credentials found.</p>
            <p class="text-[13px] text-gray-400 mt-1">Click 'Add New' to store your first login.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Add/Edit Modal --}}
<div id="credential-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 p-6 transform transition-all scale-95 opacity-0" id="modal-content">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[18px] font-bold text-[#1a1a24]" id="modal-title">Add New Credential</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        
        <form id="credential-form" onsubmit="saveCredential(event)">
            <input type="hidden" id="credential-id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Name *</label>
                    <input type="text" id="credential-name" required class="w-full px-4 py-2.5 text-[14px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[var(--color-dp-primary)] focus:ring-1 focus:ring-[var(--color-dp-primary)] transition-all">
                </div>
                <div>
                    <label class="block text-[13px] font-bold text-gray-700 mb-1.5">ID / Email *</label>
                    <input type="text" id="credential-login" required class="w-full px-4 py-2.5 text-[14px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[var(--color-dp-primary)] focus:ring-1 focus:ring-[var(--color-dp-primary)] transition-all">
                </div>
                <div>
                    <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Password *</label>
                    <div class="relative">
                        <input type="password" id="credential-password" required class="w-full px-4 py-2.5 text-[14px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[var(--color-dp-primary)] focus:ring-1 focus:ring-[var(--color-dp-primary)] transition-all pr-10">
                        <button type="button" onclick="toggleModalPassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="modal-pass-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 justify-end mt-8">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 text-[14px] font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-[var(--color-dp-primary)] hover:bg-opacity-90 rounded-xl transition-all flex items-center gap-2">
                    <span id="save-btn-text">Save</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 p-6 transform transition-all scale-95 opacity-0" id="delete-modal-content">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <h3 class="text-[18px] font-bold text-center text-[#1a1a24] mb-2">Delete Credential?</h3>
        <p class="text-[14px] text-center text-gray-500 mb-6">Are you sure you want to delete this credential? This action cannot be undone.</p>
        <div class="flex items-center gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 text-[14px] font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Cancel</button>
            <button id="confirm-delete-btn" class="flex-1 px-4 py-2.5 text-[14px] font-semibold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-all">Delete</button>
        </div>
    </div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';
    let currentDeleteId = null;

    // Dropdown Logic
    function toggleDropdown(id) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            if (el.id !== `dropdown-${id}`) {
                el.classList.add('hidden');
                el.classList.remove('is-open');
                el.style.display = 'none';
            }
        });
        const menu = document.getElementById(`dropdown-${id}`);
        if (!menu) return;
        const willOpen = menu.classList.contains('hidden');
        menu.classList.toggle('hidden');
        menu.classList.toggle('is-open', willOpen);
        menu.style.display = willOpen ? 'flex' : 'none';
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown-container')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('is-open');
                el.style.display = 'none';
            });
        }
    });

    // Add/Edit Modal Logic
    function openModal(mode, data = null) {
        const modal = document.getElementById('credential-modal');
        const content = document.getElementById('modal-content');
        const form = document.getElementById('credential-form');
        
        form.reset();
        document.getElementById('credential-password').type = 'password';
        
        if(mode === 'edit' && data) {
            document.getElementById('modal-title').textContent = 'Edit Credential';
            document.getElementById('credential-id').value = data.id;
            document.getElementById('credential-name').value = data.name;
            document.getElementById('credential-login').value = data.login_id;
            // password is not prefilled for security, must enter new or we leave it blank?
            // Actually requirement says: "Populate: Name, ID/Email, Password". Wait, we don't have decrypted password in DOM.
            // Let's fetch it securely before opening modal.
            
            document.getElementById('save-btn-text').textContent = 'Update';
            
            fetch(`/settings/security-credentials/${data.id}/password`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    document.getElementById('credential-password').value = res.password;
                }
            });

        } else {
            document.getElementById('modal-title').textContent = 'Add New Credential';
            document.getElementById('credential-id').value = '';
            document.getElementById('save-btn-text').textContent = 'Save';
        }
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('credential-modal');
        const content = document.getElementById('modal-content');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    function toggleModalPassword() {
        const input = document.getElementById('credential-password');
        const icon = document.getElementById('modal-pass-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    // Delete Modal
    function deleteCredential(id) {
        currentDeleteId = id;
        const dd = document.getElementById('dropdown-' + id);
        if (dd) {
            dd.classList.add('hidden');
            dd.classList.remove('is-open');
            dd.style.display = 'none';
        }
        
        const modal = document.getElementById('delete-modal');
        const content = document.getElementById('delete-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeDeleteModal() {
        currentDeleteId = null;
        const modal = document.getElementById('delete-modal');
        const content = document.getElementById('delete-modal-content');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', () => {
        if(!currentDeleteId) return;
        
        fetch(`/settings/security-credentials/${currentDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(res => {
            if(res.success) {
                if (window.DevOSAlert) window.DevOSAlert.deleted('done successfully :)', 'Security entry deleted.');
                window.DevOSNotifications?.refresh();
                setTimeout(() => location.reload(), 400);
            }
        });
    });

    // Save Logic
    function saveCredential(e) {
        e.preventDefault();
        const id = document.getElementById('credential-id').value;
        const url = id ? `/settings/security-credentials/${id}` : '/settings/security-credentials';
        const method = id ? 'PUT' : 'POST';
        
        const payload = {
            name: document.getElementById('credential-name').value,
            login_id: document.getElementById('credential-login').value,
            password: document.getElementById('credential-password').value
        };

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(res => res.json()).then(res => {
            if(res.success) {
                if (window.DevOSAlert) {
                    window.DevOSAlert[id ? 'update' : 'success'](
                        'done successfully :)',
                        id ? 'Security entry updated.' : 'New security entry added.'
                    );
                }
                window.DevOSNotifications?.refresh();
                setTimeout(() => location.reload(), 400);
            }
        });
    }

    // Pin Logic
    function togglePin(id) {
        const dd = document.getElementById('dropdown-' + id);
        if (dd) {
            dd.classList.add('hidden');
            dd.classList.remove('is-open');
            dd.style.display = 'none';
        }
        fetch(`/settings/security-credentials/${id}/pin`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(res => res.json()).then(res => {
            if(res.success) location.reload();
        });
    }

    // Password View/Copy Logic
    let visiblePasswords = {};

    function togglePassword(id, btnIcon) {
        const display = document.getElementById('pass-display-' + id);
        
        if (visiblePasswords[id]) {
            // Hide
            display.textContent = '••••••••••••';
            display.classList.add('tracking-widest');
            display.classList.remove('tracking-normal', 'text-[#1a1a24]');
            btnIcon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
            visiblePasswords[id] = false;
        } else {
            // Fetch and Show
            fetch(`/settings/security-credentials/${id}/password`, {
                headers: { 'Accept': 'application/json' }
            }).then(res => res.json()).then(res => {
                if(res.success) {
                    display.textContent = res.password;
                    display.classList.remove('tracking-widest');
                    display.classList.add('tracking-normal', 'text-[#1a1a24]');
                    btnIcon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
                    visiblePasswords[id] = true;
                }
            });
        }
    }

    function copyPassword(id, btn) {
        fetch(`/settings/security-credentials/${id}/password`, {
            headers: { 'Accept': 'application/json' }
        }).then(res => res.json()).then(res => {
            if(res.success) {
                navigator.clipboard.writeText(res.password).then(() => {
                    const origHtml = btn.innerHTML;
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                    btn.classList.add('text-green-500');
                    
                    // Simple toast fallback if no global toast
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-5 right-5 bg-gray-900 text-white px-4 py-2 rounded-lg text-sm shadow-xl z-50 animate-fade-in-up';
                    toast.textContent = 'Password copied';
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        btn.innerHTML = origHtml;
                        btn.classList.remove('text-green-500');
                        toast.remove();
                    }, 2000);
                });
            }
        });
    }
</script>
<style>
    @keyframes fade-in-up {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.3s ease-out forwards;
    }
</style>

@endsection
