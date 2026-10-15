<?php

use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

it('requires login to offer a seat', function () {
	$this->get(route('offers.create'))->assertRedirect(route('login'));
});

it('lets a season ticket holder offer seats for an upcoming fixture', function () {
	$user = User::factory()->create();
	$fixture = Fixture::factory()->create(['kickoff_at' => now()->addDays(3)]);
	$sector = Sector::factory()->create();

	Livewire::actingAs($user)
		->test('pages::offers.create')
		->set('fixtureId', $fixture->id)
		->set('sectorId', $sector->id)
		->set('seats', 2)
		->set('note', 'Reihe 3')
		->call('save')
		->assertHasNoErrors()
		->assertRedirect(route('dashboard'));

	expect(Offer::sole())
		->user_id->toBe($user->id)
		->fixture_id->toBe($fixture->id)
		->sector_id->toBe($sector->id)
		->seats->toBe(2)
		->status->toBe(OfferStatus::Open);
});

it('rejects past fixtures, inactive sectors and too many seats', function () {
	$user = User::factory()->create();
	$pastFixture = Fixture::factory()->create(['kickoff_at' => now()->subHour()]);
	$inactiveSector = Sector::factory()->create(['is_active' => false]);

	Livewire::actingAs($user)
		->test('pages::offers.create')
		->set('fixtureId', $pastFixture->id)
		->set('sectorId', $inactiveSector->id)
		->set('seats', 5)
		->call('save')
		->assertHasErrors(['fixtureId', 'sectorId', 'seats']);

	expect(Offer::count())->toBe(0);
});

it('lets the giver withdraw an open offer and declines pending requests', function () {
	$offer = Offer::factory()->create(['fixture_id' => Fixture::factory()->create(['kickoff_at' => now()->addDay()])]);
	$claim = Claim::factory()->for($offer)->create();

	Livewire::actingAs($offer->giver)
		->test('pages::dashboard')
		->assertSee($offer->fixture->opponent)
		->call('withdraw', $offer->id);

	expect($offer->fresh()->status)->toBe(OfferStatus::Withdrawn)
		->and($claim->fresh()->status)->toBe(ClaimStatus::Declined);
});

it('does not let anyone else withdraw an offer', function () {
	$offer = Offer::factory()->create();

	Livewire::actingAs(User::factory()->create())
		->test('pages::dashboard')
		->call('withdraw', $offer->id)
		->assertNotFound();

	expect($offer->fresh()->status)->toBe(OfferStatus::Open);
});
