<?php

use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

it('saves seeker preferences and contact details', function () {
    $user = User::factory()->create();
    $sectors = Sector::factory()->count(2)->create();

    Livewire::actingAs($user)
        ->test('pages::settings')
        ->set('name', 'Pippo')
        ->set('phone', '079 123 45 67')
        ->set('sectorIds', $sectors->modelKeys())
        ->set('wantedSeats', 2)
        ->set('notifyByMail', false)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user)
        ->name->toBe('Pippo')
        ->phone->toBe('079 123 45 67')
        ->wanted_seats->toBe(2)
        ->notify_by_mail->toBeFalse()
        ->and($user->sectors->modelKeys())->toEqualCanonicalizing($sectors->modelKeys());
});

it('stores and removes push subscriptions', function () {
    $user = User::factory()->create();
    $payload = ['endpoint' => 'https://push.example.com/abc', 'keys' => ['p256dh' => 'key', 'auth' => 'token'], 'contentEncoding' => 'aes128gcm'];

    $this->actingAs($user)->postJson(route('push-subscriptions.store'), $payload)->assertNoContent();
    expect($user->pushSubscriptions()->count())->toBe(1);

    $this->actingAs($user)->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])->assertNoContent();
    expect($user->pushSubscriptions()->count())->toBe(0);
});

it('confirms saving with a toast', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::settings')
        ->set('name', 'Pippo')
        ->call('save')
        ->assertDispatched('toast', message: 'Einstellungen gespeichert.');
});
