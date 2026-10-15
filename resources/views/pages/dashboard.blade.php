<?php

use App\Actions\Claims\AcceptClaim;
use App\Actions\Claims\CancelClaim;
use App\Actions\Claims\ConfirmReceipt;
use App\Actions\Claims\DeclineClaim;
use App\Actions\Claims\MarkTicketSent;
use App\Actions\Claims\ReportOutcome;
use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Meine Tickets')] class extends Component
{
    /**
     * Offers from fixtures that are upcoming or kicked off within the last two days (for reporting).
     *
     * @return Collection<int, Offer>
     */
    #[Computed]
    public function offers(): Collection
    {
        return auth()->user()->offers()
            ->with(['fixture', 'sector', 'claims' => fn ($query) => $query->whereIn('status', ClaimStatus::active())->with('seeker')])
            ->whereHas('fixture', fn (Builder $query) => $query->where('kickoff_at', '>', now()->subDays(2)))
            ->where('status', '!=', OfferStatus::Withdrawn)
            ->get()
            ->sortBy('fixture.kickoff_at');
    }

    /**
     * @return Collection<int, Claim>
     */
    #[Computed]
    public function claims(): Collection
    {
        return auth()->user()->claims()
            ->with(['offer.fixture', 'offer.sector', 'offer.giver'])
            ->whereHas('offer.fixture', fn (Builder $query) => $query->where('kickoff_at', '>', now()->subDays(2)))
            ->latest()
            ->get()
            ->sortBy('offer.fixture.kickoff_at');
    }

    public function withdraw(int $offerId): void
    {
        $offer = auth()->user()->offers()->findOrFail($offerId);

        abort_unless($offer->status === OfferStatus::Open, 403);

        DB::transaction(function () use ($offer): void {
            $offer->claims()->where('status', ClaimStatus::Requested)->update(['status' => ClaimStatus::Declined]);
            $offer->update(['status' => OfferStatus::Withdrawn]);
        });

        $this->refresh();
    }

    public function accept(int $claimId, AcceptClaim $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)));
    }

    public function decline(int $claimId, DeclineClaim $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)));
    }

    public function markSent(int $claimId, MarkTicketSent $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)));
    }

    public function confirmReceipt(int $claimId, ConfirmReceipt $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)));
    }

    public function cancel(int $claimId, CancelClaim $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)));
    }

    public function report(int $claimId, string $outcome, ReportOutcome $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId), ClaimOutcome::from($outcome)));
    }

    private function run(Closure $callback): void
    {
        try {
            $callback();
        } catch (ValidationException $exception) {
            $this->addError('action', $exception->validator->errors()->first());
        }

        $this->refresh();
    }

    private function refresh(): void
    {
        unset($this->offers, $this->claims);
    }
};
?>

