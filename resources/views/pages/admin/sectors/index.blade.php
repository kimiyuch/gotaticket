<?php

use App\Models\Sector;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sektoren · Admin')] class extends Component
{
	/**
	 * @return Collection<int, Sector>
	 */
	#[Computed]
	public function sectors(): Collection
	{
		return Sector::query()->withCount('seekers')->orderBy('sort')->orderBy('name')->get();
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header title="Sektoren" description="Bereiche, in denen Fans Plätze freigeben und suchen. Der Gästesektor fehlt bewusst." class="mt-8">
		<x-button size="sm" :href="route('admin.sectors.create')" wire:navigate class="pl-2"><x-phosphor-plus class="size-4" /> Sektor erfassen</x-button>
	</x-page-header>

	<div class="mt-8 -mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6">
		<div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6">
			<table class="w-full text-left text-sm/6">
				<thead>
					<tr class="border-b border-ink/10">
						<th scope="col" class="py-2 pr-4 font-medium whitespace-nowrap">Sektor</th>
						<th scope="col" class="px-4 py-2 font-medium whitespace-nowrap">Art</th>
						<th scope="col" class="px-4 py-2 text-right font-medium whitespace-nowrap">Folgen</th>
						<th scope="col" class="py-2 pl-4 font-medium whitespace-nowrap"><span class="sr-only">Status</span></th>
					</tr>
				</thead>
				<tbody class="divide-y divide-ink/8">
					@foreach ($this->sectors as $sector)
						<tr wire:key="sector-{{ $sector->id }}">
							<td class="py-3 pr-4">
								<a href="{{ route('admin.sectors.edit', $sector) }}" wire:navigate class="font-medium hover:text-red-dark">{{ $sector->name }}</a>
								<p class="text-ink/55">{{ $sector->code ? 'Sektor '.$sector->code : 'Code fehlt' }}</p>
							</td>
							<td class="px-4 py-3 text-ink/60">{{ $sector->kind->label() }}</td>
							<td class="px-4 py-3 text-right tabular-nums">{{ $sector->seekers_count }}</td>
							<td class="py-3 pl-4 text-right">
								@unless ($sector->is_active)
									<x-badge color="muted">Inaktiv</x-badge>
								@endunless
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
