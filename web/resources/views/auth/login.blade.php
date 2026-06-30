<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('home.account.auth.login_title') }} | {{ config('shop.name') }}</title>
        <meta name="robots" content="noindex,nofollow">
        <script>
            try {
                if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
            } catch (error) {}
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="store-page min-h-screen antialiased">
        <main class="grid min-h-screen place-items-center px-4 py-8">
            <div class="w-full max-w-md">
                <livewire:account.login-form :locale="$locale" />
            </div>
        </main>
        @livewireScripts
    </body>
</html>
