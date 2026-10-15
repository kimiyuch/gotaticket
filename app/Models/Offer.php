<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Enums\OfferStatus;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['fixture_id', 'user_id', 'sector_id', 'seats', 'note', 'status'])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OfferStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Fixture, $this>
     */
    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    /**
     * The season ticket holder giving the seats away.
     *
     * @return BelongsTo<User, $this>
     */
    public function giver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Sector, $this>
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * @return HasMany<Claim, $this>
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Seats not yet promised to an accepted seeker.
     */
    public function availableSeats(): int
    {
        $claims = $this->relationLoaded('claims') ? $this->claims : $this->claims()->get();

        $promised = $claims
            ->whereIn('status', [ClaimStatus::Accepted, ClaimStatus::Sent, ClaimStatus::Received])
            ->sum('seats');

        return max(0, $this->seats - $promised);
    }
}
