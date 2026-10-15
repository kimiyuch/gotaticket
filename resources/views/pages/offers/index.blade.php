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

    /** @var array<int, int> Seats the seeker wants, keyed by offer id */
    public array $seats = [];

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

    public function request(int $offerId, RequestSeats $requestSeats): void
    {
        if (auth()->guest()) {
            session()->put('url.intended', route('offers.index').'#offer-'.$offerId);
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $offer = Offer::findOrFail($offerId);

        try {
            $requestSeats->handle(auth()->user(), $offer, (int) ($this->seats[$offerId] ?? 1));
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->dispatch('toast', message: $exception->validator->errors()->first(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: "Angefragt! {$offer->giver->displayName()} wird benachrichtigt.");

        unset($this->myClaims, $this->fixtures);
    }
};
?>

<div>
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
            </p>
            <x-form.toggle id="only-my-sectors" name="only_my_sectors" wire:model.live="onlyMySectors" />
        </div>
    @endif

    <div @class(['flex flex-col gap-y-8', 'mt-4' => auth()->check() && $this->mySectorIds() !== [], 'mt-12' => ! (auth()->check() && $this->mySectorIds() !== [])])>
        @forelse ($this->fixtures as $fixture)
            <section wire:key="fixture-{{ $fixture->id }}" class="divide-y divide-ink/8 overflow-hidden rounded-xl ring-1 ring-ink/10">
                <h2 class="bg-red/4 p-4 sm:p-6">
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
                        <article id="offer-{{ $offer->id }}" wire:key="offer-{{ $offer->id }}" class="scroll-mt-24 p-4 target:bg-red/4 sm:p-6">
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
                                <div class="mt-4 flex items-center gap-x-2">
                                    @if ($available > 1)
                                        <div class="inline-grid grid-cols-[1fr_--spacing(8)] rounded-lg ring-1 ring-ink/15">
                                            <select name="seats-{{ $offer->id }}" wire:model="seats.{{ $offer->id }}" aria-label="Anzahl Plätze" class="col-span-full row-start-1 appearance-none rounded-lg bg-white py-1 pr-8 pl-3 text-base/6 tabular-nums focus:outline-2 focus:-outline-offset-1 focus:outline-red">
                                                @foreach (range(1, $available) as $count)
                                                    <option value="{{ $count }}">{{ $count }} {{ $count === 1 ? 'Platz' : 'Plätze' }}</option>
                                                @endforeach
                                            </select>
                                            <svg viewBox="0 0 8 5" width="8" height="5" fill="none" class="pointer-events-none col-start-2 row-start-1 place-self-center text-ink/50"><path d="M.5.5 4 4 7.5.5" stroke="currentcolor" /></svg>
                                        </div>
                                    @endif
                                    <x-button variant="soft" size="sm" wire:click="request({{ $offer->id }})" wire:loading.attr="disabled">Ich gang! Anfragen</x-button>
                                </div>
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

    <p class="mt-12 text-sm/6 text-pretty text-ink/45">Mit einer Anfrage sagst du zu, dass du wirklich gehst. Wer nicht erscheint, lässt einen Platz leer. Bei Saisonkarten zählt zudem jeder Matchbesuch für die 70%-Regel.</p>
</div>
