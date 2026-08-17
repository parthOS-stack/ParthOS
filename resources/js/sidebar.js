/**
 * DevOS collapsible sidebar + mobile overlay drawer
 */

const DevOSSidebar = (() => {
    const STORAGE_KEY = 'devos_sidebar_collapsed';
    const MOBILE_QUERY = '(max-width: 1024px)';

    function sidebar() {
        return document.getElementById('devosSidebar');
    }

    function overlay() {
        return document.getElementById('sidebarOverlay');
    }

    function navbarToggleBtn() {
        return document.getElementById('navbarSidebarToggle');
    }

    function media() {
        return window.matchMedia(MOBILE_QUERY);
    }

    function isMobile() {
        return media().matches;
    }

    function isCollapsed() {
        return sidebar()?.classList.contains('is-collapsed') ?? false;
    }

    function isMobileOpen() {
        return sidebar()?.classList.contains('is-mobile-open') ?? false;
    }

    function updateToggleUi() {
        const navBtn = navbarToggleBtn();
        if (!navBtn) return;

        if (isMobile()) {
            const open = isMobileOpen();
            navBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            navBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            return;
        }

        const collapsed = isCollapsed();
        navBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        navBtn.setAttribute('aria-label', collapsed ? 'Open menu' : 'Close menu');
    }

    function setMobileOpen(open) {
        const el = sidebar();
        const veil = overlay();
        if (!el) return;

        el.classList.toggle('is-mobile-open', open);
        document.body.classList.toggle('sidebar-mobile-open', open);

        if (veil) {
            veil.classList.toggle('is-visible', open);
        }

        updateToggleUi();
    }

    function setCollapsed(collapsed, persist = true) {
        const el = sidebar();
        if (!el) return;

        if (collapsed) {
            el.classList.remove('is-opening');
            el.classList.add('is-collapsed');
        } else {
            el.classList.remove('is-collapsed');
            el.classList.add('is-opening');

            let openingDone = false;
            const finishOpening = (event) => {
                if (event && (event.target !== el || event.propertyName !== 'width')) {
                    return;
                }

                if (openingDone) {
                    return;
                }

                openingDone = true;
                el.classList.remove('is-opening');
                el.removeEventListener('transitionend', finishOpening);
            };

            el.addEventListener('transitionend', finishOpening);
            window.setTimeout(finishOpening, 400);
        }

        document.body.classList.toggle('sidebar-collapsed', collapsed);
        document.documentElement.classList.remove('sidebar-collapsed-pending');
        updateToggleUi();

        if (persist) {
            try {
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            } catch (error) {
                // ignore
            }
        }
    }

    function toggle() {
        if (isMobile()) {
            setMobileOpen(!isMobileOpen());
            return;
        }

        setCollapsed(!isCollapsed());
    }

    function restore() {
        if (isMobile()) {
            document.documentElement.classList.remove('sidebar-collapsed-pending');
            setMobileOpen(false);
            return;
        }

        let collapsed = false;
        try {
            collapsed = localStorage.getItem(STORAGE_KEY) === '1';
        } catch (error) {
            collapsed = false;
        }
        setCollapsed(collapsed, false);
    }

    function init() {
        const el = sidebar();
        if (!el) return;

        restore();
        navbarToggleBtn()?.addEventListener('click', toggle);
        overlay()?.addEventListener('click', () => setMobileOpen(false));

        el.querySelectorAll('.dp-nav-item, .dp-sidebar-brand').forEach((link) => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    setMobileOpen(false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isMobile() && isMobileOpen()) {
                setMobileOpen(false);
            }
        });

        const mq = media();
        const onBreakpoint = () => restore();
        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', onBreakpoint);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(onBreakpoint);
        }
    }

    return { init, toggle, setCollapsed, isCollapsed, setMobileOpen };
})();

window.DevOSSidebar = DevOSSidebar;

export default DevOSSidebar;
