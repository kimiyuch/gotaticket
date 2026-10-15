<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<meta name="apple-mobile-web-app-title" content="Got a ticket?">

<x-seo :title="$title ?? null" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
