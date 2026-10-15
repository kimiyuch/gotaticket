@props(['user', 'label'])

<div class="mt-3 rounded-xl bg-paper px-4 py-3 text-sm">
    <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ $label }}</p>
    <p class="font-bold">{{ $user->displayName() }}</p>
    <p><a href="mailto:{{ $user->email }}" class="underline">{{ $user->email }}</a></p>
    @if ($user->phone)
        <p>
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $user->phone) }}" class="underline">{{ $user->phone }}</a>
            · <a href="https://wa.me/{{ preg_replace('/^0/', '41', preg_replace('/[^0-9]/', '', $user->phone)) }}" target="_blank" rel="noopener" class="underline">WhatsApp</a>
        </p>
    @endif
</div>
