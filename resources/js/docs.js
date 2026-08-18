const DocsLanding = (() => {
    function init() {
        const root = document.querySelector('[data-docs-panels]');
        if (!root) {
            return;
        }

        const panels = [...root.querySelectorAll('.docs-panel')];

        const open = (panel) => {
            if (!panel || panel.classList.contains('is-open')) {
                return;
            }

            panels.forEach((item) => {
                const isOpen = item === panel;
                item.classList.toggle('is-open', isOpen);
                item.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        };

        panels.forEach((panel) => {
            panel.addEventListener('pointerenter', (event) => {
                if (event.pointerType === 'touch') {
                    return;
                }

                open(panel);
            });

            panel.addEventListener('click', () => open(panel));
            panel.addEventListener('focus', () => open(panel));
        });
    }

    return { init };
})();

export default DocsLanding;
