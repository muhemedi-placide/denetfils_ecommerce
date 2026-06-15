if (!window.ShopUi) {
    const readStoredTheme = () => {
        try {
            return localStorage.getItem('theme');
        } catch (error) {
            return null;
        }
    };

    const writeStoredTheme = (value) => {
        try {
            localStorage.setItem('theme', value);
        } catch (error) {
            // Browsers with disabled storage still get the visible theme change.
        }
    };

    const normalizeTheme = (value) => (['light', 'dark'].includes(value) ? value : null);

    const prefersDark = () => window.matchMedia('(prefers-color-scheme: dark)').matches;

    const systemTheme = () => (prefersDark() ? 'dark' : 'light');

    const preferredTheme = () => normalizeTheme(readStoredTheme()) || systemTheme();

    const refreshThemeIcons = (theme) => {
        document.querySelectorAll('[data-theme-icon="light"]').forEach((icon) => {
            icon.classList.toggle('hidden', theme !== 'light');
        });

        document.querySelectorAll('[data-theme-icon="dark"]').forEach((icon) => {
            icon.classList.toggle('hidden', theme !== 'dark');
        });
    };

    const applyTheme = (value, persist = true) => {
        value = normalizeTheme(value) || systemTheme();

        if (persist) {
            writeStoredTheme(value);
        }

        document.documentElement.classList.toggle('dark', value === 'dark');
        document.body?.classList.toggle('dark', value === 'dark');
        refreshThemeIcons(value);
    };

    const setMobileMenu = (open) => {
        const button = document.querySelector('[data-mobile-menu-toggle]');
        const state = document.getElementById('mobile-menu-state');

        if (!button) {
            return;
        }

        if (state) {
            state.checked = open;
        }

        button.setAttribute('aria-expanded', open ? 'true' : 'false');

        document.querySelectorAll('[data-mobile-menu-icon="open"]').forEach((icon) => {
            icon.classList.toggle('hidden', open);
        });

        document.querySelectorAll('[data-mobile-menu-icon="close"]').forEach((icon) => {
            icon.classList.toggle('hidden', !open);
        });
    };

    const initGlobalUi = () => {
        applyTheme(preferredTheme(), false);
        setMobileMenu(false);
    };

    document.addEventListener('click', (event) => {
        const themeToggle = event.target.closest('[data-theme-toggle]');

        if (themeToggle) {
            event.preventDefault();
            const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            applyTheme(nextTheme);
            return;
        }

        if (event.target.closest('[data-mobile-menu] a')) {
            setMobileMenu(false);
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target?.matches?.('#mobile-menu-state')) {
            setMobileMenu(event.target.checked);
        }
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        if (!normalizeTheme(readStoredTheme())) {
            applyTheme(event.matches ? 'dark' : 'light', false);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalUi);
    } else {
        initGlobalUi();
    }

    window.ShopUi = {
        applyTheme,
        initGlobalUi,
        preferredTheme,
        readStoredTheme,
        setMobileMenu,
    };
}
