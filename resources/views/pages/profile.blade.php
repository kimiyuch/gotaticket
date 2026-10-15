<?php

use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profil')] class extends Component
{
	public string $name = '';

	public string $email = '';

	public string $phone = '';

	public string $currentPassword = '';

	public string $password = '';

	public string $passwordConfirmation = '';

	/** @var list<int> */
	public array $sectorIds = [];

	public int $wantedSeats = 1;

	public bool $notifyByMail = true;

	public function mount(): void
	{
		$user = auth()->user();

		$this->name = (string) $user->name;
		$this->email = $user->email;
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
			'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class)->ignore(auth()->id())],
			'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9 +()\-]*$/'],
			'sectorIds' => ['array'],
			'sectorIds.*' => ['integer', Rule::exists('sectors', 'id')->where('is_active', true)],
			'wantedSeats' => ['required', 'integer', 'min:1', 'max:4'],
		], [
			'name.required' => 'Bitte gib einen Namen an, damit andere Fans wissen, mit wem sie es zu tun haben.',
			'phone.regex' => 'Bitte gib eine gültige Telefonnummer ein.',
		]);

		$user = auth()->user();
		$user->fill([
			'name' => $this->name,
			'email' => $this->email,
			'phone' => $this->phone ?: null,
			'wanted_seats' => $this->wantedSeats,
			'notify_by_mail' => $this->notifyByMail,
		]);

		$emailChanged = $user->isDirty('email');

		if ($emailChanged) {
			$user->email_verified_at = null;
		}

		$user->save();
		$user->sectors()->sync($this->sectorIds);

		if ($emailChanged) {
			$user->sendEmailVerificationNotification();
		}

		$this->dispatch('toast', message: $emailChanged ? 'Gespeichert. Bitte bestätige deine neue E-Mail-Adresse.' : 'Profil gespeichert.');
	}

	public function updatePassword(): void
	{
		$user = auth()->user();

		$this->validate([
			'currentPassword' => $user->password ? ['required', 'string', 'current_password'] : [],
			'password' => ['required', 'string', Password::default(), 'same:passwordConfirmation'],
		], [
			'currentPassword.current_password' => 'Das aktuelle Passwort stimmt nicht.',
			'password.same' => 'Die beiden Passwörter stimmen nicht überein.',
		], [
			'currentPassword' => 'Aktuelles Passwort',
			'password' => 'Neues Passwort',
		]);

		$user->forceFill(['password' => Hash::make($this->password)])->save();

		$this->reset('currentPassword', 'password', 'passwordConfirmation');
		$this->dispatch('toast', message: 'Passwort geändert.');
	}
};
?>

