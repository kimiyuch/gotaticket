<?php

namespace App\Notifications;

use App\Models\Offer;

class OfferAvailable extends FanNotification
{
    public function __construct(public Offer $offer) {}

    public function title(): string
    {
        return 'Got a ticket! '.$this->offer->fixture->opponent;
    }

    public function body(): string
    {
        $seats = $this->offer->availableSeats();

        return "{$seats} × {$this->offer->sector->label()} für FCW – {$this->offer->fixture->opponent} am "
            .$this->offer->fixture->kickoff_at->locale('de_CH')->isoFormat('dd, D. MMMM [um] HH:mm').'.';
    }

    public function url(): string
    {
        return route('offers.index').'#offer-'.$this->offer->id;
    }

    public function actionLabel(): string
    {
        return 'Plätze anfragen';
    }

    protected function tagKey(): string
    {
        return (string) $this->offer->id;
    }
}
