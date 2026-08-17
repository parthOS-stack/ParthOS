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

    <div class="tx-summary-grid">
        <div class="tx-summary-card">
            <p class="tx-summary-label">YOU'LL RECEIVE</p>
            <p class="tx-summary-value tx-summary-value--green" id="txSummaryReceivable">₹0</p>
        </div>
        <div class="tx-summary-card">
            <p class="tx-summary-label">YOU'LL PAY</p>
            <p class="tx-summary-value tx-summary-value--red" id="txSummaryPayable">₹0</p>
        </div>
        <div class="tx-summary-card">
            <p class="tx-summary-label">NET BALANCE</p>
            <p class="tx-summary-value tx-summary-value--green" id="txSummaryNet">₹0</p>
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
            <p class="font-semibold text-[#1a1a24] mb-1" style="font-size:15px;">No transactions yet</p>
            <p class="text-[var(--color-dp-text-muted)]" style="font-size:13px;">Add your first receivable or payable entry to start tracking money.</p>
        </div>
    </div>
</div>

@include('components.transaction-modal')
@endsection
