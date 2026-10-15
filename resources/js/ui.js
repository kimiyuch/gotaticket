/**
 * Global UI stores: toasts (confirmations) and a promise-based confirm dialog.
 *
 *   $store.toasts.push('Gespeichert.')
 *   $store.confirm.ask({ title: 'Wirklich?', confirm: 'Ja' }).then((ok) => ok && $wire.doIt())
 *
 * Livewire components trigger toasts with $this->dispatch('toast', message: '…', type: 'error').
 */
export function registerUi(Alpine) {
	Alpine.store('toasts', {
		items: [],

		push(message, type = 'success') {
			const id = crypto.randomUUID();
			this.items.push({ id, message, type });
			setTimeout(() => this.dismiss(id), type === 'error' ? 7000 : 4000);
		},

		dismiss(id) {
			this.items = this.items.filter((toast) => toast.id !== id);
		},
	});

	Alpine.store('confirm', {
		open: false,
		title: '',
		message: '',
		confirmLabel: 'OK',
		cancelLabel: 'Abbrechen',
		resolve: null,

		ask({ title, message = '', confirm = 'OK', cancel = 'Abbrechen' }) {
			Object.assign(this, { title, message, confirmLabel: confirm, cancelLabel: cancel, open: true });

			return new Promise((resolve) => {
				this.resolve = resolve;
			});
		},

		answer(confirmed) {
			if (!this.open) {
				return;
			}

			this.open = false;
			this.resolve?.(confirmed);
			this.resolve = null;
		},
	});

	window.addEventListener('toast', (event) => {
		Alpine.store('toasts').push(event.detail.message, event.detail.type ?? 'success');
	});
}
