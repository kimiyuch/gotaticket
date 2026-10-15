<?php

use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Fans · Admin')] class extends Component
{
	use WithPagination;

	#[Url(as: 'q', except: '')]
	public string $search = '';

	#[Url(except: '')]
	public string $filter = '';

	public function updated(string $property): void
	{
		if (in_array($property, ['search', 'filter'], true)) {
			$this->resetPage();
		}
	}

	/**
	 * @return Paginator<int, User>
	 */
	#[Computed]
	public function users(): Paginator
	{
		$handedOver = [ClaimStatus::Sent, ClaimStatus::Received];

		return User::query()
			->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
				->where('name', 'like', '%'.$this->search.'%')
				->orWhere('email', 'like', '%'.$this->search.'%')))
			->when($this->filter === 'blocked', fn ($query) => $query->whereNotNull('blocked_at'))
			->when($this->filter === 'admins', fn ($query) => $query->where('is_admin', true))
			->when($this->filter === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
			->withCount([
				'claims as received_count' => fn ($query) => $query->whereIn('status', $handedOver),
				'claims as no_shows_count' => fn ($query) => $query->where('outcome', ClaimOutcome::NoShow),
				'incomingClaims as given_count' => fn ($query) => $query->whereIn('claims.status', $handedOver),
			])
			->latest()
			->simplePaginate(50);
	}

	public function block(int $userId): void
	{
		$user = $this->otherUser($userId);
		$user->forceFill(['blocked_at' => now()])->save();

		$this->dispatch('toast', message: "{$user->displayName()} ist gesperrt.");
	}

	public function unblock(int $userId): void
	{
		$user = $this->otherUser($userId);
		$user->forceFill(['blocked_at' => null])->save();

		$this->dispatch('toast', message: "{$user->displayName()} ist wieder freigeschaltet.");
	}

	public function toggleAdmin(int $userId): void
	{
		$user = $this->otherUser($userId);
		$user->forceFill(['is_admin' => ! $user->is_admin])->save();

		$this->dispatch('toast', message: $user->is_admin ? "{$user->displayName()} ist jetzt Admin." : "{$user->displayName()} ist kein Admin mehr.");
	}

	/**
	 * Admins never change their own account here, so nobody locks themselves out.
	 */
	private function otherUser(int $userId): User
	{
		abort_if($userId === auth()->id(), 403);

		return User::findOrFail($userId);
	}
};
?>

<div>
	<x-admin-nav />

	<x-page-header title="Fans" description="Alle Konten mit Zuverlässigkeit: erhaltene Tickets, No-Shows und weitergegebene Tickets." class="mt-8" />

	<div class="mt-8 grid gap-4 sm:grid-cols-[1fr_--spacing(48)]">
		<div>
			<label for="search" class="sr-only">Suchen</label>
			<input id="search" name="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Name oder E-Mail" autocomplete="off"
				class="w-full rounded-lg bg-white px-3 py-2 text-base/6 text-ink ring-1 ring-ink/15 placeholder:text-ink/35 focus:outline-2 focus:-outline-offset-1 focus:outline-red">
		</div>
		<x-form.select label="Filter" name="filter" wire:model.live="filter" class="[&>label]:sr-only [&>div]:mt-0">
			<option value="">Alle</option>
			<option value="blocked">Gesperrt</option>
			<option value="admins">Admins</option>
			<option value="unverified">E-Mail unbestätigt</option>
		</x-form.select>
	</div>

	<div class="mt-6 -mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6">
		<div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6">
			<table class="w-full text-left text-sm/6">
				<thead>
					<tr class="border-b border-ink/10">
						<th scope="col" class="py-2 pr-4 font-medium whitespace-nowrap">Fan</th>
						<th scope="col" class="px-4 py-2 text-right font-medium whitespace-nowrap">Erhalten</th>
						<th scope="col" class="px-4 py-2 text-right font-medium whitespace-nowrap">No-Shows</th>
						<th scope="col" class="py-2 pl-4 text-right font-medium whitespace-nowrap">Gegeben</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-ink/8">
					@forelse ($this->users as $user)
						<tr wire:key="user-{{ $user->id }}">
							<td class="py-3 pr-4 align-top">
								<p class="flex items-center gap-x-2">
									<span class="font-medium">{{ $user->displayName() }}</span>
									@if ($user->is_admin)
										<x-badge color="red">Admin</x-badge>
									@endif
									@if ($user->isBlocked())
										<x-badge color="solid">Gesperrt</x-badge>
									@endif
									@unless ($user->hasVerifiedEmail())
										<x-badge color="muted">Unbestätigt</x-badge>
									@endunless
								</p>
								<p class="text-ink/55">{{ $user->email }} · seit {{ $user->created_at->locale('de_CH')->isoFormat('D. MMM YYYY') }}</p>
								@if ($user->id !== auth()->id())
									<div class="mt-2 flex gap-2">
										<x-button variant="neutral" size="sm" aria-label="{{ $user->is_admin ? 'Admin-Rechte entfernen für' : 'Zum Admin machen:' }} {{ $user->displayName() }}"
											x-on:click="$store.confirm.ask({ title: @js($user->is_admin ? 'Admin-Rechte entfernen?' : 'Zum Admin machen?'), message: @js($user->displayName().' ('.$user->email.')'), confirm: @js($user->is_admin ? 'Entfernen' : 'Admin machen') }).then((ok) => ok && $wire.toggleAdmin({{ $user->id }}))">{{ $user->is_admin ? 'Kein Admin' : 'Admin' }}</x-button>
										@if ($user->isBlocked())
											<x-button variant="neutral" size="sm" aria-label="{{ $user->displayName() }} entsperren" wire:click="unblock({{ $user->id }})">Entsperren</x-button>
										@else
											<x-button variant="neutral" size="sm" aria-label="{{ $user->displayName() }} sperren"
												x-on:click="$store.confirm.ask({ title: @js($user->displayName().' sperren?'), message: 'Gesperrte Fans werden abgemeldet, können sich nicht mehr einloggen und keine Plätze anfragen.', confirm: 'Sperren' }).then((ok) => ok && $wire.block({{ $user->id }}))">Sperren</x-button>
										@endif
									</div>
								@endif
							</td>
							<td class="px-4 py-3 text-right align-top tabular-nums">{{ $user->received_count }}</td>
							<td @class(['px-4 py-3 text-right align-top tabular-nums', 'font-semibold text-red-dark' => $user->no_shows_count > 0])>{{ $user->no_shows_count }}</td>
							<td class="py-3 pl-4 text-right align-top tabular-nums">{{ $user->given_count }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="4" class="py-6 text-base/6 text-ink/60">Keine Fans gefunden.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	@if ($this->users->hasPages())
		<div class="mt-6 flex justify-between gap-3">
			<x-button variant="neutral" size="sm" wire:click="previousPage" :disabled="$this->users->onFirstPage()">Zurück</x-button>
			<x-button variant="neutral" size="sm" wire:click="nextPage" :disabled="! $this->users->hasMorePages()">Weiter</x-button>
		</div>
	@endif
</div>
