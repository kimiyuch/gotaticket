@props(['fixture', 'size' => 'base'])

{{-- A home game: red date line, opponent and the competition as a badge. A slot replaces the badge with a detail line (e.g. seats and sector). --}}
<span {{ $attributes->class('flex items-start justify-between gap-x-3') }}>
    <span class="min-w-0">
        <span class="block text-sm/6 font-medium text-red tabular-nums">{{ $fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM · HH:mm') }}</span>
        <span @class(['block font-semibold', 'text-xl tracking-tight' => $size === 'lg'])>{{ $fixture->opponent }}</span>
        @if ($slot->isNotEmpty())
            <span class="block text-sm/6 text-ink/55">{{ $slot }}</span>
        @endif
    </span>
    @if ($slot->isEmpty() && $fixture->competition)
        <x-badge>{{ $fixture->competition }}</x-badge>
    @endif
</span>
