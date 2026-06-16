<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Connexion back-office | Denetfils</title>
        <meta name="robots" content="noindex,nofollow">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f7f5ef] text-[#1f2a1c] antialiased">
        <main class="grid min-h-screen lg:grid-cols-[1fr_520px]">
            <section class="relative hidden overflow-hidden bg-[#12210f] p-10 text-white lg:block">
                <div class="absolute inset-0 opacity-70" style="background: radial-gradient(circle at 20% 20%, rgba(241,91,42,.32), transparent 28%), radial-gradient(circle at 80% 12%, rgba(142,217,87,.20), transparent 30%);"></div>
                <div class="relative flex h-full flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f15b2a] text-sm font-black">DF</span>
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.24em]">Denetfils</p>
                                <p class="text-sm text-white/55">Back-office commerce Europe</p>
                            </div>
                        </div>
                        <div class="mt-20 max-w-xl">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-[#8ed957]">Admin office</p>
                            <h1 class="mt-4 text-5xl font-black leading-tight">Piloter le catalogue, le stock et les operations avec clarte.</h1>
                            <p class="mt-5 text-base leading-8 text-white/68">Un espace dedie a l equipe DEN & FILS pour suivre les alertes, verifier les donnees API et garder une vue propre sur les priorites du jour.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-3xl bg-white/10 p-4">
                            <p class="text-2xl font-black">API</p>
                            <p class="mt-1 text-xs text-white/55">Consommation admin</p>
                        </div>
                        <div class="rounded-3xl bg-white/10 p-4">
                            <p class="text-2xl font-black">Stock</p>
                            <p class="mt-1 text-xs text-white/55">Alertes rapides</p>
                        </div>
                        <div class="rounded-3xl bg-white/10 p-4">
                            <p class="text-2xl font-black">Audit</p>
                            <p class="mt-1 text-xs text-white/55">Actions sensibles</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md rounded-3xl border border-black/5 bg-white p-6 shadow-2xl shadow-black/5 sm:p-8">
                    <div class="mb-8 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#12210f] text-sm font-black text-white">DF</span>
                        <p class="mt-5 text-xs font-black uppercase tracking-[0.24em] text-[#2f7d1b]">Back-office</p>
                        <h1 class="mt-2 text-3xl font-black text-[#1f2a1c]">Connexion admin</h1>
                        <p class="mt-2 text-sm leading-6 text-[#1f2a1c]/60">Utilisez un compte staff ou administrateur. Les comptes clients simples sont bloques ici.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store', ['locale' => $locale]) }}" class="space-y-4">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-bold text-[#1f2a1c]">Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 min-h-[48px] w-full rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none transition focus:border-[#2f7d1b] focus:ring-4 focus:ring-[#2f7d1b]/10">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-[#1f2a1c]">Mot de passe</span>
                            <input type="password" name="password" required autocomplete="current-password" class="mt-2 min-h-[48px] w-full rounded-2xl border border-black/10 bg-[#f7f5ef] px-4 text-sm font-semibold outline-none transition focus:border-[#2f7d1b] focus:ring-4 focus:ring-[#2f7d1b]/10">
                        </label>
                        <button type="submit" class="mt-2 flex min-h-[50px] w-full items-center justify-center rounded-2xl bg-[#f15b2a] px-5 text-sm font-black uppercase tracking-wide text-white shadow-lg shadow-[#f15b2a]/20 transition hover:bg-[#c94b26]">
                            Entrer dans l admin
                        </button>
                    </form>

                    <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="mt-5 flex min-h-[46px] items-center justify-center rounded-2xl border border-black/10 text-sm font-black text-[#1f2a1c]/70 transition hover:bg-[#f7f5ef]">
                        Retour boutique
                    </a>
                </div>
            </section>
        </main>
    </body>
</html>
