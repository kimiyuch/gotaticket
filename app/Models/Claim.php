<?php

namespace App\Models;

use App\Enums\ClaimOutcome;
use App\Enums\ClaimStatus;
use Database\Factories\ClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['offer_id', 'user_id', 'seats', 'status', 'outcome', 'accepted_at', 'sent_at', 'received_at'])]
class Claim extends Model
{
	/** @use HasFactory<ClaimFactory> */
	use HasFactory;

	/**
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'status' => ClaimStatus::class,
			'outcome' => ClaimOutcome::class,
			'accepted_at' => 'datetime',
			'sent_at' => 'datetime',
			'received_at' => 'datetime',
		];
	}

	/**
	 * @return BelongsTo<Offer, $this>
	 */
	public function offer(): BelongsTo
	{
		return $this->belongsTo(Offer::class);
	}

	/**
	 * The fan receiving the seats.
	 *
	 * @return BelongsTo<User, $this>
	 */
	public function seeker(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
