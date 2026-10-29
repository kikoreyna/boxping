<?php

namespace App\Filament\Resources\Agencias\Schemas;

use App\Models\Agencia;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AgenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('alias')
                    ->label('Alias')
                    ->maxLength(255),
                TextInput::make('nombre')
                    ->label('Nombre de la agencia')
                    ->required()
                    ->maxLength(255),
                Select::make('central_id')
                    ->label('Central')
                    ->relationship(
                        'central',
                        'nombre',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('activa', true),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('La agencia necesita una central activa para cotizar servicios.'),
                Select::make('tarifa_id')
                    ->label('Tarifa de la agencia')
                    ->relationship(
                        'tarifa',
                        'nombre',
                        modifyQueryUsing: fn (Builder $query, ?Agencia $record): Builder => $query
                            ->where('activa', true)
                            ->where(fn (Builder $query): Builder => $query
                                ->whereNull('agencia_id')
                                ->when($record, fn (Builder $query): Builder => $query->orWhere('agencia_id', $record->id))),
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->nombre} (v{$record->version})")
                    ->searchable()
                    ->preload()
                    ->helperText('Las guías de esta agencia se cotizan con esta tarifa.'),
                TextInput::make('codigo_iata')
                    ->label('Código IATA')
                    ->required()
                    ->length(3)
                    ->regex('/^[A-Z]{3}$/')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                TextInput::make('codigo')
                    ->label('Código de agencia (5 dígitos)')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Se asigna automáticamente al guardar'),
                TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(40),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(255),
                TextInput::make('ciudad')
                    ->label('Ciudad')
                    ->maxLength(255),
                TextInput::make('estado')
                    ->label('Estado')
                    ->maxLength(255),
                TextInput::make('pais')
                    ->label('País')
                    ->maxLength(255),
                TextInput::make('codigo_postal')
                    ->label('Código postal')
                    ->maxLength(20),
                TextInput::make('codigo_pais')
                    ->label('Código de país (ISO-2)')
                    ->required()
                    ->length(2)
                    ->regex('/^[A-Z]{2}$/')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('activa')
                    ->label('Activa')
                    ->default(true),
            ]);
    }
}
