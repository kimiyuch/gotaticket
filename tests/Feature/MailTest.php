<?php

use App\Models\Offer;
use App\Models\User;
use App\Notifications\OfferAvailable;
use Illuminate\Auth\Notifications\VerifyEmail;

it('renders notification mails with the brand theme in German', function () {
	$user = User::factory()->create(['name' => 'Pippo']);
	$html = (string) (new OfferAvailable(Offer::factory()->create()))->toMail($user)->render();

	expect($html)
		->toContain('Hoi Pippo!')
		->toContain('mail/logo.png')
		->toContain('#d10a10')
		->toContain('Funktioniert der Button «Plätze anfragen» nicht?')
		->toContain('Benachrichtigungen anpassen')
		->not->toContain('All rights reserved');
});

it('sends the verification mail in German', function () {
	$user = User::factory()->unverified()->create();
	$html = (string) (new VerifyEmail)->toMail($user)->render();

	expect($html)->toContain('Bestätige noch kurz deine E-Mail-Adresse')->toContain('E-Mail bestätigen');
});
