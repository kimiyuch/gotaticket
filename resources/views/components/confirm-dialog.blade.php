{{-- Global confirm dialog, driven by $store.confirm.ask({...}). Bottom sheet on mobile, centred on desktop. --}}
<dialog x-data
    x-effect="$store.confirm.open ? ($el.open || $el.showModal()) : ($el.open && $el.close())"
    x-on:close="$store.confirm.answer(false)"
    x-on:click.self="$store.confirm.answer(false)"
    aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message"
    class="m-auto w-full max-w-sm rounded-2xl bg-white p-0 text-ink shadow-xl ring-1 ring-ink/10 backdrop:bg-ink/40 max-sm:mb-0 max-sm:max-w-none max-sm:rounded-b-none">
    <div class="p-6 pb-[max(--spacing(6),env(safe-area-inset-bottom))] sm:pb-6">
        <h2 id="confirm-dialog-title" class="text-lg font-semibold text-balance" x-text="$store.confirm.title"></h2>
        <p id="confirm-dialog-message" class="mt-2 text-base/6 text-pretty text-ink/60" x-show="$store.confirm.message" x-text="$store.confirm.message"></p>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-button variant="neutral" x-on:click="$store.confirm.answer(false)" x-text="$store.confirm.cancelLabel" />
            <x-button x-on:click="$store.confirm.answer(true)" x-text="$store.confirm.confirmLabel" />
        </div>
    </div>
</dialog>
