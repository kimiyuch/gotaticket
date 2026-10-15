<?php

use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\Fixture;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Übergaben · Admin')] class extends Component
{
	use WithPagination;

	#[Url(except: '')]
	public string $status = '';

	#[Url(as: 'match', except: '')]
	public string $fixtureId = '';

	public function updated(string $property): void
	{
		if (in_array($property, ['status', 'fixtureId'], true)) {
			$this->resetPage();
		}
	}

	/**
	 * @return Collection<int, Fixture>
	 */
	#[Computed]
	public function fixtures(): Collection
	{
		return Fixture::query()->whereHas('offers.claims')->orderByDesc('kickoff_at')->get();
	}

	/**
	 * @return Paginator<int, Claim>
	 */
	#[Computed]
	public function claims(): Paginator
	{
		return Claim::query()
			->with(['seeker', 'offer.giver', 'offer.sector', 'offer.fixture'])
			->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
			->when($this->fixtureId !== '', fn ($query) => $query->whereHas('offer', fn ($query) => $query->where('fixture_id', $this->fixtureId)))
			->latest()
			->simplePaginate(50);
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header title="Übergaben" description="Alle Anfragen und wie sie ausgegangen sind." class="mt-8" />

	<div class="mt-8 grid gap-4 sm:grid-cols-2">
		<x-form.select label="Status" name="status" wire:model.live="status">
			<option value="">Alle</option>
			<option value="{{ ClaimStatus::Requested->value }}">Angefragt</option>
			<option value="{{ ClaimStatus::Accepted->value }}">Zugesagt</option>
			<option value="{{ ClaimStatus::Sent->value }}">Ticket gesendet</option>
			<option value="{{ ClaimStatus::Received->value }}">Erhalten</option>
			<option value="{{ ClaimStatus::Declined->value }}">Abgelehnt</option>
			<option value="{{ ClaimStatus::Cancelled->value }}">Zurückgezogen</option>
		</x-form.select>
		<x-form.select label="Match" name="fixture" wire:model.live="fixtureId">
			<option value="">Alle</option>
			@foreach ($this->fixtures as $fixture)
				<option value="{{ $fixture->id }}">{{ $fixture->kickoff_at->format('d.m.') }} {{ $fixture->opponent }}</option>
			@endforeach
		</x-form.select>
	</div>

	<div class="mt-6 -mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6">
		<div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6">
			<table class="w-full text-left text-sm/6">
				<thead>
					<tr class="border-b border-ink/10">
						<th scope="col" class="py-2 pr-4 font-medium whitespace-nowrap">Match</th>
						<th scope="col" class="px-4 py-2 font-medium whitespace-nowrap">Von → An</th>
						<th scope="col" class="px-4 py-2 font-medium whitespace-nowrap">Status</th>
						<th scope="col" class="py-2 pl-4 font-medium whitespace-nowrap">Ergebnis</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-ink/8">
					@forelse ($this->claims as $claim)
						<tr wire:key="claim-{{ $claim->id }}">
							<td class="py-3 pr-4">
								<p class="font-medium">{{ $claim->offer->fixture->opponent }}</p>
								<p class="text-ink/55 tabular-nums">{{ $claim->offer->fixture->kickoff_at->locale('de_CH')->isoFormat('D. MMM') }} · {{ $claim->seats }} × {{ $claim->offer->sector->label() }}</p>
							</td>
							<td class="px-4 py-3">
								<p>{{ $claim->offer->giver->displayName() }} → {{ $claim->seeker->displayName() }}</p>
								<p class="text-ink/55 tabular-nums">{{ $claim->created_at->locale('de_CH')->isoFormat('D. MMM, HH:mm') }}</p>
							</td>
							<td class="px-4 py-3"><x-claim-status :claim="$claim" /></td>
							<td class="py-3 pl-4">
								@switch($claim->outcome)
									@case(ClaimOutcome::Attended)
										<span class="text-ink/60">War dabei</span>
										@break
									@case(ClaimOutcome::NoShow)
										<x-badge color="solid">No-Show</x-badge>
										@break
									@case(ClaimOutcome::TicketInvalid)
										<x-badge color="red">Ticket ungültig</x-badge>
										@break
									@default
										<span class="text-ink/35">–</span>
								@endswitch
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="4" class="py-6 text-base/6 text-ink/60">Keine Übergaben gefunden.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	@if ($this->claims->hasPages())
		<div class="mt-6 flex justify-between gap-3">
			<x-button variant="neutral" size="sm" wire:click="previousPage" :disabled="$this->claims->onFirstPage()">Zurück</x-button>
			<x-button variant="neutral" size="sm" wire:click="nextPage" :disabled="! $this->claims->hasMorePages()">Weiter</x-button>
		</div>
	@endif
</div>
