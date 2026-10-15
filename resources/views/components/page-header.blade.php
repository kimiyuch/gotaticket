@props(['title', 'description' => null])

<div {{ $attributes->class('flex items-end justify-between gap-x-4') }}>
    <div>
        <h1 class="text-3xl font-semibold tracking-tight text-balance">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 max-w-[56ch] text-base text-pretty text-ink/60">{{ $description }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