<div class="space-y-10">
    @if (session('status'))
        <p class="rounded-2xl bg-ink px-5 py-4 font-medium text-white">{{ session('status') }}</p>
    @endif

    @error('action')
        <p class="rounded-2xl bg-red px-5 py-4 font-medium text-white">{{ $message }}</p>
    @enderror

    <div class="flex items-end justify-between gap-4">
        <h1 class="text-3xl font-extrabold tracking-tight">Meine Tickets</h1>
        <a href="{{ route('settings') }}" wire:navigate class="text-sm font-semibold text-ink/60 underline hover:text-red">Einstellungen</a>
    </div>

    {{-- As seeker --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink/50">Meine Anfragen</h2>
            <a href="{{ route('offers.index') }}" wire:navigate class="text-sm font-bold text-red">Freie Plätze →</a>
        </div>

        @forelse ($this->claims as $claim)
            @php($fixture = $claim->offer->fixture)
            <article wire:key="claim-{{ $claim->id }}" class="rounded-2xl bg-white p-4 ring-1 ring-line">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-bold">{{ $fixture->opponent }}</p>
                        <p class="text-sm text-ink/60">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }}</p>
                        <p class="mt-1 text-sm">{{ $claim->seats }} × {{ $claim->offer->sector->label() }}</p>
                    </div>
                    <x-claim-status :claim="$claim" />
                </div>

                @if ($claim->status === ClaimStatus::Requested)
                    <p class="mt-3 text-sm text-ink/60">Warte auf die Bestätigung von {{ $claim->offer->giver->displayName() }}.</p>
                @endif

                @if ($claim->status->revealsContact())
                    <x-contact :user="$claim->offer->giver" label="Ticket kommt von" />
                @endif

                @if ($fixture->kickoff_at->isFuture())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if (in_array($claim->status, [ClaimStatus::Accepted, ClaimStatus::Sent], true))
                            <button type="button" wire:click="confirmReceipt({{ $claim->id }})" class="rounded-xl bg-red px-4 py-2 font-bold text-white hover:bg-red-dark">Ticket erhalten</button>
                        @endif
                        @if (in_array($claim->status, [ClaimStatus::Requested, ClaimStatus::Accepted, ClaimStatus::Sent], true))
                            <button type="button" wire:click="cancel({{ $claim->id }})" wire:confirm="Kannst du wirklich nicht? Dann wird der Platz wieder freigegeben." class="rounded-xl px-4 py-2 font-semibold text-ink/60 ring-1 ring-line hover:text-red">Ich chan doch nöd</button>
                        @endif
                    </div>
                    @if ($claim->status === ClaimStatus::Received)
                        <p class="mt-3 text-sm text-ink/60">Viel Spass im Stadion! Falls du doch nicht kannst, gib das Ticket bitte selbst weiter.</p>
                    @endif
                @elseif (in_array($claim->status, [ClaimStatus::Sent, ClaimStatus::Received], true))
                    @if ($claim->outcome === ClaimOutcome::TicketInvalid)
                        <p class="mt-3 text-sm font-semibold text-red">Du hast gemeldet, dass das Ticket nicht funktioniert hat.</p>
                    @else
                        <button type="button" wire:click="report({{ $claim->id }}, 'ticket_invalid')" wire:confirm="Hat das Ticket am Eingang nicht funktioniert?" class="mt-3 text-sm font-semibold text-ink/60 underline hover:text-red">Ticket hat nicht funktioniert</button>
                    @endif
                @endif
            </article>
        @empty
            <p class="rounded-2xl bg-white p-5 text-ink/60 ring-1 ring-line">Noch keine Anfragen. <a href="{{ route('offers.index') }}" wire:navigate class="font-semibold text-red underline">Freie Plätze ansehen</a></p>
        @endforelse
    </section>

    {{-- As giver --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink/50">Freigegeben</h2>
            <a href="{{ route('offers.create') }}" wire:navigate class="text-sm font-bold text-red">+ Platz freigeben</a>
        </div>

        @forelse ($this->offers as $offer)
            @php($kickedOff = $offer->fixture->kickoff_at->isPast())
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
                    ])>{{ $offer->status === OfferStatus::Open ? $offer->availableSeats().' offen' : 'Vergeben' }}</span>
                </div>

                <div class="mt-3 space-y-3">
                    @foreach ($offer->claims as $claim)
                        @php($record = $claim->seeker->reliability())
                        <div wire:key="offer-claim-{{ $claim->id }}" class="border-t border-line pt-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $claim->seeker->displayName() }} · {{ $claim->seats }} {{ $claim->seats === 1 ? 'Platz' : 'Plätze' }}</p>
                                    <p class="text-sm text-ink/60">
                                        @if ($record['received'] === 0 && $record['no_shows'] === 0)
                                            Neu dabei
                                        @else
                                            {{ $record['received'] }}× dabei gewesen @if ($record['no_shows'] > 0) · <span class="font-semibold text-red">{{ $record['no_shows'] }}× nicht erschienen</span> @endif
                                        @endif
                                    </p>
                                </div>
                                <x-claim-status :claim="$claim" />
                            </div>

                            @if ($claim->status->revealsContact())
                                <x-contact :user="$claim->seeker" label="Ticket schicken an" />
                            @endif

                            @if (! $kickedOff)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($claim->status === ClaimStatus::Requested)
                                        <button type="button" wire:click="accept({{ $claim->id }})" class="rounded-xl bg-red px-4 py-2 font-bold text-white hover:bg-red-dark">Zusagen</button>
                                        <button type="button" wire:click="decline({{ $claim->id }})" class="rounded-xl px-4 py-2 font-semibold text-ink/60 ring-1 ring-line hover:text-red">Ablehnen</button>
                                    @elseif ($claim->status === ClaimStatus::Accepted)
                                        <button type="button" wire:click="markSent({{ $claim->id }})" class="rounded-xl bg-red px-4 py-2 font-bold text-white hover:bg-red-dark">Ticket gesendet</button>
                                    @endif
                                </div>
                            @elseif (in_array($claim->status, [ClaimStatus::Sent, ClaimStatus::Received], true))
                                @if ($claim->outcome)
                                    <p class="mt-2 text-sm text-ink/60">Gemeldet: {{ $claim->outcome === ClaimOutcome::NoShow ? 'nicht erschienen' : 'war dabei' }}</p>
                                @else
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                        <span class="text-ink/60">War {{ $claim->seeker->displayName() }} da?</span>
                                        <button type="button" wire:click="report({{ $claim->id }}, 'attended')" class="rounded-xl bg-ink px-3 py-1.5 font-bold text-white">Ja</button>
                                        <button type="button" wire:click="report({{ $claim->id }}, 'no_show')" wire:confirm="Wirklich als «nicht erschienen» melden?" class="rounded-xl px-3 py-1.5 font-semibold text-ink/60 ring-1 ring-line hover:text-red">Nein</button>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    @if ($offer->claims->isEmpty() && $offer->status === OfferStatus::Open && ! $kickedOff)
                        <p class="text-sm text-ink/60">Noch keine Anfragen. Wir benachrichtigen passende Fans.</p>
                    @endif
                </div>

                @if ($offer->status === OfferStatus::Open && ! $kickedOff && $offer->claims->whereIn('status', [ClaimStatus::Accepted, ClaimStatus::Sent, ClaimStatus::Received])->isEmpty())
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
