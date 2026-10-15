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
    #[Url(as: 'alle')]
    public bool $showAllSectors = false;

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
                ->when(! $this->showAllSectors && $sectorIds !== [], fn ($query) => $query->whereIn('sector_id', $sectorIds))
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
            $this->addError("offer.{$offerId}", $exception->validator->errors()->first());

            return;
        }

        unset($this->myClaims, $this->fixtures);
    }
};
?>

<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight">Ich bruuch eis</h1>
        <p class="mt-1 text-ink/70">Diese Plätze geben Saisonkarten-Besitzer:innen gerade weiter. Kostenlos oder zum Originalpreis, direkt von Fan zu Fan.</p>
    </div>

    @auth
        @if ($this->mySectorIds() === [])
            <a href="{{ route('settings') }}" wire:navigate class="block rounded-2xl bg-red px-5 py-4 text-white hover:bg-red-dark">
                <span class="block font-bold">Nie mehr einen freien Platz verpassen</span>
                <span class="block text-sm text-white/80">Wähle deine Sektoren und aktiviere Benachrichtigungen →</span>
            </a>
        @else
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" wire:model.live="showAllSectors" class="size-4 accent-red">
                Auch andere Sektoren anzeigen
            </label>
        @endif
    @else
        <a href="{{ route('login') }}" wire:navigate class="block rounded-2xl bg-ink px-5 py-4 text-white">
            <span class="block font-bold">Benachrichtigt werden, wenn etwas frei wird</span>
            <span class="block text-sm text-white/70">Einloggen und Sektoren wählen →</span>
        </a>
    @endauth

    @forelse ($this->fixtures as $fixture)
        <section wire:key="fixture-{{ $fixture->id }}" class="space-y-3">
            <h2>
                <span class="block text-xl font-extrabold">{{ $fixture->opponent }}</span>
                <span class="block text-sm text-ink/60">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }} · {{ $fixture->competition }}</span>
            </h2>

            @forelse ($fixture->offers as $offer)
                @php
                    $available = $offer->availableSeats();
                    $myClaim = $this->myClaims->get($offer->id);
                    $hasActiveClaim = $myClaim && in_array($myClaim->status, \App\Enums\ClaimStatus::active(), true);
                    $record = $offer->giver->reliability();
                @endphp
                <article id="offer-{{ $offer->id }}" wire:key="offer-{{ $offer->id }}" class="scroll-mt-6 rounded-2xl bg-white p-4 ring-1 ring-line target:ring-2 target:ring-red">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold">{{ $offer->sector->label() }}</p>
                            <p class="text-sm text-ink/60">
                                {{ $offer->giver->displayName() }}
                                @if ($record['given'] > 0) · {{ $record['given'] }}× weitergegeben @endif
                            </p>
                            @if ($offer->note)
                                <p class="mt-1 text-sm text-ink/60">«{{ $offer->note }}»</p>
                            @endif
                        </div>
                        <span class="shrink-0 rounded-full bg-red/10 px-3 py-1 text-xs font-bold text-red">{{ $available }} frei</span>
                    </div>

                    @if ($hasActiveClaim)
                        <p class="mt-3 rounded-xl bg-paper px-4 py-3 text-sm font-semibold">
                            Angefragt ✓ <a href="{{ route('dashboard') }}" wire:navigate class="font-normal text-ink/60 underline">Status ansehen</a>
                        </p>
                    @else
                        <div class="mt-3 flex items-center gap-2">
                            @if ($available > 1)
                                <select wire:model="seats.{{ $offer->id }}" class="rounded-xl border-0 bg-paper px-3 py-3 font-semibold ring-1 ring-line" aria-label="Anzahl Plätze">
                                    @foreach (range(1, $available) as $count)
                                        <option value="{{ $count }}">{{ $count }}×</option>
                                    @endforeach
                                </select>
                            @endif
                            <button type="button" wire:click="request({{ $offer->id }})" wire:loading.attr="disabled" class="flex-1 rounded-xl bg-red px-4 py-3 font-bold text-white hover:bg-red-dark disabled:opacity-60">
                                Ich gang! Anfragen
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-ink/50">Mit der Anfrage sagst du zu, dass du wirklich gehst. Wer nicht erscheint, kostet die Saisonkarten-Besitzer:in einen Matchbesuch.</p>
                    @endif
                    @error("offer.{$offer->id}") <p class="mt-2 text-sm font-medium text-red">{{ $message }}</p> @enderror
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-ink/20 px-4 py-3 text-sm text-ink/50">Aktuell nichts frei{{ ! $showAllSectors && $this->mySectorIds() !== [] ? ' in deinen Sektoren' : '' }}.</p>
            @endforelse
        </section>
    @empty
        <p class="rounded-2xl bg-white p-5 ring-1 ring-line">Aktuell sind keine Heimspiele erfasst.</p>
    @endforelse
</div>
