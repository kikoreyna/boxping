<?php

namespace App\Filament\Resources\Consolidados\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConsolidadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('folio')->label('Folio')->required()->maxLength(40),
                Select::make('agencia_id')
                    ->label('Agencia')
                    ->relationship('agencia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('central_id')
                    ->label('Central')
                    ->relationship('central', 'nombre')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('codigo_pais_destino')
                    ->label('País de destino (ISO-2)')
                    ->required()
                    ->length(2)
                    ->regex('/^[A-Z]{2}$/'),
                Select::make('estatus')
                    ->label('Estado')
                    ->options([
                        'abierto' => 'Abierto',
                        'cerrado' => 'Cerrado',
                        'despachado' => 'Despachado',
                    ])
                    ->required()
                    ->default('abierto'),
                DateTimePicker::make('cerrado_en')->label('Cerrado el'),
                DateTimePicker::make('despachado_en')->label('Despachado el'),
                Select::make('guias')
                    ->label('Guías incluidas')
                    ->relationship('guias', 'folio')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
