<?php

namespace App\Actions\Claims;

use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\ClaimAccepted;
use App\Notifications\ClaimDeclined;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class AcceptClaim
{
	/**
	 * The giver accepts a request; once all seats are promised, the remaining requests are declined.
	 */
	public function handle(User $giver, Claim $claim): Claim
	{
		abort_unless($claim->offer->user_id === $giver->id, 403);

		/** @var Collection<int, Claim> $declined */
		$declined = new Collection;

		DB::transaction(function () use ($claim, &$declined): void {
			$offer = Offer::query()->lockForUpdate()->findOrFail($claim->offer_id);
			$claim->refresh();

			if ($claim->status !== ClaimStatus::Requested || $offer->status !== OfferStatus::Open) {
				throw ValidationException::withMessages(['claim' => 'Diese Anfrage ist nicht mehr offen.']);
			}

			if ($claim->seats > $offer->availableSeats()) {
				throw ValidationException::withMessages(['claim' => 'Dafür sind nicht mehr genug Plätze frei.']);
			}

			$claim->update(['status' => ClaimStatus::Accepted, 'accepted_at' => now()]);

			if ($offer->availableSeats() === 0) {
				$offer->update(['status' => OfferStatus::Taken]);

				$declined = $offer->claims()->where('status', ClaimStatus::Requested)->get();
				$offer->claims()->whereKey($declined->modelKeys())->update(['status' => ClaimStatus::Declined]);
			}
		});

		$claim->seeker->notify(new ClaimAccepted($claim));

		foreach ($declined as $declinedClaim) {
			Notification::send($declinedClaim->seeker, new ClaimDeclined($declinedClaim));
		}

		return $claim;
	}
}
