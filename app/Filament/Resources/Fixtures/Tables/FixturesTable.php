<?php

namespace App\Filament\Resources\Fixtures\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FixturesTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('kickoff_at')
					->label('Anpfiff')
					->dateTime('D, d.m.Y H:i')
					->sortable(),
				TextColumn::make('opponent')
					->label('Gegner')
					->searchable(),
				TextColumn::make('competition')
					->label('Wettbewerb')
					->badge(),
				TextColumn::make('offers_count')
					->label('Angebote')
					->counts('offers'),
				TextColumn::make('created_at')
					->dateTime()
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
				TextColumn::make('updated_at')
					->dateTime()
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
			])
			->defaultSort('kickoff_at')
			->filters([
				Filter::make('upcoming')
					->label('Nur kommende')
					->query(fn (Builder $query): Builder => $query->where('kickoff_at', '>', now()))
					->default(),
			])
			->recordActions([
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
