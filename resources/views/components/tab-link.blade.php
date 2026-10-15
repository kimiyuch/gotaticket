@props(['route', 'icon'])

@php($active = request()->routeIs($route))

{{-- Mobile bottom tab bar item; the active one is marked by its red icon. --}}
<a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
	{{ $attributes->class([
		'flex flex-1 flex-col items-center gap-y-1 px-1 py-2 text-xs/4 font-medium',
		'text-ink' => $active,
		'text-ink/55' => ! $active,
	]) }}>
	<x-dynamic-component :component="'phosphor-'.$icon.'-light'" @class(['size-6', 'text-red' => $active]) />
	{{ $slot }}
</a>
