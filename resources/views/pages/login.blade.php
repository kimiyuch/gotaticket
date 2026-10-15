<?php

use App\Actions\SendMagicLink;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Einloggen')] class extends Component
{
    #[Validate('required|email:rfc|max:255', as: 'E-Mail')]
    public string $email = '';

    public bool $sent = false;

    public ?string $devLink = null;

    public function send(SendMagicLink $sendMagicLink): void
    {
        $this->validate();

        $key = 'magic-link:'.mb_strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('email', 'Zu viele Versuche. Bitte warte '.RateLimiter::availableIn($key).' Sekunden.');

            return;
        }

        RateLimiter::hit($key, 600);

        $url = $sendMagicLink->handle($this->email);

        $this->sent = true;
        $this->devLink = app()->isLocal() ? $url : null;
    }
};
?>

<div class="space-y-6">
    <h1 class="text-3xl font-extrabold tracking-tight">Einloggen</h1>

    @if ($sent)
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-line">
            <p class="font-semibold">Check dein Postfach.</p>
            <p class="mt-1 text-ink/70">Wir haben dir einen Login-Link an <strong>{{ $email }}</strong> geschickt. Er ist 30 Minuten gültig.</p>

            @if ($devLink)
                <a href="{{ $devLink }}" class="mt-4 inline-block text-sm font-semibold text-red underline">Lokal: direkt einloggen</a>
            @endif
        </div>
    @else
        <p class="text-ink/70">Kein Passwort nötig. Gib deine E-Mail ein und wir schicken dir einen Login-Link. Neu hier? Dann wird dein Konto gleich erstellt.</p>

        <form wire:submit="send" class="space-y-3">
            <label class="block">
                <span class="text-sm font-semibold">E-Mail</span>
                <input type="email" wire:model="email" autocomplete="email" autofocus required
                    class="mt-1 block w-full rounded-xl border-0 bg-white px-4 py-3 text-lg ring-1 ring-line focus:ring-2 focus:ring-red focus:outline-none">
            </label>
            @error('email') <p class="text-sm font-medium text-red">{{ $message }}</p> @enderror

            <button type="submit" class="w-full rounded-xl bg-red px-5 py-3 text-lg font-bold text-white transition hover:bg-red-dark disabled:opacity-60" wire:loading.attr="disabled">
                Login-Link schicken
            </button>
        </form>
    @endif
</div>
