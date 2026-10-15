<?php

namespace App\Notifications;

use App\Models\Claim;

class ClaimDeclined extends FanNotification
{
	public function __construct(public Claim $claim) {}

	public function title(): string
	{
		return 'Leider nicht geklappt';
	}

	public function body(): string
	{
		return "Die Plätze gegen {$this->claim->offer->fixture->opponent} sind bereits vergeben. Wir melden uns, wenn wieder etwas frei wird.";
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
