<?php

namespace App\Actions\Claims;

use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\SeatsRequested;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestSeats
{
	/**
	 * A seeker confirms "I'm going" for some seats of an offer.
	 */
	public function handle(User $seeker, Offer $offer, int $seats): Claim
	{
		if ($seeker->isBlocked()) {
			throw ValidationException::withMessages(['seats' => 'Dein Konto ist gesperrt.']);
		}

		if ($offer->user_id === $seeker->id) {
			throw ValidationException::withMessages(['seats' => 'Das ist dein eigenes Angebot.']);
		}

		$claim = DB::transaction(function () use ($seeker, $offer, $seats): Claim {
			$offer = Offer::query()->lockForUpdate()->findOrFail($offer->id);

			if ($offer->status !== OfferStatus::Open || $offer->fixture->kickoff_at->isPast()) {
				throw ValidationException::withMessages(['seats' => 'Dieses Angebot ist nicht mehr verfügbar.']);
			}

			$available = $offer->availableSeats();

			if ($seats < 1 || $seats > $available) {
				throw ValidationException::withMessages(['seats' => "Es sind noch {$available} Plätze frei."]);
			}

			$existing = $offer->claims()->where('user_id', $seeker->id)->first();

			if ($existing && in_array($existing->status, ClaimStatus::active(), true)) {
				throw ValidationException::withMessages(['seats' => 'Du hast diese Plätze bereits angefragt.']);
			}

			$claim = $existing ?? new Claim(['offer_id' => $offer->id, 'user_id' => $seeker->id]);
			$claim->fill([
				'seats' => $seats,
				'status' => ClaimStatus::Requested,
				'accepted_at' => null,
				'sent_at' => null,
				'received_at' => null,
			])->save();

			return $claim;
		});

		$offer->giver->notify(new SeatsRequested($claim));

		return $claim;
	}
}
