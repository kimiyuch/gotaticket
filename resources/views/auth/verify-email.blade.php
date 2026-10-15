<x-layouts::auth>
	<x-slot:title>E-Mail bestätigen</x-slot:title>
	<x-slot:heading>E-Mail bestätigen</x-slot:heading>

	<div class="rounded-xl bg-paper p-6">
		<x-phosphor-envelope-simple-light class="size-7 text-red" />
		<p class="mt-4 font-semibold">Fast geschafft.</p>
		<p class="mt-1 text-base/6 text-pretty text-ink/60">Wir haben dir einen Link an <strong class="font-medium text-ink">{{ auth()->user()->email }}</strong> geschickt. Klick darauf, dann kannst du Plätze freigeben und anfragen.</p>

		@if (session('status') === 'verification-link-sent')
			<p class="mt-4 text-base/6 font-medium text-red-dark" role="status">Ein neuer Link ist unterwegs.</p>
		@endif
	</div>

	<form method="POST" action="{{ route('verification.send') }}">
		@csrf
		<x-button type="submit" class="mt-8 w-full">Link nochmals schicken</x-button>
	</form>

	<div class="mt-6 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 text-sm/6">
		<a href="{{ route('offers.index') }}" class="text-ink/60 underline decoration-ink/20 underline-offset-2 hover:text-ink">Freie Plätze ansehen</a>
		<form method="POST" action="{{ route('logout') }}">
			@csrf
			<button type="submit" class="text-ink/60 underline decoration-ink/20 underline-offset-2 hover:text-ink">Abmelden</button>
		</form>
	</div>
</x-layouts::auth>
