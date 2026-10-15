<?php

namespace App\Notifications;

use App\Models\Claim;

class ClaimAccepted extends FanNotification
{
	public function __construct(public Claim $claim) {}

	public function title(): string
	{
		return 'Du bist dabei!';
	}

	public function body(): string
	{
		return "{$this->claim->offer->giver->displayName()} gibt dir {$this->claim->seats} × {$this->claim->offer->sector->label()} gegen {$this->claim->offer->fixture->opponent}. Das Ticket bekommst du direkt zugeschickt, die Kontaktdaten findest du in der App.";
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
