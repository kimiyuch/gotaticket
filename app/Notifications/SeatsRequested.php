<?php

namespace App\Notifications;

use App\Models\Claim;

class SeatsRequested extends FanNotification
{
	public function __construct(public Claim $claim) {}

	public function title(): string
	{
		return 'Anfrage für deinen Platz';
	}

	public function body(): string
	{
		return "{$this->claim->seeker->displayName()} möchte {$this->claim->seats} × {$this->claim->offer->sector->label()} gegen {$this->claim->offer->fixture->opponent}. Bestätige die Anfrage, dann seht ihr gegenseitig eure Kontaktdaten.";
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
