{{-- Standalone layout for error pages: no session, auth or Livewire, so it renders even when those fail. --}}
<!DOCTYPE html>
<html lang="de-CH" class="bg-white antialiased sm:bg-neutral-50">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
		<meta name="theme-color" content="#ffffff">
		<link rel="icon" href="/favicon.svg" type="image/svg+xml">
		<link rel="icon" href="/favicon.png" type="image/png">
		<meta name="robots" content="noindex, nofollow">
		<title>@yield('title') · {{ config('app.name') }}</title>
		@vite(['resources/css/app.css'])
	</head>
	<body class="min-h-dvh font-sans text-ink">
		<div class="flex min-h-dvh flex-col items-center px-4 py-12 sm:justify-center sm:px-6">
			<main class="w-full max-w-sm sm:rounded-2xl sm:bg-white sm:p-8 sm:shadow-sm sm:ring-1 sm:ring-ink/5">
				<p class="text-base/7 font-medium text-red tabular-nums">Fehler @yield('code')</p>
				<h1 class="mt-2 text-2xl font-semibold tracking-tight text-balance">@yield('title')</h1>
				<p class="mt-2 text-base/6 text-pretty text-ink/60">@yield('message')</p>
				<x-button href="/" class="mt-8 w-full">Zur Startseite</x-button>
			</main>
		</div>
	</body>
</html>
