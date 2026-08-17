/**
 * DevOS Alert Popup — bottom-right toast (image-3)
 * Usage: DevOSAlert.success('Title', 'Description')
 */

const DevOSAlert = (() => {
    const ICONS = {
        success: '✓',
        error: '✕',
        warning: '!',
        info: 'i',
        update: '↻',
        delete: '🗑',
        rename: '✎',
    };

    let stack = null;

    function ensureStack() {
        if (stack && document.body.contains(stack)) return stack;
        stack = document.getElementById('devosAlertStack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'devosAlertStack';
            stack.className = 'devos-alert-stack';
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }
        return stack;
    }

    function show({ type = 'success', title = '', description = '', duration = 3200 } = {}) {
        const root = ensureStack();
        const el = document.createElement('div');
        const kind = ICONS[type] ? type : 'info';
        el.className = `devos-alert devos-alert--${kind}`;
        el.setAttribute('role', 'status');
        el.innerHTML = `
            <div class="devos-alert-icon" aria-hidden="true">${ICONS[kind]}</div>
            <div class="devos-alert-body">
                <p class="devos-alert-title"></p>
                ${description ? '<p class="devos-alert-desc"></p>' : ''}
            </div>
            <button type="button" class="devos-alert-close" aria-label="Dismiss">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        el.querySelector('.devos-alert-title').textContent = title || 'Notification';
        const descEl = el.querySelector('.devos-alert-desc');
        if (descEl) descEl.textContent = description;

        const dismiss = () => {
            el.classList.remove('is-in');
            el.classList.add('is-out');
            setTimeout(() => el.remove(), 320);
        };

        el.querySelector('.devos-alert-close')?.addEventListener('click', dismiss);
        root.appendChild(el);
        requestAnimationFrame(() => el.classList.add('is-in'));

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }

        if (kind === 'error') {
            window.DevOSSounds?.play('error');
        } else if (['success', 'update', 'delete', 'rename'].includes(kind)) {
            window.DevOSSounds?.play('success');
        }

        return el;
    }

    function success(title, description, duration) {
        return show({ type: 'success', title, description, duration });
    }

    function error(title, description, duration) {
        return show({ type: 'error', title, description, duration: duration ?? 4200 });
    }

    function warning(title, description, duration) {
        return show({ type: 'warning', title, description, duration });
    }

    function info(title, description, duration) {
        return show({ type: 'info', title, description, duration });
    }

    function update(title, description, duration) {
        return show({ type: 'update', title, description, duration });
    }

    function deleted(title, description, duration) {
        return show({ type: 'delete', title, description, duration });
    }

    function rename(title, description, duration) {
        return show({ type: 'rename', title, description, duration });
    }

    function bootFromFlash() {
        const flash = document.getElementById('devosAlertFlash');
        if (!flash) return;
        const type = flash.dataset.type || 'success';
        const title = flash.dataset.title || '';
        const description = flash.dataset.description || '';
        if (title || description) {
            show({ type, title: title || description, description: title ? description : '' });
        }
        flash.remove();
    }

    function init() {
        ensureStack();
        bootFromFlash();
    }

    return { init, show, success, error, warning, info, update, deleted, rename };
})();

window.DevOSAlert = DevOSAlert;

export default DevOSAlert;
