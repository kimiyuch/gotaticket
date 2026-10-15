@props(['label', 'name', 'hint' => null])

@php($errorKey = $attributes->wire('model')->value() ?: $name)

<div {{ $attributes->only('class') }}>
	<label for="{{ $name }}" class="text-sm/6 font-medium">
		{{ $label }}
		@if ($hint)
			<span class="font-normal text-ink/50">{{ $hint }}</span>
		@endif
	</label>
	<input id="{{ $name }}" name="{{ $name }}" {{ $attributes->except('class') }}
		@error($errorKey) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
		class="mt-2 w-full rounded-lg bg-white px-3 py-2 text-base/6 text-ink ring-1 ring-ink/15 placeholder:text-ink/35 focus:outline-2 focus:-outline-offset-1 focus:outline-red">
	@error($errorKey)
		<p id="{{ $name }}-error" class="mt-2 text-sm/6 text-red-dark">{{ $message }}</p>
	@enderror
</div>
