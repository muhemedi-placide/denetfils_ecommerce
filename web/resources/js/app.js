import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js';

window.Alpine = Alpine;

const {
    applyTheme,
    initGlobalUi,
    preferredTheme,
    readStoredTheme,
    setMobileMenu,
} = window.ShopUi;

Alpine.data('shopApp', (config) => ({
    locale: config.locale,
    theme: preferredTheme(),
    activeMenu: config.activeMenu || 'home',
    mobileMenuOpen: false,
    alertIndex: 0,

    init() {
        this.setTheme(this.theme, false);
        this.watchSystemTheme();
        this.watchViewport();
        this.initNavigation();
    },

    setTheme(value, persist = true) {
        this.theme = value;
        applyTheme(value, persist);
    },

    toggleTheme() {
        this.setTheme(this.theme === 'dark' ? 'light' : 'dark');
    },

    toggleMobileMenu() {
        this.mobileMenuOpen = !this.mobileMenuOpen;
        setMobileMenu(this.mobileMenuOpen);
    },

    closeMobileMenu() {
        this.mobileMenuOpen = false;
        setMobileMenu(false);
    },

    watchViewport() {
        const desktopQuery = window.matchMedia('(min-width: 1024px)');
        const closeOnDesktop = (event) => {
            if (event.matches) {
                this.closeMobileMenu();
            }
        };

        closeOnDesktop(desktopQuery);
        desktopQuery.addEventListener('change', closeOnDesktop);
    },

    watchSystemTheme() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        mediaQuery.addEventListener('change', (event) => {
            if (!readStoredTheme()) {
                this.setTheme(event.matches ? 'dark' : 'light', false);
            }
        });
    },

    initNavigation() {
        const sections = ['home', 'about', 'products', 'blog'];
        let ticking = false;

        const updateActiveMenu = () => {
            let current = null;
            const offset = window.innerHeight * 0.35;

            for (const id of sections) {
                const element = document.getElementById(id);

                if (element && element.getBoundingClientRect().top <= offset) {
                    current = id;
                }
            }

            if (current) {
                this.activeMenu = current;
            }
        };

        const requestUpdate = () => {
            if (ticking) {
                return;
            }

            ticking = true;
            window.requestAnimationFrame(() => {
                updateActiveMenu();
                ticking = false;
            });
        };

        updateActiveMenu();
        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('hashchange', () => {
            this.activeMenu = window.location.hash.replace('#', '') || config.activeMenu || 'home';
            this.closeMobileMenu();
        });
    },

}));

document.addEventListener('livewire:navigate', () => {
    document.documentElement.classList.add('is-navigating');
    setMobileMenu(false);
    document.getElementById('shop-app')?._x_dataStack?.[0]?.closeMobileMenu?.();
});

document.addEventListener('livewire:navigated', () => {
    document.documentElement.classList.remove('is-navigating');
    initGlobalUi();
});

Livewire.start();
