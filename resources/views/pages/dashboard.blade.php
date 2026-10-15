<?php

use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Meine Tickets')] class extends Component
{
    /**
     * @return Collection<int, Offer>
     */
    #[Computed]
    public function offers(): Collection
    {
        return auth()->user()->offers()
            ->with(['fixture', 'sector'])
            ->whereHas('fixture', fn ($query) => $query->upcoming())
            ->where('status', '!=', OfferStatus::Withdrawn)
            ->latest()
            ->get();
    }

    public function withdraw(int $offerId): void
    {
        $offer = auth()->user()->offers()->findOrFail($offerId);

        abort_unless($offer->status === OfferStatus::Open, 403);

        DB::transaction(function () use ($offer): void {
            $offer->claims()->where('status', ClaimStatus::Requested)->update(['status' => ClaimStatus::Declined]);
            $offer->update(['status' => OfferStatus::Withdrawn]);
        });

        unset($this->offers);
    }
};
?>

<div class="space-y-8">
    @if (session('status'))
        <p class="rounded-2xl bg-ink px-5 py-4 font-medium text-white">{{ session('status') }}</p>
    @endif

    <div class="flex items-end justify-between gap-4">
        <h1 class="text-3xl font-extrabold tracking-tight">Meine Tickets</h1>
        <a href="{{ route('offers.create') }}" wire:navigate class="shrink-0 whitespace-nowrap rounded-xl bg-red px-4 py-2 font-bold text-white hover:bg-red-dark">+ Platz freigeben</a>
    </div>

    <section class="space-y-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink/50">Freigegeben</h2>

        @forelse ($this->offers as $offer)
            <article wire:key="offer-{{ $offer->id }}" class="rounded-2xl bg-white p-4 ring-1 ring-line">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-bold">{{ $offer->fixture->opponent }}</p>
                        <p class="text-sm text-ink/60">{{ $offer->fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }}</p>
                        <p class="mt-1 text-sm">{{ $offer->seats }} × {{ $offer->sector->label() }}</p>
                        @if ($offer->note)
                            <p class="mt-1 text-sm text-ink/60">«{{ $offer->note }}»</p>
                        @endif
                    </div>
                    <span @class([
                        'shrink-0 rounded-full px-3 py-1 text-xs font-bold',
                        'bg-red/10 text-red' => $offer->status === OfferStatus::Open,
                        'bg-ink text-white' => $offer->status === OfferStatus::Taken,
                    ])>{{ $offer->status === OfferStatus::Open ? 'Offen' : 'Vergeben' }}</span>
                </div>

                @if ($offer->status === OfferStatus::Open)
                    <button type="button" wire:click="withdraw({{ $offer->id }})" wire:confirm="Freigabe wirklich zurückziehen?" class="mt-3 text-sm font-semibold text-ink/60 underline hover:text-red">
                        Zurückziehen
                    </button>
                @endif
            </article>
        @empty
            <p class="rounded-2xl bg-white p-5 text-ink/60 ring-1 ring-line">Du hast aktuell keine Plätze freigegeben.</p>
        @endforelse
    </section>
</div>
