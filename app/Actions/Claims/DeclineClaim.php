<?php

namespace App\Actions\Claims;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\User;
use App\Notifications\ClaimDeclined;
use Illuminate\Validation\ValidationException;

class DeclineClaim
{
    public function handle(User $giver, Claim $claim): Claim
    {
        abort_unless($claim->offer->user_id === $giver->id, 403);

        if ($claim->status !== ClaimStatus::Requested) {
            throw ValidationException::withMessages(['claim' => 'Diese Anfrage ist nicht mehr offen.']);
        }

        $claim->update(['status' => ClaimStatus::Declined]);
        $claim->seeker->notify(new ClaimDeclined($claim));

        return $claim;
    }
}
