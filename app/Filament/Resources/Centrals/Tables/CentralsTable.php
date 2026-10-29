<?php

namespace App\Filament\Resources\Centrals\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CentralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Central')->searchable()->sortable(),
                TextColumn::make('codigo')->label('Código')->searchable(),
                TextColumn::make('ciudad')->label('Ciudad'),
                TextColumn::make('codigo_pais')->label('País'),
                TextColumn::make('activa')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activa' : 'Inactiva')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
