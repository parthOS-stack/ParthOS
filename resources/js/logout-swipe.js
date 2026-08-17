/**
 * Swipe-right to logout control (sidebar footer)
 */

const LogoutSwipe = (() => {
    const THRESHOLD = 0.9;

    function init() {
        const root = document.getElementById('logoutSwipe');
        const track = document.getElementById('logoutSwipeTrack');
        const handle = document.getElementById('logoutSwipeHandle');
        const fill = document.getElementById('logoutSwipeFill');
        const form = document.getElementById('logoutSwipeForm');
        const start = root?.querySelector('.dp-logout-swipe-start');
        const sidebar = document.getElementById('devosSidebar');

        if (!root || !track || !handle || !form) {
            return;
        }

        let dragging = false;
        let startX = 0;
        let startOffset = 0;
        let currentOffset = 0;
        let completed = false;

        function isCompact() {
            return sidebar?.classList.contains('is-collapsed')
                || sidebar?.classList.contains('is-opening');
        }

        function maxTravel() {
            return Math.max(0, track.clientWidth - handle.offsetWidth - 6);
        }

        function setOffset(offset, animate = false) {
            const max = maxTravel();
            currentOffset = Math.max(0, Math.min(offset, max));

            handle.style.transition = animate
                ? 'transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), background 0.25s ease'
                : 'none';
            fill.style.transition = animate ? 'width 0.32s cubic-bezier(0.22, 1, 0.36, 1)' : 'none';

            handle.style.transform = `translateX(${currentOffset}px)`;
            fill.style.width = `${currentOffset + handle.offsetWidth * 0.55}px`;

            const armed = currentOffset >= max * 0.65;
            root.classList.toggle('is-armed', armed);
        }

        function reset(animate = true) {
            if (completed) {
                return;
            }

            setOffset(0, animate);
            handle.classList.remove('is-complete');
            root.classList.remove('is-armed', 'is-dragging');
        }

        function complete() {
            if (completed) {
                return;
            }

            completed = true;
            setOffset(maxTravel(), true);
            handle.classList.add('is-complete');
            root.classList.add('is-armed');

            window.setTimeout(() => {
                form.submit();
            }, 180);
        }

        function onPointerDown(event) {
            if (completed || isCompact()) {
                return;
            }

            dragging = true;
            startX = event.clientX;
            startOffset = currentOffset;
            root.classList.add('is-dragging');
            handle.setPointerCapture(event.pointerId);
            event.preventDefault();
        }

        function onPointerMove(event) {
            if (!dragging || completed) {
                return;
            }

            const delta = event.clientX - startX;
            setOffset(startOffset + delta, false);

            if (currentOffset >= maxTravel() * THRESHOLD) {
                dragging = false;
                complete();
            }
        }

        function onPointerUp() {
            if (!dragging || completed) {
                return;
            }

            dragging = false;
            root.classList.remove('is-dragging');

            if (currentOffset >= maxTravel() * THRESHOLD) {
                complete();
                return;
            }

            reset(true);
        }

        handle.addEventListener('pointerdown', onPointerDown);
        handle.addEventListener('pointermove', onPointerMove);
        handle.addEventListener('pointerup', onPointerUp);
        handle.addEventListener('pointercancel', onPointerUp);

        start?.addEventListener('click', () => {
            if (isCompact()) {
                form.submit();
            }
        });

        window.addEventListener('resize', () => {
            if (!dragging && !completed) {
                reset(false);
            }
        });
    }

    return { init };
})();

export default LogoutSwipe;
