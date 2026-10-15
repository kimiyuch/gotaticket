@props(['type' => 'success'])

<div {{ $attributes->class([
    'flex gap-x-3 rounded-xl p-4 text-base/6',
    'bg-ink/4 text-ink' => $type === 'success',
    'bg-red/6 text-red-dark' => $type === 'error',
]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    @if ($type === 'error')
        <span class="flex h-6 shrink-0 items-center"><x-phosphor-warning-circle class="size-5" /></span>
    @else
        <span class="flex h-6 shrink-0 items-center"><x-phosphor-check-circle class="size-5 text-red" /></span>
    @endif
    <p class="text-pretty">{{ $slot }}</p>
</div>
