<?php

namespace App\Actions\Claims;

use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReportOutcome
{
	/**
	 * After kick-off: the giver reports whether the seeker showed up, the seeker can report an invalid ticket.
	 */
	public function handle(User $reporter, Claim $claim, ClaimOutcome $outcome): Claim
	{
		$isGiver = $claim->offer->user_id === $reporter->id;
		$isSeeker = $claim->user_id === $reporter->id;

		abort_unless(
			($isGiver && in_array($outcome, [ClaimOutcome::Attended, ClaimOutcome::NoShow], true))
			|| ($isSeeker && $outcome === ClaimOutcome::TicketInvalid),
			403,
		);

		if (! in_array($claim->status, [ClaimStatus::Sent, ClaimStatus::Received], true) || $claim->offer->fixture->kickoff_at->isFuture()) {
			throw ValidationException::withMessages(['claim' => 'Das kann erst nach Anpfiff gemeldet werden.']);
		}

		$claim->update(['outcome' => $outcome]);

		return $claim;
	}
}
