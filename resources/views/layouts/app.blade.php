<!DOCTYPE html>
<html lang="de-CH" class="bg-white antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#ffffff">
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
    <body class="min-h-dvh font-sans text-ink">
        <div class="isolate flex min-h-dvh flex-col">
            <header class="sticky top-0 z-10 border-b border-ink/8 bg-white/90 backdrop-blur-sm">
                <div class="mx-auto flex max-w-2xl items-center gap-x-6 px-4 py-2 sm:px-6">
                    <a href="{{ route('home') }}" wire:navigate aria-label="Homepage" class="-m-1 rounded-lg p-1">
                        <x-logo horizontal class="h-10 -rotate-2 sm:h-11" />
                        <span class="sr-only">Got a ticket?</span>
                    </a>

                    <nav class="ml-auto flex items-center gap-x-4 max-sm:hidden">
                        <x-nav-link route="offers.index">Freie Plätze</x-nav-link>
                        <x-nav-link route="offers.create">Platz freigeben</x-nav-link>
                        @auth
                            <x-nav-link route="dashboard">Meine Tickets</x-nav-link>
                        @else
                            <x-nav-link route="login">Login</x-nav-link>
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="mx-auto w-full max-w-2xl flex-1 px-4 pt-8 pb-28 sm:px-6 sm:pt-12 sm:pb-16">
                {{ $slot }}
            </main>

            <footer class="mx-auto w-full max-w-2xl px-4 pb-28 text-sm/6 text-ink/45 sm:px-6 sm:pb-12">
                <p>
                    Von Fans, für Fans. Kein offizielles Angebot des FC Winterthur.
                    Mit <x-phosphor-heart-fill class="inline size-4 -translate-y-px text-red" aria-label="Liebe" role="img" /> gemacht von <a href="https://kimiyu.ch" target="_blank" rel="noopener" class="underline decoration-ink/20 underline-offset-2 hover:text-ink">kimiyu.ch</a>
                </p>
            </footer>

            <nav class="fixed inset-x-0 bottom-0 z-10 flex border-t border-ink/8 bg-white/95 px-2 pt-1 pb-[max(--spacing(1),env(safe-area-inset-bottom))] backdrop-blur-sm sm:hidden">
                <x-tab-link route="offers.index" icon="ticket">Freie Plätze</x-tab-link>
                <x-tab-link route="offers.create" icon="plus-circle">Freigeben</x-tab-link>
                @auth
                    <x-tab-link route="dashboard" icon="user-circle">Meine Tickets</x-tab-link>
                @else
                    <x-tab-link route="login" icon="user-circle">Login</x-tab-link>
                @endauth
            </nav>
        </div>

        @livewireScripts
    </body>
</html>
