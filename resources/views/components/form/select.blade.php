@props(['label', 'name', 'options' => []])

@php($errorKey = $attributes->wire('model')->value() ?: $name)

{{-- Native select with a custom chevron; $options maps values to labels. --}}
<div {{ $attributes->only('class') }}>
	<label for="{{ $name }}" class="text-sm/6 font-medium">{{ $label }}</label>
	<div class="mt-2 grid grid-cols-[1fr_--spacing(8)] rounded-lg bg-white ring-1 ring-ink/15">
		<select id="{{ $name }}" name="{{ $name }}" {{ $attributes->except('class') }}
			@error($errorKey) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
			class="col-span-full row-start-1 appearance-none rounded-lg bg-transparent py-2 pr-8 pl-3 text-base/6 focus:outline-2 focus:-outline-offset-1 focus:outline-red">
			{{ $slot }}
			@foreach ($options as $value => $optionLabel)
				<option value="{{ $value }}">{{ $optionLabel }}</option>
			@endforeach
		</select>
		<svg aria-hidden="true" viewBox="0 0 8 5" width="8" height="5" fill="none" class="pointer-events-none col-start-2 row-start-1 place-self-center text-ink/50"><path d="M.5.5 4 4 7.5.5" stroke="currentcolor" /></svg>
	</div>
	@error($errorKey)
		<p id="{{ $name }}-error" class="mt-2 text-sm/6 text-red-dark">{{ $message }}</p>
	@enderror
</div>
