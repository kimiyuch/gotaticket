<?php

use App\Actions\Offers\NotifyMatchingSeekers;
use App\Enums\OfferStatus;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Platz freigeben')] class extends Component
{
    public const MAX_SEATS = 4;

    public ?int $fixtureId = null;

    public ?int $sectorId = null;

    public int $seats = 1;

    public string $note = '';

    public function mount(): void
    {
        $this->fixtureId = $this->fixtures->first()?->id;
    }

    /**
     * @return Collection<int, Fixture>
     */
    #[Computed]
    public function fixtures(): Collection
    {
        return Fixture::where('kickoff_at', '>', now())->orderBy('kickoff_at')->get();
    }

    /**
     * @return Collection<int, Sector>
     */
    #[Computed]
    public function sectors(): Collection
    {
        return Sector::active()->get();
    }

    public function save(NotifyMatchingSeekers $notifyMatchingSeekers): void
    {
        $this->validate([
            'fixtureId' => ['required', Rule::exists('fixtures', 'id')->where(fn ($query) => $query->where('kickoff_at', '>', now()))],
            'sectorId' => ['required', Rule::exists('sectors', 'id')->where('is_active', true)],
            'seats' => ['required', 'integer', 'min:1', 'max:'.self::MAX_SEATS],
            'note' => ['nullable', 'string', 'max:200'],
        ], [
            'fixtureId.required' => 'Bitte wähle einen Match.',
            'sectorId.required' => 'Bitte wähle einen Sektor.',
        ]);

        $offer = Offer::create([
            'fixture_id' => $this->fixtureId,
            'user_id' => auth()->id(),
            'sector_id' => $this->sectorId,
            'seats' => $this->seats,
            'note' => $this->note ?: null,
            'status' => OfferStatus::Open,
        ]);

        $notifyMatchingSeekers->handle($offer);

        session()->flash('status', 'Merci! Dein Platz ist freigegeben. Wir melden uns, sobald sich jemand meldet.');

        $this->redirectRoute('dashboard', navigate: true);
    }
};
?>

<div>
    <x-page-header title="Ich han eis" description="Du kannst an einem Match nicht? Gib deinen Platz frei, ein anderer Fan freut sich." />

    @if ($this->fixtures->isEmpty())
        <p class="mt-8 rounded-xl bg-paper p-6 text-ink/60">Aktuell sind keine Heimspiele erfasst.</p>
    @else
        <form wire:submit="save" class="mt-8 flex flex-col gap-y-12">
            <fieldset>
                <legend class="text-base/7 font-semibold">Match</legend>
                <div class="mt-3 divide-y divide-ink/8 rounded-xl ring-1 ring-ink/10">
                    @foreach ($this->fixtures as $fixture)
                        <label wire:key="fixture-{{ $fixture->id }}" class="flex cursor-pointer items-center gap-x-4 px-4 py-3 first:rounded-t-xl last:rounded-b-xl has-checked:bg-red/4">
                            <x-form.radio name="fixture" wire:model="fixtureId" value="{{ $fixture->id }}" />
                            <x-fixture :fixture="$fixture" class="flex-1" />
                        </label>
                    @endforeach
                </div>
                @error('fixtureId') <p class="mt-2 text-sm/6 text-red-dark">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset>
                <legend class="text-base/7 font-semibold">Sektor</legend>
                <div class="mt-3 grid grid-cols-2 gap-4">
                    @foreach ($this->sectors as $sector)
                        <x-form.choice wire:key="sector-{{ $sector->id }}" name="sector" wire:model="sectorId" value="{{ $sector->id }}"
                            :meta="$sector->code ? 'Sektor '.$sector->code : $sector->kind->label()" :title="str($sector->name)->after(' ')" />
                    @endforeach
                </div>
                @error('sectorId') <p class="mt-2 text-sm/6 text-red-dark">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset>
                <legend class="text-base/7 font-semibold">Anzahl Plätze</legend>
                <div class="mt-3 grid grid-cols-4 gap-4 sm:max-w-xs">
                    @foreach (range(1, $this::MAX_SEATS) as $count)
                        <label wire:key="seats-{{ $count }}" class="flex cursor-pointer items-center justify-center rounded-lg py-2 text-base/6 font-semibold tabular-nums ring-1 ring-ink/10 hover:ring-ink/20 has-checked:bg-red/4 has-checked:ring-2 has-checked:ring-red has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-red">
                            <input type="radio" name="seats" wire:model="seats" value="{{ $count }}" class="sr-only" aria-label="{{ $count }} {{ $count === 1 ? 'Platz' : 'Plätze' }}">
                            {{ $count }}
                        </label>
                    @endforeach
                </div>
                @error('seats') <p class="mt-2 text-sm/6 text-red-dark">{{ $message }}</p> @enderror
            </fieldset>

            <x-form.input label="Notiz" hint="(optional)" name="note" wire:model="note" maxlength="200" placeholder="z.B. Reihe 3, nebeneinander" />

            <x-button type="submit" wire:loading.attr="disabled" class="w-full sm:w-auto sm:self-start">Platz freigeben</x-button>
        </form>
    @endif
</div>
