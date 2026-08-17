@extends('layouts.app')

@section('header_title', 'Settings')
@section('header_subtitle', 'Manage your account and preferences.')

@section('content')

{{-- Settings Tabs --}}
<div class="mb-6">
    <div class="flex gap-1 border-b border-[var(--color-dp-border)]">
        <a href="{{ route('settings.profile') }}"
            class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
            Profile Settings
        </a>
        <a href="{{ route('settings.admin') }}"
            class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
            Admin Settings
        </a>
        <a href="{{ route('settings.notifications') }}"
            class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-[var(--color-dp-primary)] text-[var(--color-dp-primary)] -mb-px transition-all">
            Notification Settings
        </a>
        <a href="{{ route('settings.security') }}"
            class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
            Security Locker
        </a>
    </div>
</div>

{{-- Notification Settings Card --}}
<div class="dp-card">
    <div class="flex items-center gap-3 pb-5 mb-6 border-b border-[var(--color-dp-border)]">
        <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <h2 class="text-[18px] font-bold text-[#1a1a24]">Notification Settings</h2>
    </div>

    <div class="space-y-0 divide-y divide-[var(--color-dp-border)]">

        {{-- Push Notifications --}}
        <div class="flex items-center justify-between py-5">
            <div class="flex-1 pr-8">
                <p class="text-[15px] font-bold text-[#1a1a24] mb-1">Push Notifications</p>
                <p class="text-[13px] text-[var(--color-dp-text-muted)]">Show the header bell for in-app alerts (login, tasks, profile)</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" class="sr-only peer" id="push-toggle" data-notif-key="push" @checked($notifPrefs['push_enabled'])>
                <div class="w-[52px] h-[28px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[24px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-[24px] after:w-[24px] after:transition-all peer-checked:bg-[var(--color-dp-primary)] shadow-inner"></div>
            </label>
        </div>

        {{-- Email Notifications --}}
        <div class="flex items-center justify-between py-5">
            <div class="flex-1 pr-8">
                <p class="text-[15px] font-bold text-[#1a1a24] mb-1">Email Notifications</p>
                <p class="text-[13px] text-[var(--color-dp-text-muted)]">Security only — send OTP emails for forgot password. SMTP must also be enabled.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" class="sr-only peer" id="email-toggle" data-notif-key="email" @checked($notifPrefs['email_enabled'])>
                <div class="w-[52px] h-[28px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[24px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-[24px] after:w-[24px] after:transition-all peer-checked:bg-[var(--color-dp-primary)] shadow-inner"></div>
            </label>
        </div>

        {{-- App Sounds --}}
        <div class="py-5">
            <div class="flex items-center justify-between">
                <div class="flex-1 pr-8">
                    <p class="text-[15px] font-bold text-[#1a1a24] mb-1">App Sounds</p>
                    <p class="text-[13px] text-[var(--color-dp-text-muted)]">Play a ping for new bell alerts and success / error toasts</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" class="sr-only peer" id="sounds-toggle" data-notif-key="sounds" @checked($notifPrefs['sounds_enabled'])>
                    <div class="w-[52px] h-[28px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[24px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-[24px] after:w-[24px] after:transition-all peer-checked:bg-[var(--color-dp-primary)] shadow-inner"></div>
                </label>
            </div>

            <div id="sound-upload-area" class="{{ $notifPrefs['sounds_enabled'] ? '' : 'hidden' }} mt-4">
                <div class="bg-[var(--color-dp-primary-light)] border border-[var(--color-dp-primary)]/20 rounded-2xl p-5">
                    <label class="block text-[11px] font-bold text-[var(--color-dp-text-muted)] uppercase tracking-wider mb-2">Custom notification sound</label>
                    <p class="text-[13px] text-[var(--color-dp-text-muted)] mb-3">MP3, WAV, OGG, or M4A — max 2 MB. This file plays only while App Sounds is enabled.</p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="file" id="sound-file" accept=".mp3,.wav,.ogg,.m4a,audio/mpeg,audio/wav,audio/ogg,audio/mp4"
                            class="flex-1 text-[13px] file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-[var(--color-dp-primary)] file:text-white file:text-[13px] file:font-semibold file:cursor-pointer bg-white border border-[var(--color-dp-primary)]/20 rounded-xl px-3 py-1.5">
                        <button type="button" id="sound-upload-btn"
                            class="px-5 py-2.5 text-[14px] font-bold text-white bg-[var(--color-dp-primary)] rounded-xl hover:bg-opacity-90 transition-all shrink-0">
                            Upload
                        </button>
                    </div>

                    <div id="sound-current" class="{{ $notifPrefs['sound_url'] ? 'flex' : 'hidden' }} items-center justify-between gap-3 mt-4 bg-white rounded-xl px-4 py-3 border border-[var(--color-dp-primary)]/15">
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-[#1a1a24] truncate" id="sound-file-name">{{ $notifPrefs['sound_name'] ?? 'Custom sound' }}</p>
                            <p class="text-[12px] text-[var(--color-dp-text-muted)]">Used for bell pings and success / error toasts</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" id="sound-preview-btn"
                                class="px-3 py-2 text-[13px] font-semibold text-[var(--color-dp-primary)] bg-[var(--color-dp-primary-light)] rounded-xl hover:bg-opacity-80">
                                Preview
                            </button>
                            <button type="button" id="sound-delete-btn"
                                class="px-3 py-2 text-[13px] font-semibold text-red-600 bg-red-50 rounded-xl hover:bg-red-100">
                                Remove
                            </button>
                        </div>
                    </div>

                    <p id="sound-empty-hint" class="{{ $notifPrefs['sound_url'] ? 'hidden' : '' }} mt-3 text-[13px] text-[var(--color-dp-text-muted)]">
                        No custom file yet. A default ping plays until you upload one.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const soundArea = document.getElementById('sound-upload-area');
        const soundCurrent = document.getElementById('sound-current');
        const soundName = document.getElementById('sound-file-name');
        const soundEmpty = document.getElementById('sound-empty-hint');

        function applyPrefs(prefs) {
            window.DevOSNotificationPrefs = prefs;
            window.DevOSSounds?.setPrefs(prefs);
            if (soundArea) soundArea.classList.toggle('hidden', !prefs.sounds_enabled);
            if (soundCurrent) {
                soundCurrent.classList.toggle('hidden', !prefs.sound_url);
                soundCurrent.classList.toggle('flex', !!prefs.sound_url);
            }
            if (soundName && prefs.sound_name) soundName.textContent = prefs.sound_name;
            if (soundEmpty) soundEmpty.classList.toggle('hidden', !!prefs.sound_url);
        }

        async function toggleSetting(key, enabled, checkbox) {
            try {
                const response = await fetch('{{ route("settings.notifications.toggle") }}', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ key, enabled }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    checkbox.checked = !enabled;
                    window.DevOSAlert?.error('Could not save', data.message || 'Please try again.');
                    return;
                }
                applyPrefs(data.prefs);
                window.DevOSAlert?.success('done successfully :)', data.message);
                if (key === 'push') {
                    window.location.reload();
                }
            } catch (error) {
                checkbox.checked = !enabled;
                window.DevOSAlert?.error('Could not save', 'Please try again.');
            }
        }

        document.querySelectorAll('[data-notif-key]').forEach((input) => {
            input.addEventListener('change', () => {
                toggleSetting(input.dataset.notifKey, input.checked, input);
            });
        });

        document.getElementById('sound-upload-btn')?.addEventListener('click', async () => {
            const fileInput = document.getElementById('sound-file');
            if (!fileInput?.files?.length) {
                window.DevOSAlert?.error('Choose a file', 'Select an audio file first.');
                return;
            }
            const form = new FormData();
            form.append('sound', fileInput.files[0]);
            try {
                const response = await fetch('{{ route("settings.notifications.sound") }}', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: form,
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    window.DevOSAlert?.error('Upload failed', data.message || 'Please try again.');
                    return;
                }
                fileInput.value = '';
                applyPrefs(data.prefs);
                window.DevOSAlert?.success('done successfully :)', data.message);
            } catch (error) {
                window.DevOSAlert?.error('Upload failed', 'Please try again.');
            }
        });

        document.getElementById('sound-preview-btn')?.addEventListener('click', () => {
            window.DevOSSounds?.preview();
        });

        document.getElementById('sound-delete-btn')?.addEventListener('click', async () => {
            try {
                const response = await fetch('{{ route("settings.notifications.sound.delete") }}', {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    window.DevOSAlert?.error('Could not remove', data.message || 'Please try again.');
                    return;
                }
                applyPrefs(data.prefs);
                window.DevOSAlert?.success('done successfully :)', data.message);
            } catch (error) {
                window.DevOSAlert?.error('Could not remove', 'Please try again.');
            }
        });
    });
</script>

@endsection
