@props(['label', 'name', 'hint' => null])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="text-sm/6 font-medium">
        {{ $label }}
        @if ($hint)
            <span class="font-normal text-ink/50">{{ $hint }}</span>
        @endif
    </label>
    <input id="{{ $name }}" name="{{ $name }}" {{ $attributes->except('class') }}
        class="mt-1.5 w-full rounded-lg bg-white px-3 py-2.5 text-base/6 text-ink ring-1 ring-ink/15 placeholder:text-ink/35 focus:outline-2 focus:-outline-offset-1 focus:outline-red">
    @error($attributes->wire('model')->value() ?: $name)
        <p class="mt-1.5 text-sm/6 text-red-dark">{{ $message }}</p>
    @enderror
</div>
