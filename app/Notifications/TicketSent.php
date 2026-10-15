<?php

namespace App\Notifications;

use App\Models\Claim;

class TicketSent extends FanNotification
{
    public function __construct(public Claim $claim) {}

    public function title(): string
    {
        return 'Dein Ticket ist unterwegs';
    }

    public function body(): string
    {
        return "{$this->claim->offer->giver->displayName()} hat dir das Ticket für {$this->claim->offer->fixture->opponent} geschickt. Bitte bestätige kurz, wenn es angekommen ist.";
    }

    public function url(): string
    {
        return route('dashboard');
    }

    protected function tagKey(): string
    {
        return (string) $this->claim->id;
    }
}
