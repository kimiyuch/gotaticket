@props(['route'])

@php($active = request()->routeIs($route))

<a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'rounded-sm px-2 py-1 text-sm/6 font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red',
        'text-ink underline decoration-red decoration-2 underline-offset-8' => $active,
        'text-ink/55 hover:text-ink' => ! $active,
    ]) }}>{{ $slot }}</a>
