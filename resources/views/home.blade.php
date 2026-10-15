<x-layouts::app>
	<section class="grid items-center gap-8 sm:grid-cols-[3fr_2fr]">
		<div class="sm:order-1">
			<p class="text-base/7 font-medium text-red">Ticket, aber keine Zeit?</p>
			<h1 class="mt-2 max-w-[20ch] text-4xl font-semibold tracking-tight text-balance sm:text-5xl">Du chasch nöd? Öpper anders chunnt gern.</h1>
			<p class="mt-4 max-w-[48ch] text-lg text-pretty text-ink/60">Gib deinen Platz an einen Fan weiter, der ein Ticket sucht. Kostenlos, ohne Zwischenhandel, direkt von Fan zu Fan.</p>
			<div class="mt-8 flex flex-wrap gap-3">
				<x-button :href="route('offers.create')">Ich han eis</x-button>
				<x-button variant="neutral" :href="route('offers.index')">Ich bruuch eis</x-button>
			</div>
		</div>
		<div class="flex max-sm:-order-1 sm:order-2 sm:justify-center">
			<x-fcw-crest label="FC Winterthur" class="size-24 sm:size-48" />
		</div>
	</section>

	<section class="mt-16 border-t border-ink/8 pt-12">
		<h2 class="text-xl font-semibold tracking-tight">So funktioniert's</h2>

		<div class="mt-8 grid gap-12 sm:grid-cols-2 sm:gap-8">
			@foreach ([
				'Du hast eins' => [
					['Freigeben', 'Match, Sektor, Anzahl Plätze. Dauert keine 20 Sekunden.'],
					['Zusagen', 'Fans melden sich, du sagst zu. Erst dann seht ihr eure Kontaktdaten.'],
					['Ticket schicken', 'Direkt per E-Mail, Wallet oder Ticketportal. Dein Platz bleibt nicht leer.'],
				],
				'Du suchst eins' => [
					['Sektoren wählen', 'Aktiviere Push und wir melden uns, sobald dort etwas frei wird.'],
					['Anfragen', 'Mit «Ich gang!» sagst du verbindlich zu, dass du auch wirklich hingehst.'],
					['Ticket erhalten', 'Direkt vom Fan, der nicht kann. Kostenlos oder zum Originalpreis.'],
				],
			] as $role => $steps)
				<div>
					<h3 class="font-semibold text-red">{{ $role }}</h3>
					<ol class="mt-4 flex flex-col gap-y-6">
						@foreach ($steps as [$step, $text])
							<li class="flex gap-x-4">
								<span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-red/8 text-sm/6 font-semibold text-red-dark tabular-nums" aria-hidden="true">{{ $loop->iteration }}</span>
								<div>
									<p class="font-semibold">{{ $step }}</p>
									<p class="mt-1 text-base/6 text-pretty text-ink/60">{{ $text }}</p>
								</div>
							</li>
						@endforeach
					</ol>
				</div>
			@endforeach
		</div>
	</section>
</x-layouts::app>
