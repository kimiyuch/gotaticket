<?php

use App\Enums\SectorKind;
use App\Models\Sector;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
	public ?Sector $sector = null;

	public string $code = '';

	public string $name = '';

	public string $kind = 'standing';

	public int $sort = 0;

	public bool $isActive = true;

	public function mount(?Sector $sector = null): void
	{
		if ($sector?->exists) {
			$this->sector = $sector;
			$this->code = (string) $sector->code;
			$this->name = $sector->name;
			$this->kind = $sector->kind->value;
			$this->sort = (int) $sector->sort;
			$this->isActive = $sector->is_active;
		} else {
			$this->sort = (int) Sector::max('sort') + 1;
		}
	}

	public function save(): void
	{
		$this->validate([
			'code' => ['nullable', 'string', 'max:10'],
			'name' => ['required', 'string', 'max:80'],
			'kind' => ['required', Rule::enum(SectorKind::class)],
			'sort' => ['required', 'integer', 'min:0', 'max:999'],
			'isActive' => ['boolean'],
		], attributes: [
			'kind' => 'Art',
			'sort' => 'Reihenfolge',
		]);

		$attributes = [
			'code' => $this->code ?: null,
			'name' => $this->name,
			'kind' => $this->kind,
			'sort' => $this->sort,
			'is_active' => $this->isActive,
		];

		$this->sector ? $this->sector->update($attributes) : Sector::create($attributes);

		session()->flash('toast', 'Sektor gespeichert.');
		$this->redirectRoute('admin.sectors.index', navigate: true);
	}

	public function render()
	{
		return $this->view()->title(($this->sector ? 'Sektor bearbeiten' : 'Sektor erfassen').' · Admin');
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header :title="$sector ? 'Sektor bearbeiten' : 'Sektor erfassen'" class="mt-8" />

	<form wire:submit="save" class="mt-8 flex flex-col gap-y-4 sm:max-w-sm">
		<x-form.input label="Name" name="name" wire:model="name" placeholder="z.B. Stehplatz Bierkurve" required />
		<x-form.input label="Code" hint="(optional)" name="code" wire:model="code" placeholder="z.B. D2" />
		<x-form.select label="Art" name="kind" wire:model="kind" :options="collect(App\Enums\SectorKind::cases())->mapWithKeys(fn ($kind) => [$kind->value => $kind->label()])->all()" />
		<x-form.input label="Reihenfolge" name="sort" type="number" min="0" max="999" wire:model="sort" required />

		<div class="flex items-center justify-between gap-x-4 rounded-xl p-4 ring-1 ring-ink/10">
			<label for="is-active">
				<span class="block font-medium">Aktiv</span>
				<span class="block text-sm/6 text-ink/55">Inaktive Sektoren erscheinen nicht mehr zur Auswahl.</span>
			</label>
			<x-form.toggle id="is-active" name="is_active" wire:model="isActive" />
		</div>

		<div class="mt-4 flex flex-wrap items-center gap-3">
			<x-button type="submit" wire:loading.attr="disabled">Speichern</x-button>
			<x-button variant="neutral" :href="route('admin.sectors.index')" wire:navigate>Abbrechen</x-button>
		</div>
	</form>
</div>
