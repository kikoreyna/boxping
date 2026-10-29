<?php

namespace App\Filament\Resources\Tarifas\Schemas;

use App\Models\Servicio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TarifaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')->label('Código')->required()->maxLength(40),
                TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
                Select::make('agencia_id')
                    ->label('Agencia (vacío para tarifa base)')
                    ->relationship('agencia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('clonada_de_id')
                    ->label('Clonada de')
                    ->relationship('tarifaClonadaDe', 'nombre')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('version')->label('Versión')->numeric()->minValue(1)->default(1)->required(),
                TextInput::make('moneda')->label('Moneda (ISO-4217)')->required()->length(3),
                TextInput::make('divisor_volumetrico')
                    ->label('Divisor volumétrico (pulg³ / lb)')
                    ->helperText('Peso volumétrico = largo × ancho × alto (pulgadas) ÷ divisor. Se cobra el mayor entre el peso real y el volumétrico. Ej.: 139.')
                    ->numeric()
                    ->default(139)
                    ->minValue(0.0001)
                    ->required(),
                TextInput::make('libras_minimas')
                    ->label('Libras mínimas a cobrar')
                    ->helperText('Si el envío pesa menos, se cobra este mínimo; si pesa más, se cobra el peso real. Aplica a servicios por libra. 0 = sin mínimo.')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                DatePicker::make('vigente_desde')->label('Vigente desde'),
                DatePicker::make('vigente_hasta')->label('Vigente hasta'),
                Toggle::make('activa')->label('Activa')->default(true),
                Select::make('impuesto_id')
                    ->label('Impuesto (opcional)')
                    ->relationship('impuesto', 'nombre', fn ($query) => $query->where('activo', true))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Se administra en Paquetería → Impuestos. Sin impuesto, no se cobra ninguno.')
                    ->columnSpanFull(),
                Repeater::make('servicios')
                    ->label('Servicios de la tarifa')
                    ->relationship()
                    ->schema([
                        Select::make('servicio_id')
                            ->label('Servicio')
                            ->options(fn () => Servicio::query()->where('activo', true)->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('precio_agencia')
                            ->label('Precio agencia (por unidad)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('precio_central')
                            ->label('Precio central (por unidad)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->helperText('La unidad depende del servicio: caja, libra o pie cúbico. El precio al remitente es agencia + central.')
                    ->columnSpanFull(),
            ]);
    }
}
