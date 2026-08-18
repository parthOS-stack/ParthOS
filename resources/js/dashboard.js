const Dashboard = (() => {
    let timer = null;
    let animationFrame = null;
    let privacyBound = false;

    function updateClock() {
        const root = document.querySelector('[data-dashboard-root]');
        if (!root) return;

        const now = new Date();
        const timeEl = root.querySelector('[data-dashboard-time]');
        const dayEl = root.querySelector('[data-dashboard-day]');
        const dateEl = root.querySelector('[data-dashboard-date]');

        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
        }

        if (dayEl) {
            dayEl.textContent = now.toLocaleDateString([], { weekday: 'long' });
        }

        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString([], {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        }

        const hourHand = root.querySelector('[data-dashboard-hour-hand]');
        const minuteHand = root.querySelector('[data-dashboard-minute-hand]');
        const secondHand = root.querySelector('[data-dashboard-second-hand]');

        const seconds = now.getSeconds() + now.getMilliseconds() / 1000;
        const minutes = now.getMinutes() + seconds / 60;
        const hours = (now.getHours() % 12) + minutes / 60;

        const secondDeg = seconds * 6;
        const minuteDeg = minutes * 6;
        const hourDeg = hours * 30;

        if (hourHand) {
            hourHand.style.transform = `translateX(-50%) rotate(${hourDeg}deg)`;
        }

        if (minuteHand) {
            minuteHand.style.transform = `translateX(-50%) rotate(${minuteDeg}deg)`;
        }

        if (secondHand) {
            secondHand.style.transform = `translateX(-50%) rotate(${secondDeg}deg)`;
        }
    }

    function animateClock() {
        updateClock();
        animationFrame = window.requestAnimationFrame(animateClock);
    }

    function initPrivacyCard() {
        if (privacyBound) return;

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-privacy-card]');
            if (!trigger) return;

            trigger.classList.toggle('is-revealed');
            trigger.setAttribute(
                'aria-label',
                trigger.classList.contains('is-revealed')
                    ? 'Hide high security count'
                    : 'Reveal high security count'
            );
        });

        privacyBound = true;
    }

    function init() {
        if (!document.querySelector('[data-dashboard-root]')) return;

        if (timer) window.clearInterval(timer);
        if (animationFrame) window.cancelAnimationFrame(animationFrame);

        initPrivacyCard();
        updateClock();
        timer = window.setInterval(updateClock, 1000);
        animationFrame = window.requestAnimationFrame(animateClock);
    }

    return { init };
})();

export default Dashboard;
