@extends('layouts.app')

@section('compact_header', true)

@section('content')
<div class="transactions-page" data-transactions-root>

    <div class="tx-page-header">
        <div>
            <h1 class="tx-page-title">Transactions</h1>
            <p class="tx-page-subtitle">Track money you're owed and money spent</p>
        </div>
        <button type="button" id="btnOpenAddTransaction" class="dailyops-add-task" aria-label="Add transaction">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Add Transaction
        </button>
    </div>

    <div class="tx-wallet-shell dp-card">
        <div class="tx-wallet-copy">
            <p class="tx-wallet-kicker">Premium Overview</p>
            <h2 class="tx-wallet-title">Transaction Wallet</h2>
            <p class="tx-wallet-subtitle">See receivables, payouts, and your net position in one premium snapshot.</p>

            <div class="tx-wallet-metrics">
                <div class="tx-wallet-metric">
                    <span class="tx-wallet-metric-label">You'll receive</span>
                    <span class="tx-wallet-metric-value tx-summary-value--green" id="txSummaryReceivable">₹0</span>
                </div>
                <div class="tx-wallet-metric">
                    <span class="tx-wallet-metric-label">You'll pay</span>
                    <span class="tx-wallet-metric-value tx-summary-value--red" id="txSummaryPayable">₹0</span>
                </div>
                <div class="tx-wallet-metric">
                    <span class="tx-wallet-metric-label">Total entries</span>
                    <span class="tx-wallet-metric-value" id="txSummaryCount">0</span>
                </div>
            </div>
        </div>

        <div class="tx-wallet-stage">
            <div class="wallet" id="txWallet">
                <div class="wallet-back"></div>

                <div class="card stripe">
                    <div class="card-inner">
                        <div class="card-top">
                            <span id="txHeroBrand1">Receivable</span>
                            <div class="chip"></div>
                        </div>
                        <div class="card-bottom">
                            <div class="card-info">
                                <span class="label" id="txHeroLabel1">Top client</span>
                                <span class="value" id="txHeroValue1">No receivables yet</span>
                            </div>
                            <div class="card-number-wrapper">
                                <span class="hidden-stars" id="txHeroMasked1">RCV •••• 0000</span>
                                <span class="card-number" id="txHeroVisible1">RCV 0000 0000</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card wise">
                    <div class="card-inner">
                        <div class="card-top">
                            <span id="txHeroBrand2">Payable</span>
                            <div class="chip"></div>
                        </div>
                        <div class="card-bottom">
                            <div class="card-info">
                                <span class="label" id="txHeroLabel2">Top payout</span>
                                <span class="value" id="txHeroValue2">No payables yet</span>
                            </div>
                            <div class="card-number-wrapper">
                                <span class="hidden-stars" id="txHeroMasked2">PAY •••• 0000</span>
                                <span class="card-number" id="txHeroVisible2">PAY 0000 0000</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card paypal">
                    <div class="card-inner">
                        <div class="card-top">
                            <span id="txHeroBrand3">Recent</span>
                            <div class="chip"></div>
                        </div>
                        <div class="card-bottom">
                            <div class="card-info">
                                <span class="label" id="txHeroLabel3">Latest entry</span>
                                <span class="value" id="txHeroValue3">No transactions yet</span>
                            </div>
                            <div class="card-number-wrapper">
                                <span class="hidden-stars" id="txHeroMasked3">TX •••• 0000</span>
                                <span class="card-number" id="txHeroVisible3">TX 0000 0000</span>
                            </div>
                        </div>
                        <div class="card-meta" id="txHeroMeta3" hidden>
                            <span><em>Amount</em> <strong id="txHeroAmount3">—</strong></span>
                            <span><em>Type</em> <strong id="txHeroType3">—</strong></span>
                            <span><em>Date</em> <strong id="txHeroDate3">—</strong></span>
                        </div>
                    </div>
                </div>

                <div class="pocket">
                    <svg class="pocket-svg" viewBox="0 0 280 160" fill="none">
                        <path d="M 0 20 C 0 10, 5 10, 10 10 C 20 10, 25 25, 40 25 L 240 25 C 255 25, 260 10, 270 10 C 275 10, 280 10, 280 20 L 280 120 C 280 155, 260 160, 240 160 L 40 160 C 20 160, 0 155, 0 120 Z" fill="#1e341e"></path>
                        <path d="M 8 22 C 8 16, 12 16, 15 16 C 23 16, 27 29, 40 29 L 240 29 C 253 29, 257 16, 265 16 C 268 16, 272 16, 272 22 L 272 120 C 272 150, 255 152, 240 152 L 40 152 C 25 152, 8 152, 8 120 Z" stroke="#3d5635" stroke-width="1.5" stroke-dasharray="6 4"></path>
                    </svg>
                    <div class="pocket-content">
                        <div class="tx-balance-wrap">
                            <div class="balance-stars">******</div>
                            <div class="balance-real" id="txSummaryNet">₹0</div>
                        </div>
                        <div class="tx-balance-label" id="txSummaryNetLabel">Net Balance</div>
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
    </div>

    <div class="mb-6">
        <div class="flex gap-1 border-b border-[var(--color-dp-border)]" role="tablist" aria-label="Transaction filters">
            <button type="button" class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-[var(--color-dp-primary)] text-[var(--color-dp-primary)] -mb-px transition-all" data-tx-filter="all" role="tab" aria-selected="true">
                All
            </button>
            <button type="button" class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all" data-tx-filter="receivable" role="tab" aria-selected="false">
                Receivable
            </button>
            <button type="button" class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all" data-tx-filter="payable" role="tab" aria-selected="false">
                Payable
            </button>
        </div>
    </div>

    <div id="txLoadingState" class="dp-card flex items-center justify-center" style="min-height: 260px;">
        <div class="text-center">
            <x-hourglass-loader />
            <p class="text-[var(--color-dp-text-muted)] mt-4" style="font-size:14px; font-weight: 500;">Loading transactions...</p>
        </div>
    </div>

    <div id="txTableWrap" class="dailyops-task-container" style="display: none;">
        <div class="dailyops-task-table-wrap">
            <table class="dailyops-task-table tx-task-table">
                <thead>
                    <tr>
                        <th class="do-col-task">ID</th>
                        <th class="do-col-title">Name</th>
                        <th class="do-col-project">Type</th>
                        <th class="do-col-status">Amount</th>
                        <th class="do-col-priority">Date</th>
                    </tr>
                </thead>
                <tbody id="txList"></tbody>
            </table>
        </div>
    </div>

    <div id="txEmptyState" class="dailyops-empty-state dp-card items-center justify-center" style="min-height: 260px; display: none;">
        <div class="text-center">
            <div style="width:56px;height:56px;background:#f3f1ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6558d3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
            </div>
            <p class="font-semibold text-[var(--color-dp-text-main)] mb-1" style="font-size:15px;">No transactions yet</p>
            <p class="text-[var(--color-dp-text-muted)]" style="font-size:13px;">Add your first receivable or payable entry to start tracking money.</p>
        </div>
    </div>
</div>

@include('components.transaction-modal')
@endsection
