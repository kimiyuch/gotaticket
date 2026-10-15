<x-layouts::app>
    <section>
        <p class="text-base/7 font-medium text-red">Saisonkarte, aber keine Zeit?</p>
        <h1 class="mt-2 max-w-[20ch] text-4xl font-semibold tracking-tight text-balance sm:text-5xl">Du chasch nöd? Öpper anders chunnt gern.</h1>
        <p class="mt-4 max-w-[48ch] text-lg text-pretty text-ink/60">Gib deinen Platz an einen Fan weiter, der ein Ticket sucht. Kostenlos, ohne Zwischenhandel, direkt von Fan zu Fan.</p>
    </section>

    <div class="mt-10 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('offers.create') }}" class="group flex flex-col rounded-2xl bg-red p-5 text-white hover:bg-red-dark focus-visible:outline-2 focus-visible:outline-offset-2">
            <x-phosphor-plus-circle-light class="size-7 text-white/80" />
            <p class="mt-6 text-xl font-semibold">Ich han eis</p>
            <p class="mt-1 text-base/6 text-white/80">Platz für einen Match freigeben.</p>
        </a>
        <a href="{{ route('offers.index') }}" class="group flex flex-col rounded-2xl bg-white p-5 ring-1 ring-ink/10 hover:ring-ink/20 focus-visible:outline-2 focus-visible:outline-offset-2">
            <x-phosphor-ticket-light class="size-7 text-red" />
            <p class="mt-6 text-xl font-semibold">Ich bruuch eis</p>
            <p class="mt-1 text-base/6 text-ink/60">Freie Plätze ansehen und benachrichtigt werden.</p>
        </a>
    </div>

    <section class="mt-16 border-t border-ink/8 pt-10">
        <h2 class="text-xl font-semibold tracking-tight">So funktioniert's</h2>

        <div class="mt-8 grid gap-10 sm:grid-cols-2 sm:gap-8">
            @foreach ([
                'Du hast eins' => [
                    ['Freigeben', 'Match, Sektor, Anzahl Plätze. Dauert keine 20 Sekunden.'],
                    ['Zusagen', 'Fans melden sich, du sagst zu. Erst dann seht ihr eure Kontaktdaten.'],
                    ['Ticket schicken', 'Direkt per E-Mail, Wallet oder Ticketportal. Dein Platz bleibt nicht leer.'],
                ],
                'Du suchst eins' => [
                    ['Sektoren wählen', 'Aktiviere Push und wir melden uns, sobald dort etwas frei wird.'],
                    ['Anfragen', 'Mit «Ich gang!» sagst du verbindlich zu, dass du auch wirklich hingehst.'],
                    ['Ticket erhalten', 'Direkt von der Saisonkarten-Besitzer:in. Kostenlos oder zum Originalpreis.'],
                ],
            ] as $role => $steps)
                <div>
                    <h3 class="font-semibold text-red">{{ $role }}</h3>
                    <ol class="mt-4 flex flex-col gap-y-5">
                        @foreach ($steps as [$step, $text])
                            <li class="flex gap-x-4">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-red/8 text-sm/6 font-semibold text-red-dark tabular-nums" aria-hidden="true">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="font-semibold">{{ $step }}</p>
                                    <p class="mt-0.5 text-base/6 text-pretty text-ink/60">{{ $text }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts::app>
