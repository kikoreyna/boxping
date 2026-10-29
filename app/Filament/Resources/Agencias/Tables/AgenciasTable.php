<?php

namespace App\Filament\Resources\Agencias\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('alias')->label('Alias')->searchable(),
                TextColumn::make('nombre')->label('Agencia')->searchable()->sortable(),
                TextColumn::make('codigo_iata')->label('IATA')->searchable(),
                TextColumn::make('codigo')->label('Código')->searchable(),
                TextColumn::make('central.nombre')->label('Central')->placeholder('Sin asignar'),
                TextColumn::make('ciudad')->label('Ciudad'),
                TextColumn::make('pais')->label('País')->searchable(),
                TextColumn::make('telefono')->label('Teléfono'),
                TextColumn::make('email')->label('Correo electrónico'),
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
