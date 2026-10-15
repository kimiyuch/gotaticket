<?php

use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin')] class extends Component
{
	/**
	 * @return array<string, int>
	 */
	#[Computed]
	public function stats(): array
	{
		return [
			'Fans' => User::count(),
			'Offene Angebote' => Offer::where('status', OfferStatus::Open)->whereHas('fixture', fn ($query) => $query->upcoming())->count(),
			'Übergaben' => Claim::whereIn('status', [ClaimStatus::Sent, ClaimStatus::Received])->count(),
			'No-Shows' => Claim::where('outcome', ClaimOutcome::NoShow)->count(),
		];
	}

	/**
	 * @return Collection<int, Fixture>
	 */
	#[Computed]
	public function fixtures(): Collection
	{
		return Fixture::upcoming()
			->withCount([
				'offers',
				'offers as open_offers_count' => fn ($query) => $query->where('status', OfferStatus::Open),
			])
			->withSum('offers as seats_offered', 'seats')
			->limit(5)
			->get();
	}

	/**
	 * Active handovers per fixture, keyed by fixture id.
	 *
	 * @return array<int, int>
	 */
	#[Computed]
	public function activeClaims(): array
	{
		return Claim::query()
			->whereIn('claims.status', ClaimStatus::active())
			->join('offers', 'offers.id', '=', 'claims.offer_id')
			->whereIn('offers.fixture_id', $this->fixtures->modelKeys())
			->groupBy('offers.fixture_id')
			->selectRaw('offers.fixture_id, count(*) as aggregate')
			->pluck('aggregate', 'offers.fixture_id')
			->all();
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header title="Übersicht" class="mt-8" />

	<dl class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-4">
		@foreach ($this->stats as $label => $value)
			<div class="border-t border-ink/10 pt-4">
				<dt class="truncate text-sm/6 text-ink/60">{{ $label }}</dt>
				<dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ $value }}</dd>
			</div>
		@endforeach
	</dl>

	<section class="mt-16" aria-labelledby="next-fixtures-title">
		<div class="flex items-center justify-between gap-x-4">
			<h2 id="next-fixtures-title" class="text-xl font-semibold tracking-tight">Nächste Matches</h2>
			<x-button variant="soft" size="sm" :href="route('admin.fixtures.create')" wire:navigate class="pl-2"><x-phosphor-plus class="size-4" /> Match erfassen</x-button>
		</div>

		<div class="mt-4 -mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6">
			<div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6">
				<table class="w-full text-left text-sm/6">
					<thead>
						<tr class="border-b border-ink/10">
							<th scope="col" class="py-2 pr-4 font-medium whitespace-nowrap">Match</th>
							<th scope="col" class="px-4 py-2 text-right font-medium whitespace-nowrap">Offen</th>
							<th scope="col" class="px-4 py-2 text-right font-medium whitespace-nowrap">Plätze</th>
							<th scope="col" class="py-2 pl-4 text-right font-medium whitespace-nowrap">Anfragen</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-ink/8">
						@forelse ($this->fixtures as $fixture)
							<tr wire:key="fixture-{{ $fixture->id }}">
								<td class="py-3 pr-4">
									<a href="{{ route('admin.fixtures.edit', $fixture) }}" wire:navigate class="font-medium hover:text-red-dark">{{ $fixture->opponent }}</a>
									<p class="text-ink/55 tabular-nums">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMM · HH:mm') }}</p>
								</td>
								<td class="px-4 py-3 text-right tabular-nums">{{ $fixture->open_offers_count }} / {{ $fixture->offers_count }}</td>
								<td class="px-4 py-3 text-right tabular-nums">{{ (int) $fixture->seats_offered }}</td>
								<td class="py-3 pl-4 text-right tabular-nums">{{ $this->activeClaims[$fixture->id] ?? 0 }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="4" class="py-6 text-base/6 text-ink/60">Keine Heimspiele erfasst.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</section>
</div>
