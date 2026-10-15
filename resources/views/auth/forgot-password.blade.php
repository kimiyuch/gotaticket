<x-layouts::auth>
	<x-slot:title>Passwort vergessen</x-slot:title>
	<x-slot:heading>Passwort vergessen</x-slot:heading>
	<x-slot:description>Wir schicken dir einen Link, mit dem du ein neues Passwort setzt.</x-slot:description>

	@if (session('status'))
		<div class="rounded-xl bg-neutral-100 p-6" role="status">
			<x-phosphor-envelope-simple-light class="size-7 text-red" />
			<p class="mt-4 font-semibold">Check dein Postfach.</p>
			<p class="mt-1 text-base/6 text-pretty text-ink/60">{{ session('status') }} Der Link ist 60 Minuten gültig.</p>
		</div>
	@else
		<form method="POST" action="{{ route('password.email') }}">
			@csrf
			<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required />
			<x-button type="submit" class="mt-8 w-full">Link schicken</x-button>
		</form>
	@endif

	<p class="mt-6 text-sm/6"><a href="{{ route('login') }}" class="text-ink/60 underline decoration-ink/20 underline-offset-2 hover:text-ink">Zurück zum Login</a></p>
</x-layouts::auth>
