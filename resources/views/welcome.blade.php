@extends('layouts.app')

@section('content')
@php($data = $dashboardData)
<div class="dashboard-premium" data-dashboard-root>
    <section class="dashboard-hero">
        <div class="dashboard-wallet-card dp-card">
            <div class="dashboard-hero-copy">
                <h1 class="dashboard-hero-title">Today’s live snapshot</h1>
                <p class="dashboard-hero-subtitle">Clean overview of money, task completion, and project movement.</p>

                <div class="tx-wallet-metrics dashboard-hero-metrics">
                    <div class="tx-wallet-metric">
                        <span class="tx-wallet-metric-label">Receivable</span>
                        <span class="tx-wallet-metric-value tx-summary-value--green">₹{{ number_format($data['wallet']['receivable'], 0) }}</span>
                    </div>
                    <div class="tx-wallet-metric">
                        <span class="tx-wallet-metric-label">Payable</span>
                        <span class="tx-wallet-metric-value tx-summary-value--red">₹{{ number_format($data['wallet']['payable'], 0) }}</span>
                    </div>
                    <div class="tx-wallet-metric">
                        <span class="tx-wallet-metric-label">Net</span>
                        <span class="tx-wallet-metric-value">₹{{ number_format($data['wallet']['net'], 0) }}</span>
                    </div>
                    <div class="tx-wallet-metric">
                        <span class="tx-wallet-metric-label">Completion</span>
                        <span class="tx-wallet-metric-value">{{ $data['task_progress']['percent'] }}%</span>
                    </div>
                </div>

                <div class="dashboard-hero-footer">
                    <a href="{{ route('settings.security') }}" class="dashboard-security-shortcut">
                        <div class="dashboard-security-top">
                            <span class="dashboard-security-kicker">Quick Access</span>
                            <span class="dashboard-security-arrow">Open Locker <span aria-hidden="true">→</span></span>
                        </div>
                        <strong>Security Locker</strong>
                        <p>Open protected credentials and sensitive saved items.</p>
                    </a>

                    <div class="dashboard-hero-mini-stats">
                        <div class="dashboard-hero-mini-stat dashboard-locker-stat">
                            <span>Security Locker</span>
                            <strong>{{ $data['security']['locker_count'] }}</strong>
                        </div>
                        <button type="button" class="dashboard-hero-mini-stat dashboard-privacy-stat" data-privacy-card aria-label="Reveal high security count">
                            <span>High Security</span>
                            <strong class="dashboard-privacy-count">{{ $data['security']['high_security_count'] }}</strong>
                            <span class="dashboard-privacy-eye" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <line x1="3" y1="3" x2="21" y2="21"></line>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-clock-card dp-card">
            <div class="dashboard-clock-top">
                <div>
                    <p class="dashboard-clock-kicker">Right now</p>
                    <div class="dashboard-clock-day" data-dashboard-day>{{ $data['clock']['day'] }}</div>
                    <div class="dashboard-clock-date" data-dashboard-date>{{ $data['clock']['date'] }}</div>
                </div>
                <div class="dashboard-clock-time" data-dashboard-time>{{ $data['clock']['time'] }}</div>
            </div>

            <div class="realistic-clock dashboard-realistic-clock">
                <div class="clock-face">
                    <div class="glass-cover"></div>
                    <div class="hour hand" data-dashboard-hour-hand></div>
                    <div class="minute hand" data-dashboard-minute-hand></div>
                    <div class="second hand" data-dashboard-second-hand></div>
                    <div class="center-circle"></div>
                    <div class="clock-numbers">
                        <p style="top: 12px; left: 50%; transform: translateX(-50%);" class="number">12</p>
                        <p style="top: 50%; right: 16px; transform: translateY(-50%);" class="number">3</p>
                        <p style="bottom: 12px; left: 50%; transform: translateX(-50%);" class="number">6</p>
                        <p style="top: 50%; left: 16px; transform: translateY(-50%);" class="number">9</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-project-mini">
                <div class="dashboard-project-mini-item">
                    <span>Completed</span>
                    <strong>{{ $data['project_overview']['completed'] }}</strong>
                </div>
                <div class="dashboard-project-mini-item">
                    <span>In Progress</span>
                    <strong>{{ $data['project_overview']['in_progress'] }}</strong>
                </div>
                <div class="dashboard-project-mini-item">
                    <span>Not Started</span>
                    <strong>{{ $data['project_overview']['not_started'] }}</strong>
                </div>
            </div>

            <a href="{{ route('docs.index') }}" class="project-back-btn dashboard-docs-btn">
                <span class="project-back-btn-slide dashboard-docs-btn-slide">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" height="14" width="14" aria-hidden="true">
                        <path d="M160 480h640a32 32 0 1 1 0 64H160a32 32 0 0 1 0-64z" fill="currentColor"></path>
                        <path d="m786.752 512-265.408 265.344a32 32 0 0 0 45.312 45.312l288-288a32 32 0 0 0 0-45.312l-288-288a32 32 0 1 0-45.312 45.312L786.752 512z" fill="currentColor"></path>
                    </svg>
                </span>
                <span class="project-back-btn-label dashboard-docs-btn-label">Read Documentation</span>
            </a>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="dashboard-panel dp-card">
            <div class="dashboard-panel-head">
                <div>
                    <h2>Task Progress</h2>
                    <p>Real completion from your current DailyOps data.</p>
                </div>
                <span class="dashboard-badge">This week</span>
            </div>
            <div class="dashboard-metric-row">
                <div class="dashboard-metric-large">{{ $data['task_progress']['completed'] }}<span>/{{ max($data['task_progress']['total'], 1) }}</span></div>
                <div class="dashboard-metric-side">
                    <strong>{{ $data['task_progress']['weekly_completed'] }}</strong>
                    <span>completed this week</span>
                </div>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: {{ $data['task_progress']['percent'] }}%"></div>
            </div>
        </div>

        <div class="dashboard-panel dp-card dashboard-velocity">
            <div class="dashboard-panel-head">
                <div>
                    <h2>Team Velocity</h2>
                    <p>{{ $data['velocity']['headline'] }}</p>
                </div>
            </div>
            <div class="dashboard-velocity-score">{{ $data['velocity']['score'] }}%</div>
            <div class="dashboard-chip-row">
                @foreach ($data['velocity']['tags'] as $tag)
                    <span class="dashboard-chip">{{ $tag }}</span>
                @endforeach
            </div>
        </div>

        <div class="dashboard-panel dp-card dashboard-wallet-only-card">
            <div class="wallet dashboard-wallet dashboard-wallet-inline">
                <div class="wallet-back"></div>
                @foreach ($data['wallet']['cards'] as $card)
                    <div class="card {{ $card['variant'] }}">
                        <div class="card-inner">
                            <div class="card-top">
                                <span>{{ $card['brand'] }}</span>
                                <div class="chip"></div>
                            </div>
                            <div class="card-bottom">
                                <div class="card-info">
                                    <span class="label">{{ $card['label'] }}</span>
                                    <span class="value">{{ $card['value'] }}</span>
                                </div>
                                <div class="card-number-wrapper">
                                    <span class="hidden-stars">{{ $card['masked'] }}</span>
                                    <span class="card-number">{{ $card['visible'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="pocket">
                    <svg class="pocket-svg" viewBox="0 0 280 160" fill="none">
                        <path d="M 0 20 C 0 10, 5 10, 10 10 C 20 10, 25 25, 40 25 L 240 25 C 255 25, 260 10, 270 10 C 275 10, 280 10, 280 20 L 280 120 C 280 155, 260 160, 240 160 L 40 160 C 20 160, 0 155, 0 120 Z" fill="#1e341e"></path>
                        <path d="M 8 22 C 8 16, 12 16, 15 16 C 23 16, 27 29, 40 29 L 240 29 C 253 29, 257 16, 265 16 C 268 16, 272 16, 272 22 L 272 120 C 272 150, 255 152, 240 152 L 40 152 C 25 152, 8 152, 8 120 Z" stroke="#3d5635" stroke-width="1.5" stroke-dasharray="6 4"></path>
                    </svg>
                    <div class="pocket-content">
                        <div class="tx-balance-wrap">
                            <div class="balance-stars">******</div>
                            <div class="balance-real">₹{{ number_format($data['wallet']['net'], 0) }}</div>
                        </div>
                        <div class="tx-balance-label">{{ $data['wallet']['balance_label'] }}</div>
                        <div class="eye-icon-wrapper">
                            <svg class="eye-icon eye-slash" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                                <line x1="3" y1="3" x2="21" y2="21"></line>
                            </svg>
                            <svg class="eye-icon eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-panel dp-card">
            <div class="dashboard-panel-head">
                <div>
                    <h2>Focus Reminders</h2>
                    <p>Tasks pinned for follow-through.</p>
                </div>
            </div>
            <div class="dashboard-list">
                @forelse ($data['reminders'] as $reminder)
                    <div class="dashboard-list-item {{ $reminder['active'] ? 'is-active' : '' }}">
                        <div>
                            <strong>{{ $reminder['title'] }}</strong>
                            <span>{{ $reminder['subtitle'] }}</span>
                        </div>
                        <span class="dashboard-dot"></span>
                    </div>
                @empty
                    <p class="dashboard-empty-copy">No focus reminders yet.</p>
                @endforelse
            </div>
        </div>

        <div class="dashboard-panel dp-card dashboard-panel-wide">
            <div class="dashboard-panel-head">
                <div>
                    <p class="tx-wallet-kicker">Dashboard</p>
                    <h2>Today's Schedule</h2>
                    <p>Upcoming due tasks and live planning for the day ahead.</p>
                </div>
            </div>
            <div class="dashboard-schedule">
                @forelse ($data['schedule'] as $item)
                    <div class="dashboard-schedule-card {{ $item['accent'] === 'primary' ? 'is-primary' : '' }}">
                        <span class="dashboard-schedule-badge">{{ $item['badge'] }}</span>
                        <h3>{{ $item['title'] }}</h3>
                        <div class="dashboard-schedule-time">{{ $item['time'] }}</div>
                        <p>{{ $item['caption'] }}</p>
                    </div>
                @empty
                    <p class="dashboard-empty-copy">No scheduled tasks yet. Add due dates in DailyOps to see them here.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
