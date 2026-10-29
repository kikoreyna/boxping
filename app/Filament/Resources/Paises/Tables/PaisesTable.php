<?php

namespace App\Filament\Resources\Paises\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PaisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('País')->searchable()->sortable(),
                TextColumn::make('codigo_iso2')->label('ISO-2')->searchable()->sortable(),
                IconColumn::make('activo')->label('Activo')->boolean(),
                TextColumn::make('ciudades_count')
                    ->counts('ciudades')
                    ->label('Ciudades')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activar')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['activo' => true]))
                        ->successNotificationTitle('Países seleccionados activados'),
                    BulkAction::make('desactivar')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['activo' => false]))
                        ->successNotificationTitle('Países seleccionados desactivados'),
                ]),
            ]);
    }
}
