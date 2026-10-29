<?php

namespace App\Filament\Resources\Zonas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ZonaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')->label('Código de zona')->required()->maxLength(30),
                TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
                TextInput::make('codigo_pais')
                    ->label('País (ISO-2)')
                    ->required()
                    ->length(2)
                    ->regex('/^[A-Z]{2}$/'),
                TextInput::make('estado_region')->label('Estado o región')->maxLength(255),
                TextInput::make('codigo_postal_inicio')->label('Código postal inicial')->maxLength(20),
                TextInput::make('codigo_postal_fin')->label('Código postal final')->maxLength(20),
                Toggle::make('activa')->label('Activa')->default(true),
            ]);
    }
}
