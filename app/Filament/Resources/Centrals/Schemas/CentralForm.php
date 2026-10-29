<?php

namespace App\Filament\Resources\Centrals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CentralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
                TextInput::make('codigo')->label('Código')->required()->maxLength(20),
                TextInput::make('ciudad')->label('Ciudad')->maxLength(255),
                TextInput::make('codigo_pais')
                    ->label('País (ISO-2)')
                    ->required()
                    ->length(2)
                    ->regex('/^[A-Z]{2}$/')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                Toggle::make('activa')->label('Activa')->default(true),
            ]);
    }
}
