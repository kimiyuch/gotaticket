{{-- Modal shell on the native <dialog>: bottom sheet on mobile, centred card on desktop. Closing fires the dialog's `close` event. --}}
<dialog {{ $attributes->class('m-auto w-full max-w-sm rounded-2xl bg-white p-0 text-ink shadow-xl ring-1 ring-ink/10 backdrop:bg-ink/40 max-sm:mb-0 max-sm:max-w-none max-sm:rounded-b-none') }}>
    <div class="relative p-6 pb-[max(--spacing(6),env(safe-area-inset-bottom))] sm:pb-6">
        <button type="button" x-on:click="$el.closest('dialog').close()" aria-label="Schliessen"
            class="absolute top-6 right-6 flex size-7 items-center justify-center rounded-lg text-ink/40 hover:bg-ink/5 hover:text-ink focus-visible:outline-2 focus-visible:outline-red">
            <span class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" aria-hidden="true"></span>
            <x-phosphor-x class="size-5" />
        </button>
        {{ $slot }}
    </div>
</dialog>
