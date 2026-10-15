<?php

namespace App\Models;

use App\Enums\SectorKind;
use Database\Factories\SectorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name', 'kind', 'sort', 'is_active'])]
class Sector extends Model
{
	/** @use HasFactory<SectorFactory> */
	use HasFactory;

	/**
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'kind' => SectorKind::class,
			'is_active' => 'boolean',
		];
	}

	/**
	 * Seekers who are interested in this sector.
	 *
	 * @return BelongsToMany<User, $this>
	 */
	public function seekers(): BelongsToMany
	{
		return $this->belongsToMany(User::class);
	}

	/**
	 * @param Builder<self> $query
	 */
	public function scopeActive(Builder $query): void
	{
		$query->where('is_active', true)->orderBy('sort');
	}

	public function label(): string
	{
		return $this->code ? "Sektor {$this->code} – {$this->name}" : $this->name;
	}
}
