import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('shopApp', (config) => ({
    apiBaseUrl: config.apiBaseUrl.replace(/\/$/, ''),
    locale: config.locale,
    labels: config.labels,
    theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    activeMenu: config.activeMenu || 'home',
    alertIndex: 0,
    cartOpen: false,
    cartLoading: false,
    cartMutating: false,
    cartError: null,
    cart: null,

    init() {
        this.setTheme(this.theme, false);
        this.watchSystemTheme();
        this.loadCart(false);
        this.initNavigation();
    },

    setTheme(value, persist = true) {
        this.theme = value;

        if (persist) {
            localStorage.setItem('theme', value);
        }

        document.documentElement.classList.toggle('dark', value === 'dark');
        document.body.classList.toggle('dark', value === 'dark');
    },

    toggleTheme() {
        this.setTheme(this.theme === 'dark' ? 'light' : 'dark');
    },

    watchSystemTheme() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        mediaQuery.addEventListener('change', (event) => {
            if (!localStorage.getItem('theme')) {
                this.setTheme(event.matches ? 'dark' : 'light', false);
            }
        });
    },

    initNavigation() {
        const sections = ['home', 'about', 'products', 'blog'];

        const updateActiveMenu = () => {
            const visibleSections = sections
                .map((id) => ({ id, element: document.getElementById(id) }))
                .filter((item) => item.element);

            if (visibleSections.length === 0) {
                return;
            }

            const offset = window.innerHeight * 0.35;
            const current = visibleSections.findLast((item) => item.element.getBoundingClientRect().top <= offset);

            if (current) {
                this.activeMenu = current.id;
            }
        };

        updateActiveMenu();
        window.addEventListener('scroll', updateActiveMenu, { passive: true });
        window.addEventListener('hashchange', () => {
            this.activeMenu = window.location.hash.replace('#', '') || config.activeMenu || 'home';
        });
    },

    get itemCount() {
        return (this.cart?.items || []).reduce((total, item) => total + Number(item.quantity || 0), 0);
    },

    get cartItems() {
        return this.cart?.items || [];
    },

    get formattedTotal() {
        return this.cart?.formatted_total || this.labels.emptyTotal;
    },

    emptyCart() {
        return {
            cart_token: null,
            subtotal_cents: 0,
            tax_cents: 0,
            total_cents: 0,
            formatted_total: this.labels.emptyTotal,
            items: [],
        };
    },

    url(path) {
        const normalizedPath = path.replace(/^\//, '');
        const url = new URL(`${this.apiBaseUrl}/${normalizedPath}`);
        url.searchParams.set('locale', this.locale);

        return url.toString();
    },

    async request(path, options = {}) {
        const response = await fetch(this.url(path), {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(options.headers || {}),
            },
            ...options,
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = payload.message || this.labels.apiError;
            throw new Error(message);
        }

        return payload.data;
    },

    async loadCart(openDrawer = true) {
        const token = localStorage.getItem('denetfils_cart_token');

        if (!token) {
            this.cart = this.emptyCart();
            this.cartOpen = openDrawer;
            return;
        }

        this.cartLoading = true;
        this.cartError = null;

        try {
            this.cart = await this.request(`carts/${token}`);
            this.cartOpen = openDrawer;
        } catch (error) {
            localStorage.removeItem('denetfils_cart_token');
            this.cart = this.emptyCart();
            this.cartError = this.labels.cartExpired;
            this.cartOpen = openDrawer;
        } finally {
            this.cartLoading = false;
        }
    },

    async ensureCart() {
        const existingToken = localStorage.getItem('denetfils_cart_token');

        if (existingToken && this.cart?.cart_token === existingToken) {
            return existingToken;
        }

        if (existingToken) {
            try {
                this.cart = await this.request(`carts/${existingToken}`);
                return existingToken;
            } catch (error) {
                localStorage.removeItem('denetfils_cart_token');
            }
        }

        const cart = await this.request('carts', { method: 'POST' });
        localStorage.setItem('denetfils_cart_token', cart.cart_token);
        this.cart = cart;

        return cart.cart_token;
    },

    async addToCart(productId, variantId = null) {
        this.cartMutating = true;
        this.cartError = null;

        try {
            const token = await this.ensureCart();
            const body = {
                product_id: productId,
                quantity: 1,
            };

            if (variantId) {
                body.product_variant_id = Number(variantId);
            }

            this.cart = await this.request(`carts/${token}/items`, {
                method: 'POST',
                body: JSON.stringify(body),
            });
            this.cartOpen = true;
        } catch (error) {
            this.cartError = error.message || this.labels.apiError;
            this.cartOpen = true;
        } finally {
            this.cartMutating = false;
        }
    },

    async updateCartItem(itemId, quantity) {
        const nextQuantity = Number(quantity);

        if (nextQuantity < 1 || !this.cart?.cart_token) {
            return;
        }

        this.cartMutating = true;
        this.cartError = null;

        try {
            this.cart = await this.request(`carts/${this.cart.cart_token}/items/${itemId}`, {
                method: 'PATCH',
                body: JSON.stringify({ quantity: nextQuantity }),
            });
        } catch (error) {
            this.cartError = error.message || this.labels.apiError;
        } finally {
            this.cartMutating = false;
        }
    },

    async removeCartItem(itemId) {
        if (!this.cart?.cart_token) {
            return;
        }

        this.cartMutating = true;
        this.cartError = null;

        try {
            this.cart = await this.request(`carts/${this.cart.cart_token}/items/${itemId}`, {
                method: 'DELETE',
            });
        } catch (error) {
            this.cartError = error.message || this.labels.apiError;
        } finally {
            this.cartMutating = false;
        }
    },
}));

Alpine.start();
