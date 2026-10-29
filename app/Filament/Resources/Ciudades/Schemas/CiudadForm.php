<?php

namespace App\Filament\Resources\Ciudades\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CiudadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pais_id')
                    ->label('País')
                    ->relationship(
                        'pais',
                        'nombre',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('activo', true),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('estado_id', null)),
                Select::make('estado_id')
                    ->label('Estado o región')
                    ->relationship(
                        'estado',
                        'nombre',
                        modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                            ->where('pais_id', $get('pais_id')),
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('nombre')->label('Ciudad')->required()->maxLength(255),
                TextInput::make('codigo_externo')->label('Código de origen')->maxLength(40),
                Select::make('zona_id')
                    ->label('Zona tarifaria')
                    ->relationship('zona', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Por defecto las ciudades importadas quedan en la zona A.'),
            ]);
    }
}
