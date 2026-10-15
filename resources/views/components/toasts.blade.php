{{-- Confirmation toasts: full width at the very top on mobile, top right on desktop. --}}
<div x-data @if (session('toast')) x-init="$store.toasts.push(@js(session('toast')))" @endif
	class="pointer-events-none fixed inset-x-0 top-0 z-50 flex flex-col sm:top-4 sm:right-4 sm:left-auto sm:w-sm sm:gap-2"
	aria-live="polite">
	<template x-for="toast in $store.toasts.items" :key="toast.id">
		<div x-transition.opacity.duration.200ms
			class="pointer-events-auto flex items-start gap-x-3 bg-white p-4 pt-[max(--spacing(4),env(safe-area-inset-top))] shadow-lg ring-1 ring-ink/10 sm:rounded-xl sm:pt-4"
			x-bind:role="toast.type === 'error' ? 'alert' : 'status'">
			<span class="flex h-6 shrink-0 items-center">
				<template x-if="toast.type === 'error'"><x-phosphor-warning-circle class="size-5 text-red-dark" /></template>
				<template x-if="toast.type !== 'error'"><x-phosphor-check-circle class="size-5 text-red" /></template>
			</span>
			<p class="flex-1 text-base/6 text-pretty" x-text="toast.message"></p>
			<button type="button" x-on:click="$store.toasts.dismiss(toast.id)" class="relative flex h-6 shrink-0 items-center text-ink/40 hover:text-ink" aria-label="Schliessen">
				<span class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" aria-hidden="true"></span>
				<x-phosphor-x class="size-4" />
			</button>
		</div>
	</template>
</div>
