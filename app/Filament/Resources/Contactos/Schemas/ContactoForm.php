<?php

namespace App\Filament\Resources\Contactos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->label('Tipo de contacto')
                    ->options(['persona' => 'Persona', 'empresa' => 'Empresa'])
                    ->required()
                    ->default('persona'),
                TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
                TextInput::make('nombre_empresa')->label('Empresa')->maxLength(255),
                TextInput::make('identificacion_fiscal')->label('Identificación fiscal')->maxLength(255),
                ...self::camposContacto(),
                TextInput::make('ciudad')->label('Ciudad')->maxLength(255),
                TextInput::make('estado_region')->label('Estado o región')->maxLength(255),
                TextInput::make('codigo_pais')
                    ->label('País (ISO-2)')
                    ->length(2)
                    ->regex('/^[A-Z]{2}$/')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
            ]);
    }

    /**
     * Campos para el alta rápida de remitentes y destinatarios desde la guía.
     *
     * @return array<int, mixed>
     */
    public static function altaRapida(): array
    {
        return [
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            ...self::camposContacto(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function camposContacto(): array
    {
        return [
            TextInput::make('direccion')->label('Dirección')->maxLength(255)->columnSpanFull(),
            TextInput::make('entre_calles')->label('Entre calles')->maxLength(255)->columnSpanFull(),
            TextInput::make('codigo_postal')->label('Código postal')->maxLength(20),
            TextInput::make('email')->label('Correo electrónico')->email()->maxLength(255),
            TextInput::make('telefono')->label('Teléfono')->tel()->maxLength(40),
            TextInput::make('telefono2')->label('Teléfono 2')->tel()->maxLength(40),
            TextInput::make('telefono3')->label('Teléfono 3')->tel()->maxLength(40),
        ];
    }
}
