<?php

use App\Enums\SectorKind;
use App\Models\Claim;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
	$this->admin = User::factory()->admin()->create();
});

it('renders every admin page for admins', function (string $route) {
	Claim::factory()->create();

	$this->actingAs($this->admin)->get(route($route))->assertOk();
})->with(['admin.index', 'admin.fixtures.index', 'admin.fixtures.create', 'admin.sectors.index', 'admin.sectors.create', 'admin.users', 'admin.claims']);

it('hides the admin from fans and guests', function () {
	$this->actingAs(User::factory()->create())->get(route('admin.users'))->assertNotFound();
	auth()->logout();
	$this->get(route('admin.index'))->assertNotFound();
});

it('creates and edits a match', function () {
	Livewire::actingAs($this->admin)
		->test('pages::admin.fixtures.form')
		->set('opponent', 'FC Aarau')
		->set('kickoffAt', '2027-02-14T16:30')
		->set('competition', 'Challenge League')
		->call('save')
		->assertHasNoErrors()
		->assertRedirect(route('admin.fixtures.index'));

	$fixture = Fixture::where('opponent', 'FC Aarau')->sole();
	expect($fixture->kickoff_at->format('Y-m-d H:i'))->toBe('2027-02-14 16:30');

	Livewire::actingAs($this->admin)
		->test('pages::admin.fixtures.form', ['fixture' => $fixture])
		->assertSet('opponent', 'FC Aarau')
		->set('opponent', 'FC Aarau 1902')
		->call('save')
		->assertHasNoErrors();

	expect($fixture->fresh()->opponent)->toBe('FC Aarau 1902');
});

it('only deletes matches without offers', function () {
	$offer = Offer::factory()->create();
	$empty = Fixture::factory()->create();

	Livewire::actingAs($this->admin)->test('pages::admin.fixtures.form', ['fixture' => $offer->fixture])->call('delete');
	Livewire::actingAs($this->admin)->test('pages::admin.fixtures.form', ['fixture' => $empty])->call('delete');

	expect($offer->fixture->fresh())->not->toBeNull()
		->and($empty->fresh())->toBeNull();
});

it('creates a sector', function () {
	Livewire::actingAs($this->admin)
		->test('pages::admin.sectors.form')
		->set('name', 'Stehplatz Sirupkurve')
		->set('code', 'E')
		->set('kind', SectorKind::Standing->value)
		->call('save')
		->assertHasNoErrors();

	expect(Sector::where('code', 'E')->sole())
		->name->toBe('Stehplatz Sirupkurve')
		->is_active->toBeTrue();
});

it('blocks a fan, who is then logged out', function () {
	$fan = User::factory()->create();

	Livewire::actingAs($this->admin)->test('pages::admin.users')->call('block', $fan->id);

	expect($fan->fresh()->isBlocked())->toBeTrue();

	$this->actingAs($fan->fresh())->get(route('dashboard'))->assertRedirect(route('login'));
	$this->assertGuest();
});

it('makes a fan admin but never changes the own account', function () {
	$fan = User::factory()->create();

	Livewire::actingAs($this->admin)
		->test('pages::admin.users')
		->call('toggleAdmin', $fan->id)
		->call('block', $this->admin->id)
		->assertForbidden();

	expect($fan->fresh()->is_admin)->toBeTrue()
		->and($this->admin->fresh()->isBlocked())->toBeFalse();
});

it('filters fans by search', function () {
	User::factory()->create(['name' => 'Pippo']);
	User::factory()->create(['name' => 'Patrick']);

	Livewire::actingAs($this->admin)
		->test('pages::admin.users')
		->set('search', 'Pip')
		->assertSee('Pippo')
		->assertDontSee('Patrick');
});
