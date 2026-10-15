<?php

namespace App\Actions\Claims;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\User;
use App\Notifications\TicketSent;
use Illuminate\Validation\ValidationException;

class MarkTicketSent
{
	public function handle(User $giver, Claim $claim): Claim
	{
		abort_unless($claim->offer->user_id === $giver->id, 403);

		if ($claim->status !== ClaimStatus::Accepted) {
			throw ValidationException::withMessages(['claim' => 'Das Ticket kann jetzt nicht als gesendet markiert werden.']);
		}

		$claim->update(['status' => ClaimStatus::Sent, 'sent_at' => now()]);
		$claim->seeker->notify(new TicketSent($claim));

		return $claim;
	}
}
