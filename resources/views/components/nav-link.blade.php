@props(['route', 'icon'])

@php($active = request()->routeIs($route))

{{-- Desktop: text link in the header. Mobile: icon tab in the bottom bar (rendered twice by the layout). --}}
<a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'rounded-lg px-3 py-2 text-sm/6 font-medium max-sm:flex max-sm:flex-1 max-sm:flex-col max-sm:items-center max-sm:gap-y-0.5 max-sm:px-1 max-sm:py-1.5 max-sm:text-xs/4',
        'text-ink sm:bg-ink/5' => $active,
        'text-ink/55 hover:text-ink sm:hover:bg-ink/4' => ! $active,
    ]) }}>
    <x-dynamic-component :component="'heroicon-o-'.$icon" @class(['size-6 sm:hidden', 'text-red' => $active]) />
    {{ $slot }}
</a>
