<?php

namespace App\Filament\Resources\Zonas\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ZonasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Zona')->searchable()->sortable(),
                TextColumn::make('codigo')->label('Código')->searchable(),
                TextColumn::make('codigo_pais')
                    ->label('País')
                    ->formatStateUsing(fn (string $state): string => $state === 'ZZ' ? 'Todas las ciudades' : $state)
                    ->searchable(),
                TextColumn::make('estado_region')->label('Estado o región'),
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
