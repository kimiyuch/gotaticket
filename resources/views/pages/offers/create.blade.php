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

<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight">Ich han eis</h1>
        <p class="mt-1 text-ink/70">Du kannst an einem Match nicht? Gib deinen Platz frei, ein anderer Fan freut sich.</p>
    </div>

    @if ($this->fixtures->isEmpty())
        <p class="rounded-2xl bg-white p-5 ring-1 ring-line">Aktuell sind keine Heimspiele erfasst.</p>
    @else
        <form wire:submit="save" class="space-y-8">
            <fieldset class="space-y-2">
                <legend class="mb-2 text-sm font-semibold">Match</legend>
                @foreach ($this->fixtures as $fixture)
                    <label wire:key="fixture-{{ $fixture->id }}" class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 ring-1 ring-line has-checked:ring-2 has-checked:ring-red">
                        <span>
                            <span class="block font-bold">{{ $fixture->opponent }}</span>
                            <span class="block text-sm text-ink/60">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }} · {{ $fixture->competition }}</span>
                        </span>
                        <input type="radio" wire:model="fixtureId" value="{{ $fixture->id }}" class="size-5 accent-red">
                    </label>
                @endforeach
                @error('fixtureId') <p class="text-sm font-medium text-red">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset>
                <legend class="mb-2 text-sm font-semibold">Sektor</legend>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($this->sectors as $sector)
                        <label wire:key="sector-{{ $sector->id }}" class="cursor-pointer rounded-2xl bg-white px-4 py-3 ring-1 ring-line has-checked:bg-ink has-checked:text-white has-checked:ring-ink">
                            <input type="radio" wire:model="sectorId" value="{{ $sector->id }}" class="sr-only">
                            <span class="block text-xs font-semibold uppercase tracking-wide opacity-60">{{ $sector->code ? 'Sektor '.$sector->code : $sector->kind->label() }}</span>
                            <span class="block font-bold leading-tight">{{ str($sector->name)->after(' ') }}</span>
                        </label>
                    @endforeach
                </div>
                @error('sectorId') <p class="mt-2 text-sm font-medium text-red">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset>
                <legend class="mb-2 text-sm font-semibold">Anzahl Plätze</legend>
                <div class="flex items-center gap-4">
                    <button type="button" wire:click="$set('seats', {{ max(1, $seats - 1) }})" class="size-12 rounded-full bg-white text-2xl font-bold ring-1 ring-line disabled:opacity-40" @disabled($seats <= 1) aria-label="Weniger">−</button>
                    <span class="w-8 text-center text-3xl font-extrabold">{{ $seats }}</span>
                    <button type="button" wire:click="$set('seats', {{ min($this::MAX_SEATS, $seats + 1) }})" class="size-12 rounded-full bg-white text-2xl font-bold ring-1 ring-line disabled:opacity-40" @disabled($seats >= $this::MAX_SEATS) aria-label="Mehr">+</button>
                </div>
                @error('seats') <p class="mt-2 text-sm font-medium text-red">{{ $message }}</p> @enderror
            </fieldset>

            <label class="block">
                <span class="text-sm font-semibold">Notiz <span class="font-normal text-ink/50">(optional)</span></span>
                <input type="text" wire:model="note" maxlength="200" placeholder="z.B. Reihe 3, nebeneinander"
                    class="mt-1 block w-full rounded-xl border-0 bg-white px-4 py-3 ring-1 ring-line focus:ring-2 focus:ring-red focus:outline-none">
                @error('note') <p class="mt-1 text-sm font-medium text-red">{{ $message }}</p> @enderror
            </label>

            <button type="submit" class="w-full rounded-xl bg-red px-5 py-4 text-lg font-bold text-white transition hover:bg-red-dark" wire:loading.attr="disabled">
                Platz freigeben
            </button>
        </form>
    @endif
</div>
