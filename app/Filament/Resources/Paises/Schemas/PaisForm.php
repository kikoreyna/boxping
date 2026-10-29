<?php

namespace App\Filament\Resources\Paises\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre del país')
                    ->required()
                    ->maxLength(255),
                TextInput::make('codigo_iso2')
                    ->label('Código ISO-2')
                    ->required()
                    ->length(2)
                    ->regex('/^[A-Z]{2}$/')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                Toggle::make('activo')
                    ->label('País activo')
                    ->default(true)
                    ->helperText('Los países inactivos no se podrán elegir como destino ni al registrar ciudades.'),
            ]);
    }
}
