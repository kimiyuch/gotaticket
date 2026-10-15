<?php

namespace App\Models;

use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[Fillable(['name', 'email', 'phone', 'password', 'wanted_seats', 'notify_by_push', 'notify_by_mail'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
	/** @use HasFactory<UserFactory> */
	use HasFactory, HasPushSubscriptions, Notifiable;

	/**
	 * @var array<string, mixed>
	 */
	protected $attributes = [
		'is_admin' => false,
		'wanted_seats' => 1,
		'notify_by_push' => true,
		'notify_by_mail' => true,
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
			'is_admin' => 'boolean',
			'notify_by_push' => 'boolean',
			'notify_by_mail' => 'boolean',
			'blocked_at' => 'datetime',
		];
	}

	/**
	 * Sectors this user wants to be notified about as a seeker.
	 *
	 * @return BelongsToMany<Sector, $this>
	 */
	public function sectors(): BelongsToMany
	{
		return $this->belongsToMany(Sector::class);
	}

	/**
	 * @return HasMany<Offer, $this>
	 */
	public function offers(): HasMany
	{
		return $this->hasMany(Offer::class);
	}

	/**
	 * @return HasMany<Claim, $this>
	 */
	public function claims(): HasMany
	{
		return $this->hasMany(Claim::class);
	}

	/**
	 * Name shown to other fans; falls back to the part of the email before the @.
	 */
	public function displayName(): string
	{
		return $this->name ?: str($this->email)->before('@')->toString();
	}

	/**
	 * Track record shown to the other side before accepting a request.
	 *
	 * @return array{received: int, no_shows: int, given: int, invalid_tickets: int}
	 */
	public function reliability(): array
	{
		$handedOver = [ClaimStatus::Sent, ClaimStatus::Received];

		return [
			'received' => $this->claims()->whereIn('status', $handedOver)->where(fn ($query) => $query->whereNull('outcome')->orWhere('outcome', '!=', ClaimOutcome::NoShow))->count(),
			'no_shows' => $this->claims()->where('outcome', ClaimOutcome::NoShow)->count(),
			'given' => Claim::query()->whereIn('status', $handedOver)->whereHas('offer', fn ($query) => $query->where('user_id', $this->id))->count(),
			'invalid_tickets' => Claim::query()->where('outcome', ClaimOutcome::TicketInvalid)->whereHas('offer', fn ($query) => $query->where('user_id', $this->id))->count(),
		];
	}

	public function isBlocked(): bool
	{
		return $this->blocked_at !== null;
	}

	public function canAccessAdmin(): bool
	{
		return $this->is_admin && ! $this->isBlocked();
	}
}
