<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SectorKind: string implements HasLabel
{
	case Standing = 'standing';
	case Seated = 'seated';

	public function getLabel(): string
	{
		return $this->label();
	}

	public function label(): string
	{
		return match ($this) {
			self::Standing => 'Stehplatz',
			self::Seated => 'Sitzplatz',
		};
	}
}
