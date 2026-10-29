<?php

namespace App\Filament\Resources\Tarifas\Tables;

use App\Models\Agencia;
use App\Models\Tarifa;
use App\Services\Tarifas\TarifaVersioningService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TarifasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Tarifa')->searchable()->sortable(),
                TextColumn::make('codigo')->label('Código')->searchable(),
                TextColumn::make('version')->label('Versión')->sortable(),
                TextColumn::make('agencia.nombre')->label('Agencia')->placeholder('Base general'),
                TextColumn::make('moneda')->label('Moneda'),
                TextColumn::make('vigente_desde')->label('Desde')->date('d/m/Y'),
                TextColumn::make('vigente_hasta')->label('Hasta')->date('d/m/Y'),
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
                Action::make('clonar')
                    ->label('Clonar para agencia')
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->schema([
                        Select::make('agencia_id')
                            ->label('Agencia')
                            ->options(Agencia::query()
                                ->where('activa', true)
                                ->whereHas('central', fn ($query) => $query->where('activa', true))
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('nombre')
                            ->label('Nombre de la nueva tarifa')
                            ->maxLength(255),
                    ])
                    ->action(function (Tarifa $record, array $data): void {
                        $agencia = Agencia::query()->findOrFail($data['agencia_id']);
                        app(TarifaVersioningService::class)->clonar($record, $agencia, $data['nombre'] ?? null);

                        Notification::make()
                            ->title('Tarifa clonada correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
