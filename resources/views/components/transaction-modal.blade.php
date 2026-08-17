{{-- Add Transaction Modal — matches DevOS modal styling --}}
<div id="transactionModalOverlay" class="do-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="transactionModalTitle">
    <div class="do-modal-wrapper tx-modal-wrapper" id="transactionModalWrapper">
        <div class="plan do-modal-card" role="document">
            <div class="inner do-modal-inner tx-modal-inner">
                <div class="do-modal-header">
                    <div>
                        <h2 class="title do-modal-title" id="transactionModalTitle">Add Transaction</h2>
                    </div>
                    <button type="button" class="do-modal-close" data-close-transaction-modal aria-label="Close modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <form id="transactionForm" class="do-modal-form">
                    @csrf

                    <div class="do-field-group">
                        <label class="do-field-label" for="txPartyName">Name</label>
                        <input type="text" id="txPartyName" name="party_name" class="do-field-input"
                            placeholder="Enter name" required maxlength="120" autocomplete="off" />
                    </div>

                    <div class="do-field-group">
                        <label class="do-field-label" for="txAmount">Amount</label>
                        <div class="tx-amount-wrap">
                            <span class="tx-amount-prefix">₹</span>
                            <input type="number" id="txAmount" name="amount" class="do-field-input tx-amount-input"
                                placeholder="0.00" min="0.01" step="0.01" required />
                        </div>
                    </div>

                    <div class="do-field-group">
                        <label class="do-field-label" for="txType">Type</label>
                        <select id="txType" name="type" class="do-field-select" required>
                            <option value="receivable" selected>Receivable</option>
                            <option value="payable">Payable</option>
                        </select>
                    </div>

                    <div class="do-field-group">
                        <label class="do-field-label" for="txCategory">Category</label>
                        <input type="text" id="txCategory" name="category" class="do-field-input"
                            placeholder="e.g. Client payment, Office rent" maxlength="120" autocomplete="off" />
                    </div>

                    <div class="do-field-group">
                        <label class="do-field-label" for="txDate">Date</label>
                        <input type="date" id="txDate" name="transaction_date" class="do-field-input" required />
                    </div>

                    <div class="do-field-group">
                        <label class="do-field-label" for="txNote">Note <span class="do-optional">(optional)</span></label>
                        <input type="text" id="txNote" name="note" class="do-field-input" placeholder="Add a note"
                            maxlength="1000" autocomplete="off" />
                    </div>

                    <button type="submit" class="do-btn-create tx-modal-save" id="txSubmitBtn">
                        Save Transaction
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
