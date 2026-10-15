<x-layouts::app>
	<x-slot:title>Einloggen</x-slot:title>

	<div class="max-w-xs">
		<x-page-header title="Einloggen" />
		<p class="mt-2 text-base/6 text-pretty text-ink/60">Neu hier? <a href="{{ route('register') }}" class="font-medium text-red-dark underline decoration-red/30 underline-offset-2 hover:decoration-red">Konto erstellen</a></p>

		@if (session('status'))
			<p class="mt-6 rounded-lg bg-paper px-4 py-3 text-base/6 text-pretty" role="status">{{ session('status') }}</p>
		@endif

		<form method="POST" action="{{ route('login') }}" class="mt-8 flex flex-col gap-y-4">
			@csrf
			<input type="hidden" name="remember" value="on">
			<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required />
			<div class="flex flex-col gap-y-2">
				<x-form.input label="Passwort" name="password" type="password" autocomplete="current-password" required />
				<a href="{{ route('password.request') }}" class="self-start text-sm/6 text-ink/60 underline decoration-ink/20 underline-offset-2 hover:text-ink">Passwort vergessen?</a>
			</div>
			<x-button type="submit" class="mt-4">Einloggen</x-button>
		</form>
	</div>
</x-layouts::app>
