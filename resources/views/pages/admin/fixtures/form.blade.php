<?php

use App\Models\Fixture;
use Illuminate\Support\Carbon;
use Livewire\Component;

new class extends Component
{
	public const COMPETITIONS = ['Challenge League', 'Schweizer Cup', 'Testspiel'];

	public ?Fixture $fixture = null;

	public string $opponent = '';

	public string $kickoffAt = '';

	public string $competition = 'Challenge League';

	public function mount(?Fixture $fixture = null): void
	{
		if ($fixture?->exists) {
			$this->fixture = $fixture;
			$this->opponent = $fixture->opponent;
			$this->kickoffAt = $fixture->kickoff_at->format('Y-m-d\TH:i');
			$this->competition = (string) $fixture->competition;
		}
	}

	public function save(): void
	{
		$this->validate([
			'opponent' => ['required', 'string', 'max:80'],
			'kickoffAt' => ['required', 'date'],
			'competition' => ['required', 'in:'.implode(',', self::COMPETITIONS)],
		], attributes: [
			'opponent' => 'Gegner',
			'kickoffAt' => 'Anpfiff',
			'competition' => 'Wettbewerb',
		]);

		$attributes = [
			'opponent' => $this->opponent,
			'kickoff_at' => Carbon::parse($this->kickoffAt),
			'competition' => $this->competition,
		];

		$this->fixture ? $this->fixture->update($attributes) : Fixture::create($attributes);

		session()->flash('toast', 'Match gespeichert.');
		$this->redirectRoute('admin.fixtures.index', navigate: true);
	}

	public function delete(): void
	{
		if (! $this->fixture || $this->fixture->offers()->exists()) {
			$this->dispatch('toast', message: 'Für dieses Match gibt es schon Angebote. Es kann nicht gelöscht werden.', type: 'error');

			return;
		}

		$this->fixture->delete();

		session()->flash('toast', 'Match gelöscht.');
		$this->redirectRoute('admin.fixtures.index', navigate: true);
	}

	public function render()
	{
		return $this->view()->title(($this->fixture ? 'Match bearbeiten' : 'Match erfassen').' · Admin');
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header :title="$fixture ? 'Match bearbeiten' : 'Match erfassen'" class="mt-8" />

	<form wire:submit="save" class="mt-8 flex flex-col gap-y-4 sm:max-w-sm">
		<x-form.input label="Gegner" name="opponent" wire:model="opponent" placeholder="z.B. FC Aarau" required />
		<x-form.input label="Anpfiff" name="kickoff_at" type="datetime-local" wire:model="kickoffAt" required />
		<x-form.select label="Wettbewerb" name="competition" wire:model="competition" :options="array_combine($this::COMPETITIONS, $this::COMPETITIONS)" />

		<div class="mt-4 flex flex-wrap items-center gap-3">
			<x-button type="submit" wire:loading.attr="disabled">Speichern</x-button>
			<x-button variant="neutral" :href="route('admin.fixtures.index')" wire:navigate>Abbrechen</x-button>
		</div>
	</form>

	@if ($fixture)
		<div class="mt-12 border-t border-ink/8 pt-6">
			<x-button variant="plain" size="sm" class="-ml-3" aria-haspopup="dialog"
				x-on:click="$store.confirm.ask({ title: 'Match löschen?', message: 'Das geht nur, solange niemand dafür Plätze freigegeben hat.', confirm: 'Löschen' }).then((ok) => ok && $wire.delete())">Match löschen</x-button>
		</div>
	@endif
</div>
