@props(['type' => 'success'])

<div {{ $attributes->class([
    'flex gap-x-3 rounded-xl p-4 text-base/6',
    'bg-ink/4 text-ink' => $type === 'success',
    'bg-red/6 text-red-dark' => $type === 'error',
]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    @if ($type === 'error')
        <x-phosphor-warning-circle-fill class="mt-0.5 size-5 shrink-0" />
    @else
        <x-phosphor-check-circle-fill class="mt-0.5 size-5 shrink-0 text-red" />
    @endif
    <p class="text-pretty">{{ $slot }}</p>
</div>
