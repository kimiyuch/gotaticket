<?php

use App\Models\Fixture;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Matches · Admin')] class extends Component
{
	public bool $showPast = false;

	/**
	 * @return Collection<int, Fixture>
	 */
	#[Computed]
	public function fixtures(): Collection
	{
		return Fixture::query()
			->withCount('offers')
			->when(
				$this->showPast,
				fn ($query) => $query->where('kickoff_at', '<=', now()->subMinutes(30))->orderByDesc('kickoff_at'),
				fn ($query) => $query->upcoming(),
			)
			->get();
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header title="Matches" description="Heimspiele, für die Fans Plätze freigeben können." class="mt-8">
		<x-button size="sm" :href="route('admin.fixtures.create')" wire:navigate class="pl-2"><x-phosphor-plus class="size-4" /> Match erfassen</x-button>
	</x-page-header>

	<div class="mt-8 flex items-center justify-end gap-x-3">
		<label for="show-past" class="text-sm/6 text-ink/70">Vergangene anzeigen</label>
		<x-form.toggle id="show-past" name="show_past" wire:model.live="showPast" />
	</div>

	<div class="mt-4 -mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6">
		<div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6">
			<table class="w-full text-left text-sm/6">
				<thead>
					<tr class="border-b border-ink/10">
						<th scope="col" class="py-2 pr-4 font-medium whitespace-nowrap">Anpfiff</th>
						<th scope="col" class="px-4 py-2 font-medium whitespace-nowrap">Gegner</th>
						<th scope="col" class="px-4 py-2 font-medium whitespace-nowrap">Wettbewerb</th>
						<th scope="col" class="py-2 pl-4 text-right font-medium whitespace-nowrap">Angebote</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-ink/8">
					@forelse ($this->fixtures as $fixture)
						<tr wire:key="fixture-{{ $fixture->id }}">
							<td class="py-3 pr-4 text-ink/60 tabular-nums">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMM YYYY · HH:mm') }}</td>
							<td class="px-4 py-3"><a href="{{ route('admin.fixtures.edit', $fixture) }}" wire:navigate class="font-medium hover:text-red-dark">{{ $fixture->opponent }}</a></td>
							<td class="px-4 py-3 text-ink/60">{{ $fixture->competition }}</td>
							<td class="py-3 pl-4 text-right tabular-nums">{{ $fixture->offers_count }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="4" class="py-6 text-base/6 text-ink/60">{{ $showPast ? 'Keine vergangenen Matches.' : 'Keine kommenden Matches erfasst.' }}</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
