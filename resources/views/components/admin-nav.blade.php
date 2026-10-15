{{-- Sub navigation shared by all admin pages; scrolls horizontally on small screens. --}}
@php
	$items = [
		'admin.index' => 'Übersicht',
		'admin.fixtures.*' => 'Matches',
		'admin.sectors.*' => 'Sektoren',
		'admin.users' => 'Fans',
		'admin.claims' => 'Übergaben',
	];
@endphp

<nav aria-label="Admin" class="-mx-4 overflow-x-auto border-b border-ink/8 sm:-mx-6">
	<div class="flex gap-x-6 px-4 whitespace-nowrap sm:px-6">
		@foreach ($items as $pattern => $label)
			@php($active = request()->routeIs($pattern))
			<a href="{{ route(str_replace('.*', '.index', $pattern)) }}" wire:navigate @if ($active) aria-current="page" @endif
				@class([
					'-mb-px border-b-2 py-3 text-sm/6 font-medium',
					'border-red text-ink' => $active,
					'border-transparent text-ink/55 hover:text-ink' => ! $active,
				])>{{ $label }}</a>
		@endforeach
	</div>
</nav>
