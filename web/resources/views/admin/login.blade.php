<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Connexion back-office | {{ config('shop.name') }}</title>
        <meta name="robots" content="noindex,nofollow">
        <script>
            let storedTheme = null;

            try {
                storedTheme = localStorage.getItem('theme');
            } catch (error) {
                storedTheme = null;
            }

            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if ((storedTheme !== 'light' && storedTheme !== 'dark') || storedTheme === null) {
                storedTheme = systemPrefersDark ? 'dark' : 'light';
            }

            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="theme-page surface-transition min-h-screen bg-cream text-cocoa antialiased dark:bg-ink dark:text-cream">
        <main class="admin-shell-backdrop grid min-h-screen lg:grid-cols-[1fr_520px]">
            <section class="relative hidden overflow-hidden bg-forest p-10 text-white dark:bg-[#111111] lg:block">
                <div class="relative flex h-full flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-14 w-14 place-items-center rounded-full bg-white text-sm font-black text-forest">DF</span>
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.24em]">{{ config('shop.name') }}</p>
                                <p class="text-sm text-white/65">Back-office commerce</p>
                            </div>
                        </div>
                        <div class="mt-20 max-w-xl">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-meadow">Admin office</p>
                            <h1 class="mt-4 text-5xl font-black leading-tight">Piloter le catalogue, le stock et les operations avec clarte.</h1>
                            <p class="mt-5 text-base leading-8 text-white/70">Un espace dedie a l equipe {{ config('shop.name') }} pour suivre les alertes, verifier les donnees API et garder une vue propre sur les priorites du jour.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-2xl font-black">API</p>
                            <p class="mt-1 text-xs text-white/60">Admin</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-2xl font-black">Stock</p>
                            <p class="mt-1 text-xs text-white/60">Alertes</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-2xl font-black">Audit</p>
                            <p class="mt-1 text-xs text-white/60">Journal</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md rounded-2xl border border-leaf/10 bg-white p-6 shadow-2xl shadow-black/5 dark:border-white/10 dark:bg-white/5 sm:p-8">
                    <div class="mb-8 text-center">
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-forest text-sm font-black text-white dark:bg-meadow dark:text-ink">DF</span>
                        <p class="mt-5 admin-kicker">Back-office</p>
                        <h1 class="mt-2 text-3xl font-black text-cocoa dark:text-cream">Connexion admin</h1>
                        <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">Utilisez un compte staff ou administrateur.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store', ['locale' => $locale]) }}" class="space-y-4">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-bold text-cocoa dark:text-cream">Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 admin-input">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-cocoa dark:text-cream">Mot de passe</span>
                            <input type="password" name="password" required autocomplete="current-password" class="mt-2 admin-input">
                        </label>
                        <button type="submit" class="admin-btn mt-2 w-full">
                            Entrer dans l admin
                        </button>
                    </form>

                    <div class="mt-5 grid grid-cols-[1fr_auto] gap-3">
                        <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="admin-btn-secondary">
                            Retour boutique
                        </a>
                        <button type="button" data-theme-toggle class="admin-icon-btn" aria-label="Changer le theme">
                            <svg data-theme-icon="light" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4" /><path d="M12 2v2" /><path d="M12 20v2" /><path d="M4.93 4.93l1.41 1.41" /><path d="m17.66 17.66 1.41 1.41" /><path d="M2 12h2" /><path d="M20 12h2" /><path d="m6.34 17.66-1.41 1.41" /><path d="m19.07 4.93-1.41 1.41" /></svg>
                            <svg data-theme-icon="dark" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.99 12.44A8.99 8.99 0 1 1 11.56 3a7 7 0 0 0 9.43 9.44Z" /></svg>
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
