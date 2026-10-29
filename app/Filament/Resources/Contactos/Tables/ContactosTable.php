<?php

namespace App\Filament\Resources\Contactos\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Contacto')->searchable()->sortable(),
                TextColumn::make('nombre_empresa')->label('Empresa')->searchable(),
                TextColumn::make('tipo')->label('Tipo')->badge(),
                TextColumn::make('email')->label('Correo')->searchable(),
                TextColumn::make('telefono')->label('Teléfono'),
                TextColumn::make('ciudad')->label('Ciudad'),
                TextColumn::make('codigo_pais')->label('País'),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
