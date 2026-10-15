@props(['user', 'label'])

<div class="mt-3 rounded-lg bg-paper p-3">
    <p class="text-sm/6 text-ink/50">{{ $label }}</p>
    <p class="font-semibold">{{ $user->displayName() }}</p>
    <ul role="list" class="mt-2 flex flex-wrap gap-2">
        <li>
            <x-button variant="neutral" size="sm" href="mailto:{{ $user->email }}" class="pl-2">
                <x-phosphor-envelope-simple class="size-4 text-ink/50" /> {{ $user->email }}
            </x-button>
        </li>
        @if ($user->phone)
            <li>
                <x-button variant="neutral" size="sm" href="tel:{{ preg_replace('/[^0-9+]/', '', $user->phone) }}" class="pl-2">
                    <x-phosphor-phone class="size-4 text-ink/50" /> {{ $user->phone }}
                </x-button>
            </li>
            <li>
                <x-button variant="neutral" size="sm" href="https://wa.me/{{ preg_replace('/^0/', '41', preg_replace('/[^0-9]/', '', $user->phone)) }}" target="_blank" rel="noopener" class="pl-2">
                    <x-phosphor-whatsapp-logo class="size-4 text-ink/50" /> WhatsApp
                </x-button>
            </li>
        @endif
    </ul>
</div>
