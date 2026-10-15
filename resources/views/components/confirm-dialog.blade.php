{{-- Global confirm dialog, driven by $store.confirm.ask({...}). --}}
<x-dialog x-data
    x-effect="$store.confirm.open ? ($el.open || $el.showModal()) : ($el.open && $el.close())"
    x-on:close="$store.confirm.answer(false)"
    x-on:click.self="$store.confirm.answer(false)"
    aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message">
    <h2 id="confirm-dialog-title" class="text-lg font-semibold text-balance" x-text="$store.confirm.title"></h2>
    <p id="confirm-dialog-message" class="mt-2 text-base/6 text-pretty text-ink/60" x-show="$store.confirm.message" x-text="$store.confirm.message"></p>
    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <x-button variant="neutral" x-on:click="$store.confirm.answer(false)" x-text="$store.confirm.cancelLabel" />
        <x-button x-on:click="$store.confirm.answer(true)" x-text="$store.confirm.confirmLabel" />
    </div>
</x-dialog>
