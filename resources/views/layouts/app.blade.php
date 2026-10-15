<!DOCTYPE html>
<html lang="de-CH">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#120a01">

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-dvh bg-paper font-sans text-ink antialiased">
        <header class="bg-ink text-white">
            <div class="mx-auto flex max-w-xl items-center gap-3 px-5 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <x-logo class="size-10 -rotate-6" />
                    <span class="text-lg font-extrabold tracking-tight">Got a ticket?</span>
                </a>
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
