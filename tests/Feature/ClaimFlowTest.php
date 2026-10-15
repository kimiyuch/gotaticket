<?php

use App\Actions\Claims\AcceptClaim;
use App\Actions\Claims\CancelClaim;
use App\Actions\Claims\ConfirmReceipt;
use App\Actions\Claims\MarkTicketSent;
use App\Actions\Claims\ReportOutcome;
use App\Actions\Claims\RequestSeats;
use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use App\Models\Claim;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\Sector;
use App\Models\User;
use App\Notifications\ClaimAccepted;
use App\Notifications\ClaimCancelled;
use App\Notifications\ClaimDeclined;
use App\Notifications\OfferAvailable;
use App\Notifications\SeatsRequested;
use App\Notifications\TicketSent;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Notification::fake();

    $this->offer = Offer::factory()->create([
        'seats' => 2,
        'fixture_id' => Fixture::factory()->create(['kickoff_at' => now()->addDays(2)]),
    ]);
    $this->giver = $this->offer->giver;
    $this->seeker = User::factory()->create();
});

it('lets a seeker request seats and notifies the giver', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);

    expect($claim->status)->toBe(ClaimStatus::Requested);
    Notification::assertSentTo($this->giver, SeatsRequested::class);
});

it('does not let a seeker request their own offer, more seats than available, or twice', function (Closure $attempt) {
    expect(fn () => $attempt($this))->toThrow(ValidationException::class);
})->with([
    'own offer' => [fn ($test) => app(RequestSeats::class)->handle($test->giver, $test->offer, 1)],
    'too many seats' => [fn ($test) => app(RequestSeats::class)->handle($test->seeker, $test->offer, 3)],
    'twice' => [function ($test) {
        app(RequestSeats::class)->handle($test->seeker, $test->offer, 1);
        app(RequestSeats::class)->handle($test->seeker, $test->offer, 1);
    }],
    'blocked seeker' => [fn ($test) => app(RequestSeats::class)->handle(User::factory()->create(['blocked_at' => now()]), $test->offer, 1)],
]);

it('marks the offer as taken once all seats are accepted and declines the rest', function () {
    $first = app(RequestSeats::class)->handle($this->seeker, $this->offer, 2);
    $other = app(RequestSeats::class)->handle($otherSeeker = User::factory()->create(), $this->offer, 1);

    app(AcceptClaim::class)->handle($this->giver, $first);

    expect($first->fresh()->status)->toBe(ClaimStatus::Accepted)
        ->and($this->offer->fresh()->status)->toBe(OfferStatus::Taken)
        ->and($other->fresh()->status)->toBe(ClaimStatus::Declined);

    Notification::assertSentTo($this->seeker, ClaimAccepted::class);
    Notification::assertSentTo($otherSeeker, ClaimDeclined::class);
});

it('keeps the offer open while seats remain', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);

    app(AcceptClaim::class)->handle($this->giver, $claim);

    expect($this->offer->fresh())
        ->status->toBe(OfferStatus::Open)
        ->availableSeats()->toBe(1);
});

it('only lets the giver accept a request', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);

    app(AcceptClaim::class)->handle(User::factory()->create(), $claim);
})->throws(HttpException::class);

it('runs through the full handover', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);
    app(AcceptClaim::class)->handle($this->giver, $claim);
    app(MarkTicketSent::class)->handle($this->giver, $claim->fresh());
    app(ConfirmReceipt::class)->handle($this->seeker, $claim->fresh());

    expect($claim->fresh())
        ->status->toBe(ClaimStatus::Received)
        ->sent_at->not->toBeNull()
        ->received_at->not->toBeNull();

    Notification::assertSentTo($this->seeker, TicketSent::class);
});

it('reopens the offer and tells the giver when an accepted seeker cancels', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 2);
    app(AcceptClaim::class)->handle($this->giver, $claim);

    app(CancelClaim::class)->handle($this->seeker, $claim->fresh());

    expect($claim->fresh()->status)->toBe(ClaimStatus::Cancelled)
        ->and($this->offer->fresh()->status)->toBe(OfferStatus::Open)
        ->and($this->offer->fresh()->availableSeats())->toBe(2);

    Notification::assertSentTo($this->giver, ClaimCancelled::class);
});

