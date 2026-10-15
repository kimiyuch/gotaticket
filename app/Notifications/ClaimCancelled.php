<?php

namespace App\Notifications;

use App\Models\Claim;

class ClaimCancelled extends FanNotification
{
	public function __construct(public Claim $claim) {}

	public function title(): string
	{
		return 'Anfrage zurückgezogen';
	}

	public function body(): string
	{
		return "{$this->claim->seeker->displayName()} kann gegen {$this->claim->offer->fixture->opponent} doch nicht. Dein Platz ist wieder frei und wird anderen Fans angezeigt.";
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
