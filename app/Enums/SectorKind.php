<?php

namespace App\Enums;

enum SectorKind: string
{
    case Standing = 'standing';
    case Seated = 'seated';

    public function label(): string
    {
        return match ($this) {
            self::Standing => 'Stehplatz',
            self::Seated => 'Sitzplatz',
        };
    }
}
