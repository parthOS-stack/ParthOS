@extends('layouts.app')

@section('header_title', 'Audit Log')
@section('header_subtitle', 'Login, logout, and failed attempt history.')

@section('content')
    <div class="premium-shell-banner premium-shell-banner--compact mb-6">
        <div>
            <p class="premium-shell-kicker">Audit Log</p>
            <h2 class="premium-shell-title">Security events at a glance.</h2>
            <p class="premium-shell-copy">Recent access activity stays server-rendered, with only a visual refresh around the existing filters and records.</p>
        </div>
    </div>

    <div class="audit-stats-grid grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="dp-card !p-5">
            <p class="text-[12px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">Total events</p>
            <p class="mt-2 text-[28px] font-bold text-[#1a1a24]">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="dp-card !p-5">
            <p class="text-[12px] font-bold uppercase tracking-wider text-green-600">Success today</p>
            <p class="mt-2 text-[28px] font-bold text-green-700">{{ number_format($stats['success_today']) }}</p>
        </div>
        <div class="dp-card !p-5">
            <p class="text-[12px] font-bold uppercase tracking-wider text-red-500">Failed today</p>
            <p class="mt-2 text-[28px] font-bold text-red-600">{{ number_format($stats['failed_today']) }}</p>
        </div>
        <div class="dp-card !p-5">
            <p class="text-[12px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">Logout today</p>
            <p class="mt-2 text-[28px] font-bold text-[#1a1a24]">{{ number_format($stats['logout_today']) }}</p>
        </div>
    </div>

    <div class="dp-card">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 mb-6 border-b border-[var(--color-dp-border)]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[var(--color-dp-primary-light)] flex items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dp-primary)"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                        <path d="M14 3v5h5M16 13H8M16 17H8M10 9H8" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-[18px] font-bold text-[#1a1a24]">Login audit log</h2>
                    <p class="text-[13px] text-[var(--color-dp-text-muted)]">Stored in DevOS — no phpMyAdmin needed.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('audit-log.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="q" value="{{ $filters['q'] }}"
                    placeholder="Search username or IP"
                    class="w-full sm:w-[220px] px-4 py-2.5 text-[14px] border border-[var(--color-dp-border)] rounded-xl outline-none focus:border-[var(--color-dp-primary)]">
                <select name="status"
                    class="px-4 py-2.5 text-[14px] border border-[var(--color-dp-border)] rounded-xl outline-none focus:border-[var(--color-dp-primary)] bg-white">
                    <option value="">All statuses</option>
                    <option value="success" @selected($filters['status'] === 'success')>Success</option>
                    <option value="failed" @selected($filters['status'] === 'failed')>Failed</option>
                    <option value="logout" @selected($filters['status'] === 'logout')>Logout</option>
                </select>
                <button type="submit"
                    class="px-5 py-2.5 text-[14px] font-semibold text-white bg-[var(--color-dp-primary)] rounded-xl hover:bg-opacity-90 transition-all">
                    Filter
                </button>
                @if ($filters['q'] !== '' || $filters['status'] !== '')
                    <a href="{{ route('audit-log.index') }}"
                        class="px-5 py-2.5 text-[14px] font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all text-center">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead>
                    <tr class="border-b border-[var(--color-dp-border)]">
                        <th class="pb-3 pr-4 text-[11px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">Date & time</th>
                        <th class="pb-3 pr-4 text-[11px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">Username</th>
                        <th class="pb-3 pr-4 text-[11px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">Status</th>
                        <th class="pb-3 pr-4 text-[11px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">IP address</th>
                        <th class="pb-3 text-[11px] font-bold uppercase tracking-wider text-[var(--color-dp-text-muted)]">Browser / device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-[var(--color-dp-border)]/70 last:border-0">
                            <td class="py-4 pr-4 text-[14px] text-[#1a1a24] whitespace-nowrap">
                                {{ $log->created_at?->format('M j, Y g:i A') }}
                            </td>
                            <td class="py-4 pr-4 text-[14px] font-semibold text-[#1a1a24]">
                                {{ $log->username }}
                            </td>
                            <td class="py-4 pr-4">
                                @if ($log->status === 'success')
                                    <span class="inline-flex px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide rounded-lg bg-green-100 text-green-700">Success</span>
                                @elseif ($log->status === 'failed')
                                    <span class="inline-flex px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide rounded-lg bg-red-100 text-red-700">Failed</span>
                                @elseif ($log->status === 'logout')
                                    <span class="inline-flex px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide rounded-lg bg-gray-100 text-gray-700">Logout</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide rounded-lg bg-orange-100 text-orange-700">{{ $log->status }}</span>
                                @endif
                            </td>
                            <td class="py-4 pr-4 text-[14px] text-[var(--color-dp-text-muted)] font-mono">
                                {{ $log->ip_address ?: '—' }}
                            </td>
                            <td class="py-4 text-[13px] text-[var(--color-dp-text-muted)] max-w-[320px] truncate" title="{{ $log->user_agent }}">
                                {{ $log->browser_label }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-[14px] text-[var(--color-dp-text-muted)]">
                                No audit events yet. Login activity will appear here automatically.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="mt-6 pt-4 border-t border-[var(--color-dp-border)]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