it('lets the seeker request again after cancelling', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);
    app(CancelClaim::class)->handle($this->seeker, $claim);

    $again = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);

    expect($again->id)->toBe($claim->id)
        ->and($again->status)->toBe(ClaimStatus::Requested);
});

it('records no-shows after kick-off and counts them in the reliability record', function () {
    $claim = Claim::factory()->for($this->offer)->for($this->seeker, 'seeker')->create(['status' => ClaimStatus::Received]);
    $this->offer->fixture->update(['kickoff_at' => now()->subHour()]);

    app(ReportOutcome::class)->handle($this->giver, $claim->fresh(), ClaimOutcome::NoShow);

    expect($this->seeker->reliability())->toMatchArray(['received' => 0, 'no_shows' => 1])
        ->and($this->giver->reliability())->toMatchArray(['given' => 1]);
});

it('does not accept outcome reports before kick-off or from the wrong side', function () {
    $claim = Claim::factory()->for($this->offer)->for($this->seeker, 'seeker')->create(['status' => ClaimStatus::Received]);

    expect(fn () => app(ReportOutcome::class)->handle($this->giver, $claim, ClaimOutcome::NoShow))
        ->toThrow(ValidationException::class);

    $this->offer->fixture->update(['kickoff_at' => now()->subHour()]);

    expect(fn () => app(ReportOutcome::class)->handle($this->seeker, $claim->fresh(), ClaimOutcome::NoShow))
        ->toThrow(HttpException::class);
});

it('notifies seekers who follow the sector when a seat is offered', function () {
    $sector = Sector::factory()->create();
    $follower = User::factory()->create();
    $follower->sectors()->attach($sector);
    $stranger = User::factory()->create();

    Livewire::actingAs($this->giver)
        ->test('pages::offers.create')
        ->set('fixtureId', $this->offer->fixture_id)
        ->set('sectorId', $sector->id)
        ->call('save');

    Notification::assertSentTo($follower, OfferAvailable::class);
    Notification::assertNotSentTo([$stranger, $this->giver], OfferAvailable::class);
});

it('shows open offers to guests and sends them to login when requesting', function () {
    Livewire::test('pages::offers.index')
        ->assertSee($this->offer->sector->label())
        ->call('request', $this->offer->id)
        ->assertRedirect(route('login'));
});

it('lets a seeker request from the browse page', function () {
    Livewire::actingAs($this->seeker)
        ->test('pages::offers.index')
        ->set('seats.'.$this->offer->id, 2)
        ->call('request', $this->offer->id)
        ->assertHasNoErrors()
        ->assertSee('Angefragt');

    expect(Claim::sole())->seats->toBe(2);
});

it('shows contact details to both sides only after acceptance', function () {
    $this->seeker->update(['phone' => '079 123 45 67']);
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);

    Livewire::actingAs($this->giver)->test('pages::dashboard')->assertDontSee('079 123 45 67');

    Livewire::actingAs($this->giver)->test('pages::dashboard')
        ->call('accept', $claim->id)
        ->assertSee('079 123 45 67')
        ->assertSee($this->seeker->email);

    Livewire::actingAs($this->seeker)->test('pages::dashboard')->assertSee($this->giver->email);
});

it('shows all sectors by default and filters to the seeker\'s sectors on request', function () {
    $mine = Sector::factory()->create(['name' => 'Stehplatz Meinkurve']);
    $this->seeker->sectors()->attach($mine);
    Offer::factory()->create(['sector_id' => $mine->id, 'fixture_id' => $this->offer->fixture_id]);

    Livewire::actingAs($this->seeker)
        ->test('pages::offers.index')
        ->assertSee('Meinkurve')
        ->assertSee($this->offer->sector->name)
        ->set('onlyMySectors', true)
        ->assertSee('Meinkurve')
        ->assertDontSee($this->offer->sector->name);
});

it('confirms actions with a toast and reports failures as an error toast', function () {
    $claim = app(RequestSeats::class)->handle($this->seeker, $this->offer, 1);

    Livewire::actingAs($this->giver)
        ->test('pages::dashboard')
        ->call('accept', $claim->id)
        ->assertDispatched('toast', message: 'Zugesagt. Schick das Ticket jetzt direkt an den Fan.')
        ->call('accept', $claim->id)
        ->assertDispatched('toast', type: 'error');
});
