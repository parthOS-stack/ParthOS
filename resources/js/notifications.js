/**
 * DevOS Notification Center — header bell + dropdown (image-2)
 */

const DevOSNotifications = (() => {
    const state = {
        initialized: false,
        open: false,
        filter: 'all',
        items: [],
        unread: 0,
        loading: false,
        pollTimer: null,
        avatarUrl: '',
    };

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function els() {
        return {
            wrap: document.getElementById('devosNotifWrap'),
            btn: document.getElementById('devosNotifBtn'),
            badge: document.getElementById('devosNotifBadge'),
            panel: document.getElementById('devosNotifPanel'),
            list: document.getElementById('devosNotifList'),
            listWrap: document.getElementById('devosNotifListWrap'),
            markAll: document.getElementById('devosNotifMarkAll'),
        };
    }

    function iconGlyph(icon) {
        const map = {
            shield: '🛡',
            folder: '📁',
            check: '✓',
            user: '👤',
            lock: '🔒',
            alert: '!',
            bell: '🔔',
        };
        return map[icon] || map.bell;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatMessage(message) {
        if (!message) return '';
        return escapeHtml(message).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    }

    function updateBadge() {
        const { btn, badge } = els();
        if (!badge || !btn) return;
        if (state.unread > 0) {
            badge.textContent = state.unread > 99 ? '99+' : String(state.unread);
            badge.classList.add('is-visible');
            btn.classList.add('has-unread');
        } else {
            badge.classList.remove('is-visible');
            btn.classList.remove('has-unread');
        }
    }

    function updateFade() {
        const { list, listWrap } = els();
        if (!list || !listWrap) return;
        const canScroll = list.scrollHeight > list.clientHeight + 8;
        const nearBottom = list.scrollTop + list.clientHeight >= list.scrollHeight - 12;
        listWrap.classList.toggle('has-fade', canScroll && !nearBottom);
    }

    function renderList() {
        const { list } = els();
        if (!list) return;

        if (state.loading && !state.items.length) {
            list.innerHTML = `<li class="devos-notif-loading">${window.DevOSHourglass?.html('sm') || ''}<span>Loading notifications…</span></li>`;
            updateFade();
            return;
        }

        if (!state.items.length) {
            list.innerHTML = `<li class="devos-notif-empty">${state.filter === 'unread' ? 'No unread notifications.' : 'No notifications yet.'}</li>`;
            updateFade();
            return;
        }

        list.innerHTML = state.items.map((item) => {
            const icon = item.meta?.icon || 'bell';
            const unreadClass = item.is_unread ? 'is-unread' : '';
            return `
                <li class="devos-notif-item ${unreadClass}" data-id="${item.id}" role="button" tabindex="0">
                    <div class="devos-notif-avatar-wrap">
                        <img class="devos-notif-avatar" src="${escapeHtml(state.avatarUrl)}" alt="" />
                        <span class="devos-notif-type-badge" data-icon="${escapeHtml(icon)}">${iconGlyph(icon)}</span>
                    </div>
                    <div class="devos-notif-content">
                        <div class="devos-notif-top">
                            <span class="devos-notif-title">${escapeHtml(item.title)}</span>
                            <span class="devos-notif-time">${escapeHtml(item.created_at_human || '')}</span>
                        </div>
                        ${item.message ? `<p class="devos-notif-message">${formatMessage(item.message)}</p>` : ''}
                        ${item.snippet ? `<p class="devos-notif-snippet">${escapeHtml(item.snippet)}</p>` : ''}
                    </div>
                    <span class="devos-notif-dot" aria-hidden="true"></span>
                </li>
            `;
        }).join('');

        requestAnimationFrame(updateFade);
    }

    async function fetchList() {
        const { list } = els();
        if (!list) return;
        state.loading = true;
        if (!state.items.length) renderList();

        try {
            const response = await fetch(`/notifications?filter=${encodeURIComponent(state.filter)}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Failed');
            const data = await response.json();
            state.items = data.notifications || [];
            state.unread = Number(data.unread_count || 0);
            updateBadge();
            renderList();
        } catch (error) {
            console.error(error);
            if (!state.items.length) {
                list.innerHTML = '<li class="devos-notif-empty">Unable to load notifications.</li>';
            }
        } finally {
            state.loading = false;
        }
    }

    async function refreshCount({ ping = false } = {}) {
        const { btn } = els();
        try {
            const response = await fetch('/notifications/unread-count', {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return;
            const data = await response.json();
            const next = Number(data.unread_count || 0);
            if (ping && next > state.unread) {
                btn?.classList.add('is-ping');
                setTimeout(() => btn?.classList.remove('is-ping'), 1600);
                window.DevOSSounds?.play('notification');
            }
            state.unread = next;
            updateBadge();
            if (state.open) fetchList();
        } catch (error) {
            // silent
        }
    }

    function setFilter(filter) {
        if (state.filter === filter) return;
        state.filter = filter;
        document.querySelectorAll('.devos-notif-tab').forEach((tab) => {
            tab.classList.toggle('is-active', tab.dataset.filter === filter);
        });
        fetchList();
    }

    function openPanel() {
        const { btn, panel } = els();
        if (!panel) return;
        state.open = true;
        panel.classList.add('is-open');
        btn?.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        fetchList();
    }

    function closePanel() {
        const { btn, panel } = els();
        if (!panel) return;
        state.open = false;
        panel.classList.remove('is-open');
        btn?.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    function togglePanel() {
        if (state.open) closePanel();
        else openPanel();
    }

    async function markRead(id) {
        try {
            const response = await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });
            if (!response.ok) return;
            const data = await response.json();
            state.unread = Number(data.unread_count || 0);
            updateBadge();
            const item = state.items.find((n) => Number(n.id) === Number(id));
            if (item) item.is_unread = false;
            if (state.filter === 'unread') {
                state.items = state.items.filter((n) => Number(n.id) !== Number(id));
            }
            renderList();
        } catch (error) {
            console.error(error);
        }
    }

    async function markAllRead() {
        try {
            const response = await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });
            if (!response.ok) return;
            state.unread = 0;
            state.items = state.items.map((n) => ({ ...n, is_unread: false }));
            if (state.filter === 'unread') state.items = [];
            updateBadge();
            renderList();
        } catch (error) {
            console.error(error);
        }
    }

    function init() {
        if (state.initialized) return;
        const { wrap, btn, panel, list, markAll } = els();
        if (!wrap || !btn || !panel) return;
        state.initialized = true;
        state.avatarUrl = wrap.dataset.avatar || 'https://api.dicebear.com/7.x/notionists/svg?seed=devos';

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePanel();
        });

        document.querySelectorAll('.devos-notif-tab').forEach((tab) => {
            tab.addEventListener('click', (e) => {
                e.stopPropagation();
                setFilter(tab.dataset.filter || 'all');
            });
        });

        markAll?.addEventListener('click', (e) => {
            e.stopPropagation();
            markAllRead();
        });

        list?.addEventListener('click', (e) => {
            const item = e.target.closest('.devos-notif-item');
            if (!item) return;
            markRead(item.dataset.id);
        });

        list?.addEventListener('scroll', updateFade);

        document.addEventListener('click', (e) => {
            if (!state.open) return;
            if (wrap.contains(e.target)) return;
            closePanel();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && state.open) closePanel();
        });

        refreshCount();
        state.pollTimer = setInterval(() => refreshCount({ ping: true }), 45000);
    }

    return {
        init,
        refresh: () => refreshCount({ ping: true }),
        open: openPanel,
        close: closePanel,
    };
})();

window.DevOSNotifications = DevOSNotifications;

export default DevOSNotifications;
