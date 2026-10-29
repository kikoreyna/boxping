<?php

namespace App\Filament\Resources\Consolidados\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConsolidadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('folio')->label('Folio')->searchable()->sortable(),
                TextColumn::make('central.nombre')->label('Central')->placeholder('Sin asignar'),
                TextColumn::make('agencia.nombre')->label('Agencia')->placeholder('Varias agencias'),
                TextColumn::make('codigo_pais_destino')->label('Destino'),
                TextColumn::make('estatus')->label('Estado')->badge(),
                TextColumn::make('guias_count')->label('Guías')->counts('guias'),
                TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
