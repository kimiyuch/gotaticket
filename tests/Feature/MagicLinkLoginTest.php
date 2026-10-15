<?php

use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

it('registers a new fan and sends a login link', function () {
	Notification::fake();

	Livewire::test('pages::login')
		->set('email', 'Fan@Example.ch')
		->call('send')
		->assertHasNoErrors()
		->assertSet('sent', true);

	$user = User::where('email', 'fan@example.ch')->sole();

	Notification::assertSentTo($user, MagicLinkNotification::class);
});

it('sends a login link to an existing fan without creating a duplicate', function () {
	Notification::fake();
	$user = User::factory()->create(['email' => 'fan@example.ch']);

	Livewire::test('pages::login')->set('email', 'fan@example.ch')->call('send');

	expect(User::count())->toBe(1);
	Notification::assertSentTo($user, MagicLinkNotification::class);
});

it('rate limits login link requests', function () {
	Notification::fake();

	$component = Livewire::test('pages::login')->set('email', 'fan@example.ch');

	foreach (range(1, 3) as $attempt) {
		$component->call('send')->assertHasNoErrors();
	}

	$component->call('send')->assertHasErrors('email');
});

it('logs the fan in with a valid signed link', function () {
	$user = User::factory()->unverified()->create();

	$this->get(URL::temporarySignedRoute('login.magic', now()->addMinutes(30), ['user' => $user]))
		->assertRedirect(route('dashboard'));

	$this->assertAuthenticatedAs($user);
	expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('rejects a tampered or expired link', function () {
	$user = User::factory()->create();

	$this->get(route('login.magic', ['user' => $user]))->assertForbidden();
	$this->get(URL::temporarySignedRoute('login.magic', now()->subMinute(), ['user' => $user]))->assertForbidden();

	$this->assertGuest();
});

it('does not log in a blocked fan', function () {
	$user = User::factory()->create(['blocked_at' => now()]);

	$this->get(URL::temporarySignedRoute('login.magic', now()->addMinutes(30), ['user' => $user]))
		->assertRedirect(route('login'));

	$this->assertGuest();
});
