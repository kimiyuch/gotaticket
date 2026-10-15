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

<div class="max-w-xs">
    <x-page-header title="Einloggen" />

    @if ($sent)
        <div class="mt-6 rounded-xl bg-paper p-5">
            <x-phosphor-envelope-simple class="size-7 text-red" />
            <p class="mt-4 font-semibold">Check dein Postfach.</p>
            <p class="mt-1 text-base/6 text-pretty text-ink/60">Wir haben dir einen Login-Link an <strong class="font-medium text-ink">{{ $email }}</strong> geschickt. Er ist 30 Minuten gültig.</p>

            @if ($devLink)
                <x-button variant="soft" size="sm" :href="$devLink" class="mt-4">Lokal: direkt einloggen</x-button>
            @endif
        </div>
    @else
        <p class="mt-2 text-base/6 text-pretty text-ink/60">Kein Passwort nötig. Wir schicken dir einen Login-Link. Neu hier? Dann wird dein Konto gleich erstellt.</p>

        <form wire:submit="send" class="mt-6 flex flex-col gap-y-4">
            <x-form.input label="E-Mail" name="email" type="email" wire:model="email" autocomplete="email" autofocus required />
            <x-button type="submit" wire:loading.attr="disabled">Login-Link schicken</x-button>
        </form>
    @endif
</div>
