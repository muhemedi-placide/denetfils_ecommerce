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

Alpine.data('adminShell', (config) => ({
    theme: preferredTheme(),
    sidebarOpen: false,
    sidebarCollapsed: false,
    sidebarHover: false,
    commandOpen: false,
    quickActionsOpen: false,
    logoutOpen: false,
    commandQuery: '',
    commandItems: config.commandItems || [],
    openMenus: {},

    init() {
        this.sidebarCollapsed = this.readBoolean('adminSidebarCollapsed', false);
        this.openMenus = (config.openMenus || []).reduce((menus, key) => ({
            ...menus,
            [key]: true,
        }), {});
        this.setTheme(this.theme, false);
        this.watchSystemTheme();
        this.watchViewport();
    },

    setTheme(value, persist = true) {
        this.theme = value;
        applyTheme(value, persist);
    },

    toggleTheme() {
        this.setTheme(this.theme === 'dark' ? 'light' : 'dark');
    },

    sidebarExpanded() {
        return !this.sidebarCollapsed || this.sidebarHover;
    },

    toggleSidebarSize() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        this.writeBoolean('adminSidebarCollapsed', this.sidebarCollapsed);
    },

    isMenuOpen(key) {
        return Boolean(this.openMenus[key]);
    },

    toggleMenu(key) {
        this.openMenus = {
            ...this.openMenus,
            [key]: !this.openMenus[key],
        };
    },

    closeOverlays() {
        this.sidebarOpen = false;
        this.commandOpen = false;
        this.quickActionsOpen = false;
        this.logoutOpen = false;
    },

    filteredCommandItems() {
        const query = this.commandQuery.trim().toLowerCase();

        if (!query) {
            return this.commandItems;
        }

        return this.commandItems.filter((item) => {
            const haystack = `${item.label || ''} ${item.hint || ''}`.toLowerCase();

            return haystack.includes(query);
        });
    },

    watchViewport() {
        const desktopQuery = window.matchMedia('(min-width: 1024px)');
        const syncViewport = (event) => {
            if (event.matches) {
                this.sidebarOpen = false;
            }
        };

        syncViewport(desktopQuery);
        desktopQuery.addEventListener('change', syncViewport);
    },

    watchSystemTheme() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        mediaQuery.addEventListener('change', (event) => {
            if (!readStoredTheme()) {
                this.setTheme(event.matches ? 'dark' : 'light', false);
            }
        });
    },

    readBoolean(key, fallback) {
        try {
            const value = localStorage.getItem(key);

            if (value === null) {
                return fallback;
            }

            return value === 'true';
        } catch (error) {
            return fallback;
        }
    },

    writeBoolean(key, value) {
        try {
            localStorage.setItem(key, value ? 'true' : 'false');
        } catch (error) {
            // Sidebar state is only a convenience.
        }
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
