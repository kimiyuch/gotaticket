<!DOCTYPE html>
<html lang="de-CH">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#120a01">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/favicon.png" type="image/png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <meta name="apple-mobile-web-app-title" content="Got a ticket?">

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-dvh bg-paper font-sans text-ink antialiased">
        <header class="bg-ink text-white">
            <div class="mx-auto flex max-w-xl items-center gap-3 px-5 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <x-logo class="size-10 -rotate-6" />
                    <span class="hidden text-lg font-extrabold tracking-tight min-[400px]:inline">Got a ticket?</span>
                </a>

                <nav class="ml-auto flex items-center gap-4 text-sm font-semibold">
                    <a href="{{ route('offers.index') }}" wire:navigate @class(['hover:text-white', 'text-white' => request()->routeIs('offers.index'), 'text-white/70' => ! request()->routeIs('offers.index')])>Freie Plätze</a>
                    @auth
                        <a href="{{ route('dashboard') }}" wire:navigate @class(['hover:text-white', 'text-white' => request()->routeIs('dashboard'), 'text-white/70' => ! request()->routeIs('dashboard')])>Meine Tickets</a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="text-white/70 hover:text-white">Login</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-xl px-5 py-8">
            {{ $slot }}
        </main>

        <footer class="mx-auto max-w-xl px-5 pb-10 text-xs text-ink/50">
            Von Fans, für Fans. Kein offizielles Angebot des FC Winterthur.
        </footer>

        @livewireScripts
    </body>
</html>
