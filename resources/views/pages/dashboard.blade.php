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

        $this->dispatch('toast', message: 'Freigabe zurückgezogen.');
        $this->refresh();
    }

    public function accept(int $claimId, AcceptClaim $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)), 'Zugesagt. Schick das Ticket jetzt direkt an den Fan.');
    }

    public function decline(int $claimId, DeclineClaim $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)), 'Anfrage abgelehnt.');
    }

    public function markSent(int $claimId, MarkTicketSent $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)), 'Merci! Der Fan wurde informiert.');
    }

    public function confirmReceipt(int $claimId, ConfirmReceipt $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)), 'Ticket erhalten. Viel Spass im Stadion!');
    }

    public function cancel(int $claimId, CancelClaim $action): void
    {
        $this->run(fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId)), 'Schade! Der Platz ist wieder frei für andere Fans.');
    }

    public function report(int $claimId, string $outcome, ReportOutcome $action): void
    {
        $this->run(
            fn () => $action->handle(auth()->user(), Claim::findOrFail($claimId), ClaimOutcome::from($outcome)),
            'Merci für die Rückmeldung.',
        );
    }

    /**
     * Run a claim action and report the outcome as a toast.
     */
    private function run(Closure $callback, string $successMessage): void
    {
        try {
            $callback();
            $this->dispatch('toast', message: $successMessage);
        } catch (ValidationException $exception) {
            $this->dispatch('toast', message: $exception->validator->errors()->first(), type: 'error');
        }

        $this->refresh();
    }

    private function refresh(): void
    {
        unset($this->offers, $this->claims);
    }
};
?>

