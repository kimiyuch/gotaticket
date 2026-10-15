<x-layouts::auth>
	<x-slot:title>Einloggen</x-slot:title>
	<x-slot:heading>Einloggen</x-slot:heading>

	@if (session('status'))
		<p class="mb-6 rounded-lg bg-paper px-4 py-3 text-base/6 text-pretty" role="status">{{ session('status') }}</p>
	@endif

	<form method="POST" action="{{ route('login') }}">
		@csrf
		<input type="hidden" name="remember" value="on">
		<div class="flex flex-col gap-y-4">
			<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required />
			<x-form.input label="Passwort" name="password" type="password" autocomplete="current-password" required />
		</div>
		<div class="mt-6 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 text-sm/6">
			<a href="{{ route('password.request') }}" class="text-ink/60 underline decoration-ink/20 underline-offset-2 hover:text-ink">Passwort vergessen?</a>
			<a href="{{ route('register') }}" class="font-medium text-red-dark underline decoration-red/30 underline-offset-2 hover:decoration-red">Konto erstellen</a>
		</div>
		<x-button type="submit" class="mt-8 w-full">Einloggen</x-button>
	</form>
</x-layouts::auth>
