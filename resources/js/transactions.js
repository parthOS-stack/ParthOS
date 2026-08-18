/**
 * DevOS Transactions page
 */

const Transactions = (() => {
    const state = {
        filter: 'all',
        items: [],
        summary: { receivable: 0, payable: 0, net: 0 },
        hero: { cards: [], balance_label: 'Net Balance' },
        loading: false,
        initialized: false,
    };

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function root() {
        return document.querySelector('[data-transactions-root]');
    }

    function formatMoney(value) {
        const amount = Number(value || 0);
        return '₹' + amount.toLocaleString('en-IN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setLoading(isLoading) {
        state.loading = isLoading;
        const loading = document.getElementById('txLoadingState');
        const table = document.getElementById('txTableWrap');
        const empty = document.getElementById('txEmptyState');
        if (loading) loading.style.display = isLoading ? 'flex' : 'none';
        if (!isLoading && table) table.style.display = state.items.length ? 'block' : 'none';
        if (!isLoading && empty) empty.style.display = state.items.length ? 'none' : 'flex';
    }

    function updateSummary() {
        const { receivable, payable, net } = state.summary;
        const receivableEl = document.getElementById('txSummaryReceivable');
        const payableEl = document.getElementById('txSummaryPayable');
        const netEl = document.getElementById('txSummaryNet');
        const countEl = document.getElementById('txSummaryCount');
        const netLabelEl = document.getElementById('txSummaryNetLabel');

        if (receivableEl) receivableEl.textContent = formatMoney(receivable);
        if (payableEl) payableEl.textContent = formatMoney(payable);
        if (countEl) countEl.textContent = String(state.summary.total_transactions || state.items.length || 0);
        if (netEl) {
            netEl.textContent = formatMoney(net);
            netEl.classList.toggle('tx-summary-value--green', net >= 0);
            netEl.classList.toggle('tx-summary-value--red', net < 0);
        }
        if (netLabelEl) netLabelEl.textContent = state.hero.balance_label || 'Net Balance';
    }

    function updateHero() {
        const cards = Array.isArray(state.hero.cards) ? state.hero.cards : [];

        cards.slice(0, 3).forEach((card, index) => {
            const pos = index + 1;
            const brand = document.getElementById(`txHeroBrand${pos}`);
            const label = document.getElementById(`txHeroLabel${pos}`);
            const value = document.getElementById(`txHeroValue${pos}`);
            const masked = document.getElementById(`txHeroMasked${pos}`);
            const visible = document.getElementById(`txHeroVisible${pos}`);

            if (brand) brand.textContent = card.brand || '';
            if (label) label.textContent = card.label || '';
            if (value) value.textContent = card.value || '';
            if (masked) masked.textContent = card.masked || '';
            if (visible) visible.textContent = card.visible || '';
        });

        const recent = cards[2] || {};
        const meta = document.getElementById('txHeroMeta3');
        const amount = document.getElementById('txHeroAmount3');
        const type = document.getElementById('txHeroType3');
        const date = document.getElementById('txHeroDate3');
        const hasRecent = Boolean(recent.amount) || Boolean(recent.type);

        if (amount) amount.textContent = recent.amount_label && recent.amount_label !== 'No amount'
            ? recent.amount_label
            : (recent.amount ? formatMoney(recent.amount) : '—');
        if (type) type.textContent = recent.type_label || '—';
        if (date) date.textContent = recent.date_label || '—';
        if (meta) meta.hidden = !hasRecent;
    }

    function renderList() {
        const list = document.getElementById('txList');
        if (!list) return;

        list.innerHTML = state.items.map((item) => {
            const isReceivable = item.type === 'receivable';
            const amountPrefix = isReceivable ? '+' : '-';
            const amountClass = isReceivable ? 'tx-amount-positive' : 'tx-amount-negative';
            const typeLabel = isReceivable ? 'Receivable' : 'Payable';
            const typeClass = isReceivable ? 'is-project' : 'is-personal';
            const idLabel = item.key || ('TX-' + String(item.id).padStart(4, '0'));
            const category = item.category || item.note || '';

            return `
                <tr class="dailyops-task-row">
                    <td class="do-col-task">
                        <span class="dailyops-task-id">${escapeHtml(idLabel)}</span>
                    </td>
                    <td class="do-col-title">
                        <div class="dailyops-title-wrap">
                            <span class="dailyops-task-title-text" title="${escapeHtml(item.party_name)}">${escapeHtml(item.party_name)}</span>
                            ${category
                                ? `<span class="dailyops-task-desc-text" title="${escapeHtml(category)}">${escapeHtml(category)}</span>`
                                : ''}
                        </div>
                    </td>
                    <td class="do-col-project">
                        <span class="dailyops-project-pill ${typeClass}">${typeLabel}</span>
                    </td>
                    <td class="do-col-status">
                        <span class="tx-amount-cell ${amountClass}">${amountPrefix} ${formatMoney(item.amount)}</span>
                    </td>
                    <td class="do-col-priority">
                        <span class="dailyops-task-id">${escapeHtml(item.transaction_date_label || '')}</span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadTransactions() {
        if (!root()) return;
        setLoading(true);

        try {
            const response = await fetch(`/transactions/data?filter=${encodeURIComponent(state.filter)}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Failed to load transactions');
            const data = await response.json();
            state.items = data.transactions || [];
            state.summary = data.summary || { receivable: 0, payable: 0, net: 0 };
            state.hero = data.hero || { cards: [], balance_label: 'Net Balance' };
            updateSummary();
            updateHero();
            renderList();
        } catch (error) {
            console.error(error);
            state.items = [];
            state.hero = { cards: [], balance_label: 'Net Balance' };
            renderList();
            window.DevOSAlert?.error('Unable to load', 'Transactions could not be loaded.');
        } finally {
            setLoading(false);
        }
    }

    function setFilter(filter) {
        if (state.filter === filter) return;
        state.filter = filter;
        document.querySelectorAll('[data-tx-filter]').forEach((tab) => {
            const active = tab.dataset.txFilter === filter;
            tab.classList.toggle('border-[var(--color-dp-primary)]', active);
            tab.classList.toggle('text-[var(--color-dp-primary)]', active);
            tab.classList.toggle('border-transparent', !active);
            tab.classList.toggle('text-[var(--color-dp-text-muted)]', !active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        loadTransactions();
    }

    function openModal() {
        const overlay = document.getElementById('transactionModalOverlay');
        const form = document.getElementById('transactionForm');
        if (!overlay) return;
        form?.reset();
        const dateInput = document.getElementById('txDate');
        if (dateInput) {
            dateInput.value = new Date().toISOString().slice(0, 10);
        }
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('txPartyName')?.focus(), 120);
    }

    function closeModal() {
        const overlay = document.getElementById('transactionModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    async function submitForm(event) {
        event.preventDefault();
        const btn = document.getElementById('txSubmitBtn');
        const form = event.currentTarget;
        const formData = new FormData(form);

        const payload = {
            party_name: String(formData.get('party_name') || '').trim(),
            amount: Number(formData.get('amount')),
            type: String(formData.get('type') || 'receivable'),
            category: String(formData.get('category') || '').trim(),
            transaction_date: String(formData.get('transaction_date') || ''),
            note: String(formData.get('note') || '').trim(),
        };

        if (!payload.party_name || !payload.transaction_date || !payload.amount) {
            window.DevOSAlert?.error('Missing details', 'Please fill all required fields.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `${window.DevOSHourglass?.html('xs') || ''} Saving...`;

        try {
            const response = await fetch('/transactions', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to save transaction.');
            }

            closeModal();
            window.DevOSAlert?.success('done successfully :)', data.message || 'Transaction saved.');
            await loadTransactions();
        } catch (error) {
            window.DevOSAlert?.error('Save failed', error.message || 'Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save Transaction';
        }
    }

    function bindEvents() {
        document.getElementById('btnOpenAddTransaction')?.addEventListener('click', openModal);
        document.getElementById('transactionForm')?.addEventListener('submit', submitForm);

        document.querySelectorAll('[data-close-transaction-modal]').forEach((el) => {
            el.addEventListener('click', closeModal);
        });

        document.getElementById('transactionModalOverlay')?.addEventListener('click', (event) => {
            if (event.target.id === 'transactionModalOverlay') closeModal();
        });

        document.querySelectorAll('[data-tx-filter]').forEach((tab) => {
            tab.addEventListener('click', () => setFilter(tab.dataset.txFilter || 'all'));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeModal();
        });
    }

    function init() {
        if (state.initialized || !root()) return;
        state.initialized = true;
        bindEvents();
        loadTransactions();
    }

    return { init, openModal, closeModal, loadTransactions };
})();

window.Transactions = Transactions;

export default Transactions;
