<x-layouts::auth>
	<x-slot:title>Neues Passwort</x-slot:title>
	<x-slot:heading>Neues Passwort</x-slot:heading>
	<x-slot:description>Wähle ein Passwort mit mindestens 8 Zeichen.</x-slot:description>

	<form method="POST" action="{{ route('password.update') }}">
		@csrf
		<input type="hidden" name="token" value="{{ $request->route('token') }}">
		<div class="flex flex-col gap-y-4">
			<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="email" required />
			<x-form.input label="Neues Passwort" name="password" type="password" autocomplete="new-password" minlength="8" autofocus required />
			<x-form.input label="Passwort wiederholen" name="password_confirmation" type="password" autocomplete="new-password" required />
		</div>
		<x-button type="submit" class="mt-8 w-full">Passwort speichern</x-button>
	</form>
</x-layouts::auth>
