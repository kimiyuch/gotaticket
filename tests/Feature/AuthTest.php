<?php

use App\Models\Offer;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('registers a fan and sends a verification mail', function () {
	Notification::fake();

	$this->post(route('register'), [
		'name' => 'Pippo',
		'email' => 'Pippo@Example.ch',
		'password' => 'hopp-winti-1896',
		'password_confirmation' => 'hopp-winti-1896',
	])->assertRedirect();

	$user = User::where('email', 'pippo@example.ch')->sole();

	$this->assertAuthenticatedAs($user);
	expect($user->hasVerifiedEmail())->toBeFalse();
	Notification::assertSentTo($user, VerifyEmail::class);
});

it('rejects a second account for the same email regardless of case', function () {
	User::factory()->create(['email' => 'pippo@example.ch']);

	$this->post(route('register'), [
		'name' => 'Pippo',
		'email' => 'PIPPO@example.ch',
		'password' => 'hopp-winti-1896',
		'password_confirmation' => 'hopp-winti-1896',
	])->assertSessionHasErrors('email');

	expect(User::count())->toBe(1);
});

it('logs a fan in with email and password', function () {
	$user = User::factory()->create(['email' => 'fan@example.ch']);

	$this->post(route('login'), ['email' => 'Fan@Example.ch', 'password' => 'password'])
		->assertRedirect(route('dashboard'));

	$this->assertAuthenticatedAs($user);
});

it('rejects a wrong password with a German message', function () {
	User::factory()->create(['email' => 'fan@example.ch']);

	$this->post(route('login'), ['email' => 'fan@example.ch', 'password' => 'wrong'])
		->assertSessionHasErrors(['email' => 'E-Mail oder Passwort stimmen nicht.']);

	$this->assertGuest();
});

it('keeps blocked fans out', function () {
	User::factory()->create(['email' => 'fan@example.ch', 'blocked_at' => now()]);

	$this->post(route('login'), ['email' => 'fan@example.ch', 'password' => 'password'])
		->assertSessionHasErrors(['email' => 'Dieses Konto ist gesperrt.']);

	$this->assertGuest();
});

it('requires a verified email before offering a seat', function () {
	$this->actingAs(User::factory()->unverified()->create())
		->get(route('offers.create'))
		->assertRedirect(route('verification.notice'));
});

it('sends unverified fans to the verification page when requesting', function () {
	$offer = Offer::factory()->create();

	Livewire::actingAs(User::factory()->unverified()->create())
		->test('pages::offers.index')
		->call('request', $offer->id)
		->assertRedirect(route('verification.notice'));

	expect($offer->claims()->count())->toBe(0);
});

it('asks for verification again after changing the email', function () {
	Notification::fake();
	$user = User::factory()->create();

	Livewire::actingAs($user)
		->test('pages::profile')
		->set('email', 'neu@example.ch')
		->call('save')
		->assertHasNoErrors();

	expect($user->fresh())
		->email->toBe('neu@example.ch')
		->hasVerifiedEmail()->toBeFalse();
	Notification::assertSentTo($user, VerifyEmail::class);
});

it('changes the password only with the current one', function () {
	$user = User::factory()->create();

	Livewire::actingAs($user)
		->test('pages::profile')
		->set('currentPassword', 'wrong')
		->set('password', 'neues-passwort')
		->set('passwordConfirmation', 'neues-passwort')
		->call('updatePassword')
		->assertHasErrors('currentPassword')
		->set('currentPassword', 'password')
		->call('updatePassword')
		->assertHasNoErrors();

	expect(Hash::check('neues-passwort', $user->fresh()->password))->toBeTrue();
});

it('lets fans without a password set one', function () {
	$user = User::factory()->create(['password' => null]);

	Livewire::actingAs($user)
		->test('pages::profile')
		->set('password', 'neues-passwort')
		->set('passwordConfirmation', 'neues-passwort')
		->call('updatePassword')
		->assertHasNoErrors();

	expect(Hash::check('neues-passwort', $user->fresh()->password))->toBeTrue();
});

it('renders the auth pages', function (string $route) {
	$this->get(route($route))->assertOk();
})->with(['login', 'register', 'password.request']);

it('shows guests only the login link on the homepage', function () {
	$this->get(route('home'))
		->assertSee(route('login'))
		->assertDontSee('Platz freigeben')
		->assertDontSee('Meine Tickets');
});

it('shows styled German error pages', function () {
	$this->get('/gibts-nicht')->assertNotFound()->assertSee('Diese Seite gibt’s nicht')->assertSee('Zur Startseite');

});
