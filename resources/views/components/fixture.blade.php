@props(['fixture', 'size' => 'base'])

{{-- A home game: date line in red on top, then opponent. The slot replaces the competition line. --}}
<span {{ $attributes->class('block') }}>
    <span class="block text-sm/6 font-medium text-red tabular-nums">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }}</span>
    <span @class(['block font-semibold', 'text-xl tracking-tight' => $size === 'lg'])>{{ $fixture->opponent }}</span>
    <span class="block text-sm/6 text-ink/55">{{ $slot->isEmpty() ? $fixture->competition : $slot }}</span>
</span>
