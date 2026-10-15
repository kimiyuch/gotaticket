<x-layouts::app>
	<x-slot:title>Neues Passwort</x-slot:title>

	<div class="max-w-xs">
		<x-page-header title="Neues Passwort" description="Wähle ein Passwort mit mindestens 8 Zeichen." />

		<form method="POST" action="{{ route('password.update') }}" class="mt-8 flex flex-col gap-y-4">
			@csrf
			<input type="hidden" name="token" value="{{ $request->route('token') }}">
			<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="email" required />
			<x-form.input label="Neues Passwort" name="password" type="password" autocomplete="new-password" minlength="8" autofocus required />
			<x-form.input label="Passwort wiederholen" name="password_confirmation" type="password" autocomplete="new-password" required />
			<x-button type="submit" class="mt-4">Passwort speichern</x-button>
		</form>
	</div>
</x-layouts::app>