<div>
	<x-page-header title="Profil" description="Deine Angaben, Sektoren und Benachrichtigungen. Wir melden uns, sobald in deinen Sektoren ein Platz frei wird." />

	<form wire:submit="save" class="mt-8 flex flex-col gap-y-12">
		<fieldset>
			<legend class="text-base/7 font-semibold">Ich suche Plätze in</legend>
			<div class="mt-3 grid grid-cols-2 gap-4">
				@foreach ($this->sectors as $sector)
					<x-form.choice type="checkbox" wire:key="sector-{{ $sector->id }}" name="sectors[]" wire:model="sectorIds" value="{{ $sector->id }}"
						:meta="$sector->code ? 'Sektor '.$sector->code : $sector->kind->label()" :title="str($sector->name)->after(' ')" />
				@endforeach
			</div>
			<p class="mt-3 text-sm/6 text-pretty text-ink/55">Ohne Sektor bekommst du keine Benachrichtigungen, kannst aber jederzeit selbst nachschauen.</p>
		</fieldset>

		<div>
			<label for="wanted-seats" class="text-base/7 font-semibold">Normalerweise brauche ich</label>
			<div class="mt-3 grid grid-cols-[1fr_--spacing(8)] rounded-lg ring-1 ring-ink/15 sm:max-w-xs">
				<select id="wanted-seats" name="wanted_seats" wire:model="wantedSeats" class="col-span-full row-start-1 appearance-none rounded-lg bg-white py-2 pr-8 pl-3 text-base/6 focus:outline-2 focus:-outline-offset-1 focus:outline-red">
					@foreach (range(1, 4) as $count)
						<option value="{{ $count }}">{{ $count }} {{ $count === 1 ? 'Platz' : 'Plätze' }}</option>
					@endforeach
				</select>
				<svg aria-hidden="true" viewBox="0 0 8 5" width="8" height="5" fill="none" class="pointer-events-none col-start-2 row-start-1 place-self-center text-ink/50"><path d="M.5.5 4 4 7.5.5" stroke="currentcolor" /></svg>
			</div>
		</div>

		<fieldset>
			<legend class="text-base/7 font-semibold">Benachrichtigungen</legend>
			<div class="mt-3 divide-y divide-ink/8 rounded-xl ring-1 ring-ink/10">
				<div x-data="pushToggle" class="flex items-center justify-between gap-x-4 p-4">
					<div aria-live="polite">
						<p class="font-medium">Push auf diesem Gerät</p>
						<p class="text-sm/6 text-pretty text-ink/55" x-show="state === 'on'">Aktiv. Du erfährst sofort, wenn etwas frei wird.</p>
						<p class="text-sm/6 text-pretty text-ink/55" x-show="state === 'off' || state === 'loading'">Am schnellsten, denn Plätze sind oft in Minuten weg.</p>
						<p class="text-sm/6 text-pretty text-ink/55" x-show="state === 'denied'">Blockiert. Erlaube Mitteilungen in den Browser-Einstellungen.</p>
						<p class="text-sm/6 text-pretty text-ink/55" x-show="state === 'unsupported'">Dein Browser unterstützt leider keine Push-Mitteilungen.</p>
						<p class="text-sm/6 text-pretty text-ink/55" x-show="state === 'needs-install'">Auf dem iPhone: Teilen → «Zum Home-Bildschirm», dann die App von dort öffnen.</p>
					</div>
					<x-button variant="soft" size="sm" x-show="state === 'off'" x-on:click="enable" x-bind:disabled="busy" x-bind:aria-busy="busy">Aktivieren</x-button>
					<x-button variant="plain" size="sm" x-show="state === 'on'" x-on:click="disable" x-bind:disabled="busy" x-bind:aria-busy="busy">Deaktivieren</x-button>
				</div>

				<div class="flex items-center justify-between gap-x-4 p-4">
					<label for="notify-by-mail">
						<span class="block font-medium">E-Mail</span>
						<span class="block text-sm/6 text-ink/55">Zusätzlich oder als Ersatz für Push.</span>
					</label>
					<x-form.toggle id="notify-by-mail" name="notify_by_mail" wire:model="notifyByMail" />
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend class="text-base/7 font-semibold">Über dich</legend>
			<p class="mt-1 text-sm/6 text-pretty text-ink/55">Wird der anderen Person erst angezeigt, wenn eine Übergabe zugesagt ist.</p>

			<div class="mt-4 flex flex-col gap-y-4 sm:max-w-sm">
				<x-form.input label="Name" name="name" wire:model="name" autocomplete="name" maxlength="60" placeholder="z.B. Pippo" />
				<x-form.input label="E-Mail" name="email" type="email" wire:model="email" autocomplete="email" required />
				<x-form.input label="Telefon / WhatsApp" hint="(optional)" name="phone" type="tel" wire:model="phone" autocomplete="tel" placeholder="079 123 45 67" />
			</div>
		</fieldset>

		<x-button type="submit" wire:loading.attr="disabled" class="w-full sm:w-auto sm:self-start">Speichern</x-button>
	</form>

	<form wire:submit="updatePassword" class="mt-12 border-t border-ink/8 pt-12">
		<fieldset>
			<legend class="text-base/7 font-semibold">{{ auth()->user()->password ? 'Passwort ändern' : 'Passwort setzen' }}</legend>
			<div class="mt-4 flex flex-col gap-y-4 sm:max-w-sm">
				@if (auth()->user()->password)
					<x-form.input label="Aktuelles Passwort" name="current_password" type="password" wire:model="currentPassword" autocomplete="current-password" required />
				@endif
				<x-form.input label="Neues Passwort" hint="(mind. 8 Zeichen)" name="new_password" type="password" wire:model="password" autocomplete="new-password" minlength="8" required />
				<x-form.input label="Neues Passwort wiederholen" name="new_password_confirmation" type="password" wire:model="passwordConfirmation" autocomplete="new-password" required />
			</div>
		</fieldset>
		<x-button type="submit" variant="neutral" size="sm" wire:loading.attr="disabled" class="mt-6">Passwort speichern</x-button>
	</form>

	<form method="POST" action="{{ route('logout') }}" class="mt-12 border-t border-ink/8 pt-6">
		@csrf
		<x-button type="submit" variant="plain" size="sm" class="-ml-3">Abmelden</x-button>
	</form>
</div>
