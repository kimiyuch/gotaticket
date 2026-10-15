<?php

namespace App\Actions\Claims;

use App\Actions\Offers\NotifyMatchingSeekers;
use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\User;
use App\Notifications\ClaimCancelled;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelClaim
{
    public function __construct(public NotifyMatchingSeekers $notifyMatchingSeekers) {}

    /**
     * The seeker can't make it after all; promised seats go back to the pool.
     */
    public function handle(User $seeker, Claim $claim): Claim
    {
        abort_unless($claim->user_id === $seeker->id, 403);

        if (! in_array($claim->status, [ClaimStatus::Requested, ClaimStatus::Accepted, ClaimStatus::Sent], true)) {
            throw ValidationException::withMessages(['claim' => 'Diese Anfrage kann nicht mehr zurückgezogen werden.']);
        }

        if ($claim->offer->fixture->kickoff_at->isPast()) {
            throw ValidationException::withMessages(['claim' => 'Der Match hat bereits begonnen.']);
        }

        $wasPromised = $claim->status !== ClaimStatus::Requested;

        DB::transaction(function () use ($claim): void {
            $claim->update(['status' => ClaimStatus::Cancelled]);

            if ($claim->offer->status === OfferStatus::Taken) {
                $claim->offer->update(['status' => OfferStatus::Open]);
            }
        });

        if ($wasPromised) {
            $claim->offer->giver->notify(new ClaimCancelled($claim));
            $this->notifyMatchingSeekers->handle($claim->offer->fresh());
        }

        return $claim;
    }
}
