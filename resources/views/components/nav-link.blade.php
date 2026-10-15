@props(['route', 'variant' => 'plain'])

@php($active = request()->routeIs($route))

{{-- Header navigation item; the active one gets a red bar sitting on the header's bottom border. --}}
<x-button :href="route($route)" :variant="$variant" size="sm" wire:navigate :aria-current="$active ? 'page' : null"
    {{ $attributes->class([
        'after:absolute after:inset-x-1 after:-bottom-[calc(--spacing(3.5)+1px)] after:h-0.5 after:rounded-full after:bg-red' => $active,
        'text-ink' => $active && $variant === 'plain',
    ]) }}>{{ $slot }}</x-button>
