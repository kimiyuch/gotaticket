@props([
	'variant' => 'primary',
	'size' => 'md',
	'href' => null,
])

@php
	$classes = [
		'relative inline-flex items-center justify-center gap-x-2 rounded-lg font-semibold whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red disabled:opacity-50',
		match ($size) {
			'sm' => 'px-3 py-1 text-sm/6',
			default => 'px-4 py-2 text-base/6',
		},
		match ($variant) {
			'soft' => 'bg-red/8 text-red-dark hover:bg-red/12',
			'neutral' => 'bg-ink/5 text-ink hover:bg-ink/8',
			'plain' => 'text-ink/60 hover:bg-ink/5 hover:text-ink',
			default => 'bg-red text-white hover:bg-red-dark',
		},
	];
@endphp

@if ($href)
	<a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
	<button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
		@if ($size === 'sm')
			<span class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" aria-hidden="true"></span>
		@endif
		{{ $slot }}
	</button>
@endif
