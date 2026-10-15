<!DOCTYPE html>
<html lang="de-CH" class="bg-white antialiased sm:bg-neutral-50">
	<head>
		@include('layouts.partials.head')
	</head>
	<body class="min-h-dvh font-sans text-ink">
		<div class="isolate flex min-h-dvh flex-col items-center px-4 py-12 sm:justify-center sm:px-6">
			<main id="content" class="w-full max-w-sm sm:rounded-2xl sm:bg-white sm:p-10 sm:shadow-sm sm:ring-1 sm:ring-ink/5">
				<h1 class="text-2xl font-semibold tracking-tight text-balance">{{ $heading }}</h1>
				@isset($description)
					<p class="mt-2 text-base/6 text-pretty text-ink/60">{{ $description }}</p>
				@endisset

				<div class="mt-8">
					{{ $slot }}
				</div>
			</main>

			<p class="mt-6 w-full max-w-sm text-sm/6">
				<a href="{{ route('home') }}" class="inline-flex items-center gap-x-1.5 text-ink/55 hover:text-ink">
					<x-phosphor-arrow-left class="size-4 shrink-0" />
					Zurück zur Startseite
				</a>
			</p>
		</div>

		<x-toasts />

		@livewireScripts
	</body>
</html>
