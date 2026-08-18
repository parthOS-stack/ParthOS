const STORAGE_KEY = 'devos_theme';

const DevOSTheme = (() => {
    function getTheme() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored === 'dark' || stored === 'light') {
                return stored;
            }
        } catch (error) {
            // ignore
        }

        return 'light';
    }

    function syncToggle(theme) {
        const toggle = document.getElementById('themeToggle');
        if (!toggle) {
            return;
        }

        const isDark = theme === 'dark';
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        toggle.classList.toggle('is-dark', isDark);
    }

    function apply(theme, { animate = false } = {}) {
        const root = document.documentElement;
        const next = theme === 'dark' ? 'dark' : 'light';

        if (animate) {
            root.classList.add('theme-animate');
            window.setTimeout(() => root.classList.remove('theme-animate'), 380);
        }

        root.classList.toggle('dark', next === 'dark');
        root.style.colorScheme = next;

        try {
            localStorage.setItem(STORAGE_KEY, next);
        } catch (error) {
            // ignore
        }

        syncToggle(next);
    }

    function toggle() {
        apply(getTheme() === 'dark' ? 'light' : 'dark', { animate: true });
    }

    function init() {
        apply(getTheme(), { animate: false });

        const button = document.getElementById('themeToggle');
        if (!button) {
            return;
        }

        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            toggle();
        });
    }

    return { init, apply, toggle, getTheme };
})();

export default DevOSTheme;
