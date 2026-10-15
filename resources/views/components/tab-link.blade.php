@props(['route', 'icon'])

@php($active = request()->routeIs($route))

{{-- Mobile bottom tab bar item; the active one gets a red bar along the top edge. --}}
<a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'relative flex flex-1 flex-col items-center gap-y-0.5 px-1 pt-2 pb-1.5 text-xs/4 font-medium',
        'text-ink before:absolute before:inset-x-6 before:-top-1 before:h-0.5 before:rounded-full before:bg-red' => $active,
        'text-ink/55' => ! $active,
    ]) }}>
    <x-dynamic-component :component="'phosphor-'.$icon.'-light'" @class(['size-6', 'text-red' => $active]) />
    {{ $slot }}
</a>
