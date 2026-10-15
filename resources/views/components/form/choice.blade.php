{{-- Selectable tile backed by a hidden radio or checkbox. --}}
@props(['type' => 'radio', 'title', 'meta' => null])

<label {{ $attributes->only(['class', 'wire:key'])->class('flex cursor-pointer flex-col rounded-xl bg-white px-4 py-3 ring-1 ring-ink/10 hover:ring-ink/20 has-checked:bg-red/4 has-checked:ring-2 has-checked:ring-red has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-red') }}>
	<input type="{{ $type }}" {{ $attributes->except(['class', 'wire:key']) }} class="sr-only">
	@if ($meta)
		<span class="text-sm/6 text-ink/50">{{ $meta }}</span>
	@endif
	<span class="font-semibold">{{ $title }}</span>
</label>
