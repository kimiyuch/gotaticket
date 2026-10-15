<?php

namespace App\Filament\Resources\Fixtures\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FixtureForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				TextInput::make('opponent')
					->label('Gegner')
					->required(),
				DateTimePicker::make('kickoff_at')
					->label('Anpfiff')
					->seconds(false)
					->required(),
				Select::make('competition')
					->label('Wettbewerb')
					->options([
						'Challenge League' => 'Challenge League',
						'Schweizer Cup' => 'Schweizer Cup',
						'Testspiel' => 'Testspiel',
					])
					->default('Challenge League')
					->required(),
			]);
	}
}
