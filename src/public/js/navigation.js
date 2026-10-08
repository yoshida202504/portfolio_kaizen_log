document.addEventListener('DOMContentLoaded', () => {
    const appShell = document.querySelector('.app-shell');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const sidebarResizer = document.getElementById('sidebar-resizer');
    const mobileViewport = window.matchMedia('(max-width: 760px)');

    if (appShell && mobileMenuToggle && sidebarBackdrop) {
        const closeMobileMenu = () => {
            appShell.classList.remove('mobile-menu-open');
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
            sidebarBackdrop.hidden = true;
        };

        mobileMenuToggle.addEventListener('click', () => {
            const isOpen = appShell.classList.toggle('mobile-menu-open');

            mobileMenuToggle.setAttribute('aria-expanded', String(isOpen));
            sidebarBackdrop.hidden = ! isOpen;
        });

        sidebarBackdrop.addEventListener('click', closeMobileMenu);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileViewport.matches) {
                closeMobileMenu();
                mobileMenuToggle.focus();
            }
        });

        document.querySelectorAll('.side-nav a').forEach((link) => {
            link.addEventListener('click', () => {
                if (mobileViewport.matches) {
                    closeMobileMenu();
                }
            });
        });

        mobileViewport.addEventListener('change', (event) => {
            if (! event.matches) {
                closeMobileMenu();
            }
        });
    }

    if (appShell && sidebarResizer) {
        const minimumSidebarWidth = 220;
        const maximumSidebarWidth = 360;
        const keyboardWidthStep = 20;

        const setSidebarWidth = (candidateWidth) => {
            const width = Math.min(maximumSidebarWidth, Math.max(minimumSidebarWidth, candidateWidth));

            appShell.style.setProperty('--sidebar-width', `${width}px`);
            sidebarResizer.setAttribute('aria-valuenow', String(width));
        };

        const currentSidebarWidth = () => {
            const width = Number.parseInt(getComputedStyle(appShell).getPropertyValue('--sidebar-width'), 10);

            return Number.isNaN(width) ? 260 : width;
        };

        sidebarResizer.addEventListener('pointerdown', (event) => {
            if (mobileViewport.matches) {
                return;
            }

            event.preventDefault();
            appShell.classList.add('is-resizing');
            sidebarResizer.setPointerCapture(event.pointerId);
        });

        sidebarResizer.addEventListener('pointermove', (event) => {
            if (! appShell.classList.contains('is-resizing')) {
                return;
            }

            setSidebarWidth(event.clientX - appShell.getBoundingClientRect().left);
        });

        const stopResizing = (event) => {
            appShell.classList.remove('is-resizing');

            if (sidebarResizer.hasPointerCapture(event.pointerId)) {
                sidebarResizer.releasePointerCapture(event.pointerId);
            }
        };

        sidebarResizer.addEventListener('pointerup', stopResizing);
        sidebarResizer.addEventListener('pointercancel', stopResizing);
        sidebarResizer.addEventListener('keydown', (event) => {
            if (! ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            const width = event.key === 'Home'
                ? minimumSidebarWidth
                : event.key === 'End'
                    ? maximumSidebarWidth
                    : currentSidebarWidth() + (event.key === 'ArrowRight' ? keyboardWidthStep : -keyboardWidthStep);

            setSidebarWidth(width);
        });
    }

    document.querySelectorAll('[role="tablist"]').forEach((tabList) => {
        const tabs = Array.from(tabList.querySelectorAll('[role="tab"]'));

        const selectTab = (selectedTab) => {
            tabs.forEach((tab) => {
                const isSelected = tab === selectedTab;
                const panel = document.getElementById(tab.getAttribute('aria-controls'));

                tab.setAttribute('aria-selected', String(isSelected));
                tab.tabIndex = isSelected ? 0 : -1;
                tab.classList.toggle('is-active', isSelected);
                panel.hidden = ! isSelected;
            });
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => selectTab(tab));
            tab.addEventListener('keydown', (event) => {
                if (! ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                    return;
                }

                event.preventDefault();
                const targetIndex = event.key === 'Home'
                    ? 0
                    : event.key === 'End'
                        ? tabs.length - 1
                        : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                const targetTab = tabs[targetIndex];

                targetTab.focus();
                selectTab(targetTab);
            });
        });
    });
});
