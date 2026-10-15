<?php

namespace App\Filament\Resources\Sectors;

use App\Filament\Resources\Sectors\Pages\CreateSector;
use App\Filament\Resources\Sectors\Pages\EditSector;
use App\Filament\Resources\Sectors\Pages\ListSectors;
use App\Filament\Resources\Sectors\Schemas\SectorForm;
use App\Filament\Resources\Sectors\Tables\SectorsTable;
use App\Models\Sector;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SectorResource extends Resource
{
	protected static ?string $model = Sector::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

	protected static ?string $modelLabel = 'Sektor';

	protected static ?string $pluralModelLabel = 'Sektoren';

	protected static ?int $navigationSort = 2;

	public static function form(Schema $schema): Schema
	{
		return SectorForm::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return SectorsTable::configure($table);
	}

	public static function getRelations(): array
	{
		return [
			//
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => ListSectors::route('/'),
			'create' => CreateSector::route('/create'),
			'edit' => EditSector::route('/{record}/edit'),
		];
	}
}
