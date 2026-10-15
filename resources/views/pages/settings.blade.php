<?php

use App\Models\Sector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Einstellungen')] class extends Component
{
    public string $name = '';

    public string $phone = '';

    /** @var list<int> */
    public array $sectorIds = [];

    public int $wantedSeats = 1;

    public bool $notifyByMail = true;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) $user->name;
        $this->phone = (string) $user->phone;
        $this->sectorIds = $user->sectors()->pluck('sectors.id')->all();
        $this->wantedSeats = $user->wanted_seats;
        $this->notifyByMail = $user->notify_by_mail;
    }

    /**
     * @return Collection<int, Sector>
     */
    #[Computed]
    public function sectors(): Collection
    {
        return Sector::active()->get();
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9 +()\-]*$/'],
            'sectorIds' => ['array'],
            'sectorIds.*' => ['integer', Rule::exists('sectors', 'id')->where('is_active', true)],
            'wantedSeats' => ['required', 'integer', 'min:1', 'max:4'],
        ], [
            'name.required' => 'Bitte gib einen Namen an, damit andere Fans wissen, mit wem sie es zu tun haben.',
            'phone.regex' => 'Bitte gib eine gültige Telefonnummer ein.',
        ]);

        $user = auth()->user();
        $user->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'wanted_seats' => $this->wantedSeats,
            'notify_by_mail' => $this->notifyByMail,
        ]);
        $user->sectors()->sync($this->sectorIds);

        session()->flash('status', 'Gespeichert.');
    }
};
?>

<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight">Einstellungen</h1>
        <p class="mt-1 text-ink/70">Sag uns, wo du gern stehst oder sitzt. Wir melden uns, sobald dort ein Platz frei wird.</p>
    </div>

    @if (session('status'))
        <p class="rounded-2xl bg-ink px-5 py-4 font-medium text-white">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="space-y-8">
        <fieldset>
            <legend class="mb-2 text-sm font-semibold">Ich suche Plätze in …</legend>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($this->sectors as $sector)
                    <label wire:key="sector-{{ $sector->id }}" class="cursor-pointer rounded-2xl bg-white px-4 py-3 ring-1 ring-line has-checked:bg-ink has-checked:text-white has-checked:ring-ink">
                        <input type="checkbox" wire:model="sectorIds" value="{{ $sector->id }}" class="sr-only">
                        <span class="block text-xs font-semibold uppercase tracking-wide opacity-60">{{ $sector->code ? 'Sektor '.$sector->code : $sector->kind->label() }}</span>
                        <span class="block font-bold leading-tight">{{ str($sector->name)->after(' ') }}</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-sm text-ink/60">Keinen Sektor gewählt? Dann bekommst du keine Benachrichtigungen, kannst aber jederzeit selbst nachschauen.</p>
        </fieldset>

        <label class="block">
            <span class="text-sm font-semibold">Normalerweise brauche ich</span>
            <select wire:model="wantedSeats" class="mt-1 block w-full rounded-xl border-0 bg-white px-4 py-3 ring-1 ring-line focus:ring-2 focus:ring-red focus:outline-none">
                @foreach (range(1, 4) as $count)
                    <option value="{{ $count }}">{{ $count }} {{ $count === 1 ? 'Platz' : 'Plätze' }}</option>
                @endforeach
            </select>
        </label>

        <fieldset class="space-y-3">
            <legend class="mb-2 text-sm font-semibold">Benachrichtigungen</legend>

            <div x-data="pushToggle" class="rounded-2xl bg-white p-4 ring-1 ring-line">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold">Push auf diesem Gerät</p>
                        <p class="text-sm text-ink/60" x-show="state === 'on'">Aktiv. Du erfährst sofort, wenn etwas frei wird.</p>
                        <p class="text-sm text-ink/60" x-show="state === 'off'">Am schnellsten: Plätze sind oft in Minuten weg.</p>
                        <p class="text-sm text-ink/60" x-show="state === 'denied'">Blockiert. Erlaube Mitteilungen in den Browser-Einstellungen.</p>
                        <p class="text-sm text-ink/60" x-show="state === 'unsupported'">Dein Browser unterstützt leider keine Push-Mitteilungen.</p>
                        <p class="text-sm text-ink/60" x-show="state === 'needs-install'">Auf dem iPhone: Tippe auf «Teilen» → «Zum Home-Bildschirm» und öffne die App von dort.</p>
                    </div>
                    <button type="button" x-show="state === 'off'" x-on:click="enable" x-bind:disabled="busy" class="shrink-0 rounded-xl bg-red px-4 py-2 font-bold text-white hover:bg-red-dark disabled:opacity-60">Aktivieren</button>
                    <button type="button" x-show="state === 'on'" x-on:click="disable" x-bind:disabled="busy" class="shrink-0 rounded-xl px-4 py-2 font-semibold text-ink/60 ring-1 ring-line hover:text-red disabled:opacity-60">Deaktivieren</button>
                </div>
            </div>

            <label class="flex items-center justify-between gap-4 rounded-2xl bg-white p-4 ring-1 ring-line">
                <span>
                    <span class="block font-bold">E-Mail</span>
                    <span class="block text-sm text-ink/60">Zusätzlich oder als Ersatz für Push.</span>
                </span>
                <input type="checkbox" wire:model="notifyByMail" class="size-5 accent-red">
            </label>
        </fieldset>

        <fieldset class="space-y-3">
            <legend class="mb-2 text-sm font-semibold">Über dich</legend>
            <p class="text-sm text-ink/60">Wird nur der anderen Person angezeigt, wenn eine Übergabe bestätigt ist.</p>

            <label class="block">
                <span class="text-sm font-semibold">Name</span>
                <input type="text" wire:model="name" autocomplete="name" maxlength="60" placeholder="z.B. Pippo"
                    class="mt-1 block w-full rounded-xl border-0 bg-white px-4 py-3 ring-1 ring-line focus:ring-2 focus:ring-red focus:outline-none">
                @error('name') <p class="mt-1 text-sm font-medium text-red">{{ $message }}</p> @enderror
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Telefon / WhatsApp <span class="font-normal text-ink/50">(optional)</span></span>
                <input type="tel" wire:model="phone" autocomplete="tel" placeholder="079 123 45 67"
                    class="mt-1 block w-full rounded-xl border-0 bg-white px-4 py-3 ring-1 ring-line focus:ring-2 focus:ring-red focus:outline-none">
                @error('phone') <p class="mt-1 text-sm font-medium text-red">{{ $message }}</p> @enderror
            </label>

            <p class="text-sm text-ink/60">E-Mail: {{ auth()->user()->email }}</p>
        </fieldset>

        <button type="submit" class="w-full rounded-xl bg-ink px-5 py-4 text-lg font-bold text-white transition hover:bg-ink/90" wire:loading.attr="disabled">
            Speichern
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="border-t border-line pt-6">
        @csrf
        <button type="submit" class="font-semibold text-ink/60 underline hover:text-red">Abmelden</button>
    </form>
</div>
