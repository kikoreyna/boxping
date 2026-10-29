<?php

namespace App\Filament\Resources\Ciudades\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CiudadesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Ciudad')->searchable()->sortable(),
                TextColumn::make('estado.nombre')->label('Estado o región')->searchable(),
                TextColumn::make('pais.nombre')->label('País')->searchable(),
                TextColumn::make('zona.codigo')->label('Zona')->badge()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
