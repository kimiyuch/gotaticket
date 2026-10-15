@props(['fixture', 'size' => 'base'])

{{-- A home game: date column first, then opponent. The slot replaces the competition line. --}}
<span {{ $attributes->class('flex gap-x-4') }}>
    <span class="w-20 shrink-0 tabular-nums">
        <span @class(['block font-semibold', 'text-xl tracking-tight' => $size === 'lg'])>{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd D.M.') }}</span>
        <span class="block text-sm/6 text-ink/55">{{ $fixture->kickoff_at->format('H:i') }}</span>
    </span>
    <span class="min-w-0">
        <span @class(['block font-semibold', 'text-xl tracking-tight' => $size === 'lg'])>{{ $fixture->opponent }}</span>
        <span class="block text-sm/6 text-ink/55">{{ $slot->isEmpty() ? $fixture->competition : $slot }}</span>
    </span>
</span>
