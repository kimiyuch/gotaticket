@props(['fixture', 'size' => 'base', 'inverse' => false])

{{-- A home game: date line on top, then opponent. The slot replaces the competition line. Use `inverse` on red backgrounds. --}}
<span {{ $attributes->class('block') }}>
    <span @class(['block text-sm/6 font-medium tabular-nums', 'text-red' => ! $inverse, 'text-white/80' => $inverse])>{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }}</span>
    <span @class(['block font-semibold', 'text-xl tracking-tight' => $size === 'lg', 'text-white' => $inverse])>{{ $fixture->opponent }}</span>
    <span @class(['block text-sm/6', 'text-ink/55' => ! $inverse, 'text-white/70' => $inverse])>{{ $slot->isEmpty() ? $fixture->competition : $slot }}</span>
</span>
