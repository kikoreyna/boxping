<?php

namespace App\Filament\Resources\Guias\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuiasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('folio')->label('Folio')->searchable()->sortable(),
                TextColumn::make('remitente.nombre')->label('Remitente')->searchable(),
                TextColumn::make('destinatario.nombre')->label('Destinatario')->searchable(),
                TextColumn::make('agencia.nombre')->label('Agencia')->searchable(),
                TextColumn::make('estatus')->label('Estado')->badge(),
                TextColumn::make('codigo_pais_destino')->label('Destino'),
                TextColumn::make('peso_libras')->label('Peso (lb)')->numeric(decimalPlaces: 3),
                TextColumn::make('cargo_zona')
                    ->label('Cargo por zona')
                    ->money(fn ($record): string => $record->moneda),
                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn ($record): string => $record->moneda)
                    ->sortable(),
                TextColumn::make('created_at')->label('Creada')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
