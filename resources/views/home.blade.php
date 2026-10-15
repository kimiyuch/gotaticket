<x-layouts::app>
    <section>
        <p class="text-base/7 font-medium text-red">Saisonkarte, aber keine Zeit?</p>
        <h1 class="mt-2 max-w-[20ch] text-4xl font-semibold tracking-tight text-balance sm:text-5xl">Du chasch nöd? Öpper anders chunnt gern.</h1>
        <p class="mt-4 max-w-[48ch] text-lg text-pretty text-ink/60">Gib deinen Platz an einen Fan weiter, der ein Ticket sucht. Kostenlos, ohne Zwischenhandel, direkt von Fan zu Fan.</p>
    </section>

    <div class="mt-10 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('offers.create') }}" class="group flex flex-col rounded-2xl bg-red p-5 text-white hover:bg-red-dark focus-visible:outline-2 focus-visible:outline-offset-2">
            <x-heroicon-o-plus-circle class="size-7 text-white/80" />
            <p class="mt-6 text-xl font-semibold">Ich han eis</p>
            <p class="mt-1 text-base/6 text-white/80">Platz für einen Match freigeben.</p>
        </a>
        <a href="{{ route('offers.index') }}" class="group flex flex-col rounded-2xl bg-white p-5 ring-1 ring-ink/10 hover:ring-ink/20 focus-visible:outline-2 focus-visible:outline-offset-2">
            <x-heroicon-o-ticket class="size-7 text-red" />
            <p class="mt-6 text-xl font-semibold">Ich bruuch eis</p>
            <p class="mt-1 text-base/6 text-ink/60">Freie Plätze ansehen und benachrichtigt werden.</p>
        </a>
    </div>

    <section class="mt-16 border-t border-ink/8 pt-10">
        <h2 class="text-xl font-semibold tracking-tight">So funktioniert's</h2>
        <ol class="mt-6 grid gap-6 sm:grid-cols-3">
            @foreach ([
                ['Freigeben', 'Du kannst an einem Match nicht? Gib deinen Platz in 20 Sekunden frei.'],
                ['Zusagen', 'Ein Fan meldet sich, du sagst zu. Erst dann seht ihr eure Kontaktdaten.'],
                ['Weitergeben', 'Du schickst das Ticket direkt. Dein Platz bleibt nicht leer.'],
            ] as [$step, $text])
                <li>
                    <p class="text-sm/6 font-medium text-red tabular-nums">0{{ $loop->iteration }}</p>
                    <p class="mt-1 font-semibold">{{ $step }}</p>
                    <p class="mt-1 text-base/6 text-pretty text-ink/60">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>
</x-layouts::app>
