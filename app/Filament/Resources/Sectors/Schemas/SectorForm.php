<?php

namespace App\Filament\Resources\Sectors\Schemas;

use App\Enums\SectorKind;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SectorForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				TextInput::make('code')
					->label('Code')
					->placeholder('z.B. D2'),
				TextInput::make('name')
					->label('Name')
					->required(),
				Select::make('kind')
					->label('Art')
					->options(SectorKind::class)
					->required(),
				Toggle::make('is_active')
					->label('Aktiv')
					->default(true),
			]);
	}
}
