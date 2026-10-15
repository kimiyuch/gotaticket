{{-- Modal shell on the native <dialog>: bottom sheet on mobile, centred card on desktop. --}}
<dialog {{ $attributes->class('m-auto w-full max-w-sm rounded-2xl bg-white p-0 text-ink shadow-xl ring-1 ring-ink/10 backdrop:bg-ink/40 max-sm:mb-0 max-sm:max-w-none max-sm:rounded-b-none') }}>
    <div class="p-6 pb-[max(--spacing(6),env(safe-area-inset-bottom))] sm:pb-6">
        {{ $slot }}
    </div>
</dialog>
