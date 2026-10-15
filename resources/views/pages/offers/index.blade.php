<?php

use App\Actions\Claims\RequestSeats;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Fixture;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Ich bruuch eis')] class extends Component
{
    #[Url(as: 'meine')]
    public bool $onlyMySectors = false;

    /**
     * Upcoming fixtures with their open offers.
     *
     * @return Collection<int, Fixture>
     */
    #[Computed]
    public function fixtures(): Collection
    {
        $sectorIds = $this->mySectorIds();

        return Fixture::query()
            ->where('kickoff_at', '>', now())
            ->orderBy('kickoff_at')
            ->with(['offers' => fn ($query) => $query
                ->where('status', OfferStatus::Open)
                ->when(auth()->check(), fn ($query) => $query->where('user_id', '!=', auth()->id()))
                ->when($this->onlyMySectors && $sectorIds !== [], fn ($query) => $query->whereIn('sector_id', $sectorIds))
                ->with(['sector', 'giver', 'claims'])
                ->oldest(),
            ])
            ->get()
            ->each(fn (Fixture $fixture) => $fixture->setRelation(
                'offers',
                $fixture->offers->filter(fn (Offer $offer) => $offer->availableSeats() > 0)->values(),
            ));
    }

    /**
     * The current user's claims, keyed by offer id.
     *
     * @return Collection<int, Claim>
     */
    #[Computed]
    public function myClaims(): Collection
    {
        return auth()->check() ? auth()->user()->claims()->get()->keyBy('offer_id') : new Collection;
    }

    /**
     * @return list<int>
     */
    public function mySectorIds(): array
    {
        return auth()->check() ? auth()->user()->sectors()->pluck('sectors.id')->all() : [];
    }

    public function request(int $offerId, RequestSeats $requestSeats, int $seats = 1): void
    {
        if (auth()->guest()) {
            session()->put('url.intended', route('offers.index').'#offer-'.$offerId);
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $offer = Offer::findOrFail($offerId);

        try {
            $requestSeats->handle(auth()->user(), $offer, $seats);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->dispatch('toast', message: $exception->validator->errors()->first(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: "Angefragt! {$offer->giver->displayName()} wird benachrichtigt.");

        unset($this->myClaims, $this->fixtures);
    }
};
?>

<div x-data="{ pick: null, seats: 1 }">
    <x-page-header title="Ich bruuch eis" description="Diese Plätze geben Fans weiter, die selbst nicht können. Kostenlos oder zum Originalpreis, direkt von Fan zu Fan." />

    @auth
        @if ($this->mySectorIds() === [])
            <a href="{{ route('settings') }}" wire:navigate class="mt-6 flex items-center gap-x-4 rounded-xl bg-red/6 p-4 hover:bg-red/10">
                <x-phosphor-bell-light class="size-6 shrink-0 text-red" />
                <span class="flex-1">
                    <span class="block font-semibold">Nie mehr einen freien Platz verpassen</span>
                    <span class="block text-base/6 text-ink/60">Wähle deine Sektoren und aktiviere Benachrichtigungen.</span>
                </span>
                <x-phosphor-caret-right class="size-5 shrink-0 text-ink/40" />
            </a>
        @endif
    @else
        <a href="{{ route('login') }}" wire:navigate class="mt-6 flex items-center gap-x-4 rounded-xl bg-paper p-4 hover:bg-ink/5">
            <x-phosphor-bell-light class="size-6 shrink-0 text-red" />
            <span class="flex-1">
                <span class="block font-semibold">Benachrichtigt werden, wenn etwas frei wird</span>
                <span class="block text-base/6 text-ink/60">Einloggen und Sektoren wählen.</span>
            </span>
            <x-phosphor-caret-right class="size-5 shrink-0 text-ink/40" />
        </a>
    @endauth

    @if (auth()->check() && $this->mySectorIds() !== [])
        <div class="mt-12 flex items-center justify-end gap-x-3">
            <p class="text-sm/6 text-ink/70">
                <label for="only-my-sectors">Nur meine</label>
                <a href="{{ route('settings') }}" wire:navigate class="underline decoration-ink/20 underline-offset-2 hover:decoration-red">Sektoren</a>
                <label for="only-my-sectors">anzeigen</label>
            </p>
            <x-form.toggle id="only-my-sectors" name="only_my_sectors" wire:model.live="onlyMySectors" />
        </div>
    @endif

    <div @class(['flex flex-col gap-y-8', 'mt-4' => auth()->check() && $this->mySectorIds() !== [], 'mt-12' => ! (auth()->check() && $this->mySectorIds() !== [])])>
        @forelse ($this->fixtures as $fixture)
            <section wire:key="fixture-{{ $fixture->id }}" aria-labelledby="fixture-{{ $fixture->id }}-title" class="divide-y divide-ink/8 overflow-hidden rounded-xl ring-1 ring-ink/10">
                <h2 id="fixture-{{ $fixture->id }}-title" class="bg-red/4 p-4 sm:p-6">
                    <x-fixture :fixture="$fixture" size="lg" />
                </h2>

                <div class="divide-y divide-ink/8">
                    @forelse ($fixture->offers as $offer)
                        @php
                            $available = $offer->availableSeats();
                            $myClaim = $this->myClaims->get($offer->id);
                            $hasActiveClaim = $myClaim && in_array($myClaim->status, \App\Enums\ClaimStatus::active(), true);
                            $record = $offer->giver->reliability();
                        @endphp
                        <article id="offer-{{ $offer->id }}" wire:key="offer-{{ $offer->id }}" aria-label="{{ $offer->sector->label() }}, {{ $available }} frei" class="scroll-mt-24 p-4 target:bg-red/4 sm:p-6">
                            <div class="flex items-start justify-between gap-x-3">
                                <div>
                                    <p class="font-semibold">{{ $offer->sector->label() }}</p>
                                    <p class="text-sm/6 text-ink/55">
                                        von {{ $offer->giver->displayName() }}@if ($record['given'] > 0) · {{ $record['given'] }}× weitergegeben @endif
                                    </p>
                                    @if ($offer->note)
                                        <p class="mt-1 text-base/6 text-ink/70">«{{ $offer->note }}»</p>
                                    @endif
                                </div>
                                <x-badge color="red" class="tabular-nums">{{ $available }} frei</x-badge>
                            </div>

                            @if ($hasActiveClaim)
                                <div class="mt-4 flex items-center justify-between gap-x-3 rounded-lg bg-paper px-3 py-2">
                                    <p class="flex items-center gap-x-2 text-base/6 font-medium"><x-phosphor-check-circle class="size-5 text-red" /> Angefragt</p>
                                    <x-button variant="plain" size="sm" :href="route('dashboard')" wire:navigate>Status ansehen</x-button>
                                </div>
                            @else
                                @auth
                                    <x-button variant="soft" size="sm" class="mt-4" aria-haspopup="dialog"
                                        aria-label="Plätze anfragen: {{ $offer->sector->label() }} gegen {{ $fixture->opponent }}"
                                        x-on:click="pick = {{ Js::from([
                                            'id' => $offer->id,
                                            'available' => $available,
                                            'date' => $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm'),
                                            'opponent' => $fixture->opponent,
                                            'sector' => $offer->sector->label(),
                                            'giver' => $offer->giver->displayName(),
                                        ]) }}; seats = 1; $refs.requestDialog.showModal()">Ich gang! Anfragen</x-button>
                                @else
                                    <x-button variant="soft" size="sm" class="mt-4" wire:click="request({{ $offer->id }})">Ich gang! Anfragen</x-button>
                                @endauth
                            @endif
                        </article>
                    @empty
                        <p class="p-4 text-base/6 text-red sm:p-6">Aktuell nichts frei{{ $onlyMySectors && $this->mySectorIds() !== [] ? ' in deinen Sektoren' : '' }}.</p>
                    @endforelse
                </div>
            </section>
        @empty
            <p class="rounded-xl bg-paper p-6 text-ink/60">Aktuell sind keine Heimspiele erfasst.</p>
        @endforelse
    </div>

    <x-dialog x-ref="requestDialog" x-on:click.self="$el.close()" x-on:close="pick = null" aria-labelledby="request-dialog-title" aria-describedby="request-dialog-note">
        <template x-if="pick">
            <div>
                <h2 id="request-dialog-title" class="text-lg font-semibold">Plätze anfragen</h2>

                <div class="mt-4 rounded-xl bg-red/4 p-4">
                    <p class="text-sm/6 font-medium text-red tabular-nums" x-text="pick.date"></p>
                    <p class="font-semibold" x-text="pick.opponent"></p>
                    <p class="mt-1 text-sm/6 text-ink/60"><span x-text="pick.sector"></span> · von <span x-text="pick.giver"></span></p>
                </div>

                <fieldset class="mt-6" x-show="pick.available > 1">
                    <legend class="text-base/7 font-semibold">Wie viele Plätze?</legend>
                    <div class="mt-3 grid grid-cols-4 gap-4">
                        <template x-for="count in pick.available" x-bind:key="count">
                            <label class="flex items-center justify-center rounded-lg py-2 text-base/6 font-semibold tabular-nums ring-1 ring-ink/10 hover:ring-ink/20 has-checked:bg-red/4 has-checked:ring-2 has-checked:ring-red has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-red">
                                <input type="radio" name="request_seats" class="sr-only" x-bind:value="count" x-model.number="seats" x-bind:aria-label="count === 1 ? '1 Platz' : count + ' Plätze'">
                                <span x-text="count"></span>
                            </label>
                        </template>
                    </div>
                </fieldset>

                <p id="request-dialog-note" class="mt-6 text-sm/5 text-pretty text-ink/60">Mit der Anfrage sagst du zu, dass du wirklich gehst. Wer nicht erscheint, lässt einen Platz leer. Bei Saisonkarten zählt zudem jeder Matchbesuch für die 70%-Regel.</p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button variant="neutral" x-on:click="$refs.requestDialog.close()">Abbrechen</x-button>
                    <x-button x-on:click="$wire.request(pick.id, seats); $refs.requestDialog.close()">Bestätigen</x-button>
                </div>
            </div>
        </template>
    </x-dialog>
</div>