<div>
    <x-page-header title="Meine Tickets">
        <x-button variant="plain" size="sm" :href="route('settings')" wire:navigate class="pl-2">
            <x-phosphor-gear-six class="size-5 text-ink/40" /> Einstellungen
        </x-button>
    </x-page-header>

    {{-- As seeker --}}
    <section class="mt-12">
        <div class="flex items-center justify-between gap-x-4">
            <h2 class="text-xl font-semibold tracking-tight">Meine Anfragen</h2>
            <x-button variant="soft" size="sm" :href="route('offers.index')" wire:navigate class="pl-2"><x-phosphor-ticket class="size-5" /> Freie Plätze</x-button>
        </div>

        <div class="mt-4 flex flex-col gap-y-3">
            @forelse ($this->claims as $claim)
                @php($fixture = $claim->offer->fixture)
                <article wire:key="claim-{{ $claim->id }}" class="rounded-xl p-4 ring-1 ring-ink/10">
                    <div class="flex items-start justify-between gap-x-3">
                        <x-fixture :fixture="$fixture">{{ $claim->seats }} × {{ $claim->offer->sector->label() }}</x-fixture>
                        <x-claim-status :claim="$claim" />
                    </div>

                    @if ($claim->status === ClaimStatus::Requested)
                        <p class="mt-3 text-base/6 text-ink/60">Warte auf die Zusage von {{ $claim->offer->giver->displayName() }}.</p>
                    @endif

                    @if ($claim->status->revealsContact())
                        <x-contact :user="$claim->offer->giver" label="Ticket kommt von" />
                    @endif

                    @if ($fixture->kickoff_at->isFuture())
                        @if (in_array($claim->status, [ClaimStatus::Requested, ClaimStatus::Accepted, ClaimStatus::Sent], true))
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if (in_array($claim->status, [ClaimStatus::Accepted, ClaimStatus::Sent], true))
                                    <x-button variant="soft" size="sm" wire:click="confirmReceipt({{ $claim->id }})">Ticket erhalten</x-button>
                                @endif
                                <x-button variant="neutral" size="sm" x-on:click="$store.confirm.ask({ title: 'Kannst du doch nicht?', message: 'Dein Platz wird wieder freigegeben und andere Fans werden benachrichtigt.', confirm: 'Ja, ich chan nöd', cancel: 'Doch, ich gang' }).then((ok) => ok && $wire.cancel({{ $claim->id }}))">Ich chan doch nöd</x-button>
                            </div>
                        @elseif ($claim->status === ClaimStatus::Received)
                            <p class="mt-3 text-base/6 text-ink/60">Viel Spass im Stadion! Falls du doch nicht kannst, gib das Ticket bitte selbst weiter.</p>
                        @endif
                    @elseif (in_array($claim->status, [ClaimStatus::Sent, ClaimStatus::Received], true))
                        @if ($claim->outcome === ClaimOutcome::TicketInvalid)
                            <p class="mt-3 text-base/6 text-red-dark">Du hast gemeldet, dass das Ticket nicht funktioniert hat.</p>
                        @else
                            <x-button variant="neutral" size="sm" x-on:click="$store.confirm.ask({ title: 'Ticket hat nicht funktioniert?', message: 'Wir vermerken das beim Fan, der dir das Ticket gegeben hat.', confirm: 'Melden' }).then((ok) => ok && $wire.report({{ $claim->id }}, 'ticket_invalid'))" class="mt-3">Ticket hat nicht funktioniert</x-button>
                        @endif
                    @endif
                </article>
            @empty
                <p class="text-base/6 text-ink/45">Noch keine Anfragen.</p>
            @endforelse
        </div>
    </section>

    {{-- As giver --}}
    <section class="mt-12">
        <div class="flex items-center justify-between gap-x-4">
            <h2 class="text-xl font-semibold tracking-tight">Freigegeben</h2>
            <x-button variant="soft" size="sm" :href="route('offers.create')" wire:navigate class="pl-2"><x-phosphor-plus class="size-5" /> Platz freigeben</x-button>
        </div>

        <div class="mt-4 flex flex-col gap-y-3">
            @forelse ($this->offers as $offer)
                @php($kickedOff = $offer->fixture->kickoff_at->isPast())
                <article wire:key="offer-{{ $offer->id }}" class="rounded-xl p-4 ring-1 ring-ink/10">
                    <div class="flex items-start justify-between gap-x-3">
                        <x-fixture :fixture="$offer->fixture">
                            {{ $offer->seats }} × {{ $offer->sector->label() }}@if ($offer->note) · «{{ $offer->note }}»@endif
                        </x-fixture>
                        @if ($offer->status === OfferStatus::Open)
                            <x-badge color="red" class="tabular-nums">{{ $offer->availableSeats() }} offen</x-badge>
                        @else
                            <x-badge color="solid">Vergeben</x-badge>
                        @endif
                    </div>

                    @if ($offer->claims->isNotEmpty())
                        <ul role="list" class="mt-4 divide-y divide-ink/8 border-t border-ink/8">
                            @foreach ($offer->claims as $claim)
                                @php($record = $claim->seeker->reliability())
                                <li wire:key="offer-claim-{{ $claim->id }}" class="py-4 last:pb-0">
                                    <div class="flex items-start justify-between gap-x-3">
                                        <div>
                                            <p class="font-semibold">{{ $claim->seeker->displayName() }} <span class="font-normal text-ink/55">· {{ $claim->seats }} {{ $claim->seats === 1 ? 'Platz' : 'Plätze' }}</span></p>
                                            <p class="text-sm/6 text-ink/55">
                                                @if ($record['received'] === 0 && $record['no_shows'] === 0)
                                                    Neu dabei
                                                @else
                                                    {{ $record['received'] }}× dabei gewesen @if ($record['no_shows'] > 0) · <span class="text-red-dark">{{ $record['no_shows'] }}× nicht erschienen</span>@endif
                                                @endif
                                            </p>
                                        </div>
                                        <x-claim-status :claim="$claim" />
                                    </div>

                                    @if ($claim->status->revealsContact())
                                        <x-contact :user="$claim->seeker" label="Ticket schicken an" />
                                    @endif

                                    @if (! $kickedOff)
                                        @if ($claim->status === ClaimStatus::Requested)
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <x-button variant="soft" size="sm" wire:click="accept({{ $claim->id }})">Zusagen</x-button>
                                                <x-button variant="neutral" size="sm" wire:click="decline({{ $claim->id }})">Ablehnen</x-button>
                                            </div>
                                        @elseif ($claim->status === ClaimStatus::Accepted)
                                            <x-button variant="soft" size="sm" wire:click="markSent({{ $claim->id }})" class="mt-3">Ticket gesendet</x-button>
                                        @endif
                                    @elseif (in_array($claim->status, [ClaimStatus::Sent, ClaimStatus::Received], true))
                                        @if ($claim->outcome)
                                            <p class="mt-2 text-base/6 text-ink/60">Gemeldet: {{ $claim->outcome === ClaimOutcome::NoShow ? 'nicht erschienen' : 'war dabei' }}.</p>
                                        @else
                                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                                <p class="text-base/6 text-ink/60">War {{ $claim->seeker->displayName() }} da?</p>
                                                <x-button variant="soft" size="sm" wire:click="report({{ $claim->id }}, 'attended')">Ja</x-button>
                                                <x-button variant="neutral" size="sm" x-on:click="$store.confirm.ask({ title: 'Nicht erschienen?', message: {{ Js::from('Das wird im Profil von '.$claim->seeker->displayName().' vermerkt.') }}, confirm: 'Als nicht erschienen melden' }).then((ok) => ok && $wire.report({{ $claim->id }}, 'no_show'))">Nein</x-button>
                                            </div>
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif ($offer->status === OfferStatus::Open && ! $kickedOff)
                        <p class="mt-3 text-base/6 text-ink/45">Noch keine Anfragen. Wir benachrichtigen passende Fans.</p>
                    @endif

                    @if ($offer->status === OfferStatus::Open && ! $kickedOff && $offer->claims->whereIn('status', [ClaimStatus::Accepted, ClaimStatus::Sent, ClaimStatus::Received])->isEmpty())
                        <x-button variant="neutral" size="sm" x-on:click="$store.confirm.ask({ title: 'Freigabe zurückziehen?', message: 'Offene Anfragen werden abgelehnt.', confirm: 'Zurückziehen' }).then((ok) => ok && $wire.withdraw({{ $offer->id }}))" class="mt-3">Zurückziehen</x-button>
                    @endif
                </article>
            @empty
                <p class="text-base/6 text-ink/45">Du hast aktuell keine Plätze freigegeben.</p>
            @endforelse
        </div>
    </section>
</div>
