@props(['color' => 'neutral'])

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-xs/5 font-medium',
    'bg-ink/5 text-ink/70' => $color === 'neutral',
    'bg-red/8 text-red-dark' => $color === 'red',
    'bg-red text-white' => $color === 'solid',
    'bg-ink/5 text-ink/40' => $color === 'muted',
]) }}>{{ $slot }}</span>
