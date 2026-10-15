<x-layouts::app>
	<x-slot:title>Passwort vergessen</x-slot:title>

	<div class="max-w-xs">
		<x-page-header title="Passwort vergessen" description="Gib deine E-Mail ein. Wir schicken dir einen Link, mit dem du ein neues Passwort setzt." />

		@if (session('status'))
			<div class="mt-8 rounded-xl bg-paper p-6" role="status">
				<x-phosphor-envelope-simple-light class="size-7 text-red" />
				<p class="mt-4 font-semibold">Check dein Postfach.</p>
				<p class="mt-1 text-base/6 text-pretty text-ink/60">{{ session('status') }} Der Link ist 60 Minuten gültig.</p>
			</div>
		@else
			<form method="POST" action="{{ route('password.email') }}" class="mt-8 flex flex-col gap-y-4">
				@csrf
				<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required />
				<x-button type="submit" class="mt-4">Link schicken</x-button>
			</form>
		@endif

		<p class="mt-8 text-base/6"><a href="{{ route('login') }}" class="text-ink/60 underline decoration-ink/20 underline-offset-2 hover:text-ink">Zurück zum Login</a></p>
	</div>
</x-layouts::app>
