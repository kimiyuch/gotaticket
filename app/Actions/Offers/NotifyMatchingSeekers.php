<?php

namespace App\Actions\Offers;

use App\Enums\OfferStatus;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\OfferAvailable;
use Illuminate\Support\Facades\Notification;

class NotifyMatchingSeekers
{
    /**
     * Tell every fan looking for this sector that seats are available.
     */
    public function handle(Offer $offer): int
    {
        if ($offer->status !== OfferStatus::Open || $offer->availableSeats() === 0) {
            return 0;
        }

        $seekers = User::query()
            ->whereKeyNot($offer->user_id)
            ->whereNull('blocked_at')
            ->whereHas('sectors', fn ($query) => $query->whereKey($offer->sector_id))
            ->whereDoesntHave('claims', fn ($query) => $query->where('offer_id', $offer->id))
            ->get();

        Notification::send($seekers, new OfferAvailable($offer));

        return $seekers->count();
    }
}
