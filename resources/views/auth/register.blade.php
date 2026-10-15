<x-layouts::app>
	<x-slot:title>Konto erstellen</x-slot:title>

	<div class="max-w-xs">
		<x-page-header title="Konto erstellen" />
		<p class="mt-2 text-base/6 text-pretty text-ink/60">Schon dabei? <a href="{{ route('login') }}" class="font-medium text-red-dark underline decoration-red/30 underline-offset-2 hover:decoration-red">Einloggen</a></p>

		<form method="POST" action="{{ route('register') }}" class="mt-8 flex flex-col gap-y-4">
			@csrf
			<div class="flex flex-col gap-y-2">
				<x-form.input label="Name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="60" placeholder="z.B. Pippo" autofocus required />
				<p class="text-sm/6 text-pretty text-ink/55">Sehen andere Fans erst, wenn eine Übergabe zugesagt ist.</p>
			</div>
			<x-form.input label="E-Mail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required />
			<x-form.input label="Passwort" hint="(mind. 8 Zeichen)" name="password" type="password" autocomplete="new-password" minlength="8" required />
			<x-form.input label="Passwort wiederholen" name="password_confirmation" type="password" autocomplete="new-password" required />
			<x-button type="submit" class="mt-4">Konto erstellen</x-button>
		</form>
	</div>
</x-layouts::app>
