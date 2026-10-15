<?php

namespace App\Actions\Claims;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConfirmReceipt
{
	/**
	 * The seeker confirms the ticket arrived (also allowed before the giver marked it as sent).
	 */
	public function handle(User $seeker, Claim $claim): Claim
	{
		abort_unless($claim->user_id === $seeker->id, 403);

		if (! in_array($claim->status, [ClaimStatus::Accepted, ClaimStatus::Sent], true)) {
			throw ValidationException::withMessages(['claim' => 'Der Empfang kann jetzt nicht bestätigt werden.']);
		}

		$claim->update([
			'status' => ClaimStatus::Received,
			'sent_at' => $claim->sent_at ?? now(),
			'received_at' => now(),
		]);

		return $claim;
	}
}
