<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Connexion admin | {{ config('shop.name') }}</title>
        <meta name="robots" content="noindex,nofollow">
        <script>
            try {
                if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
            } catch (error) {}
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="store-page min-h-screen antialiased">
        <main class="grid min-h-screen place-items-center px-4 py-8">
            <section class="form-card w-full max-w-md p-5 sm:p-6">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store', ['locale' => $locale]) }}" class="space-y-5">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-bold text-cocoa dark:text-cream">Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input-premium mt-2 w-full">
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-cocoa dark:text-cream">Mot de passe</span>
                        <input type="password" name="password" required autocomplete="current-password" class="input-premium mt-2 w-full">
                    </label>

                    <button type="submit" class="store-button w-full">Se connecter</button>
                </form>
            </section>
        </main>
    </body>
</html>
