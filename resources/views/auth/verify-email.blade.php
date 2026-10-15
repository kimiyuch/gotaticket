<x-layouts::app>
	<x-slot:title>E-Mail bestätigen</x-slot:title>

	<div class="max-w-sm">
		<x-page-header title="E-Mail bestätigen" />

		<div class="mt-8 rounded-xl bg-paper p-6">
			<x-phosphor-envelope-simple-light class="size-7 text-red" />
			<p class="mt-4 font-semibold">Fast geschafft.</p>
			<p class="mt-1 text-base/6 text-pretty text-ink/60">Wir haben dir einen Link an <strong class="font-medium text-ink">{{ auth()->user()->email }}</strong> geschickt. Klick darauf, dann kannst du Plätze freigeben und anfragen.</p>

			@if (session('status') === 'verification-link-sent')
				<p class="mt-4 text-base/6 font-medium text-red-dark" role="status">Ein neuer Link ist unterwegs.</p>
			@endif
		</div>

		<div class="mt-8 flex flex-wrap items-center gap-3">
			<form method="POST" action="{{ route('verification.send') }}">
				@csrf
				<x-button type="submit" variant="soft" size="sm">Link nochmals schicken</x-button>
			</form>
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<x-button type="submit" variant="plain" size="sm">Abmelden</x-button>
			</form>
		</div>
	</div>
</x-layouts::app>
