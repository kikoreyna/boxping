<?php

namespace App\Filament\Resources\Guias\Schemas;

use App\Filament\Resources\Contactos\Schemas\ContactoForm;
use App\Models\Agencia;
use App\Models\Ciudad;
use App\Models\Guia;
use App\Models\Pais;
use App\Models\Servicio;
use App\Models\Tarifa;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class GuiaForm
{
    public static function agenciaDe(?Guia $record, ?Get $get = null): ?Agencia
    {
        if ($record !== null) {
            return $record->agencia;
        }

        $usuario = auth()->user();
        $elegida = $get === null ? null : $get('agencia_id');

        if ($elegida && ! $usuario?->esAgencia()) {
            return Agencia::query()->where('activa', true)->find($elegida);
        }

        return $usuario?->agenciaOperativa();
    }

    public static function tarifaDe(?Guia $record, ?Get $get = null): ?Tarifa
    {
        return $record !== null ? $record->tarifa : self::agenciaDe(null, $get)?->tarifaAplicable();
    }

    /** @return array<int, string> */
    public static function serviciosDe(?int $tarifaId): array
    {
        if ($tarifaId === null) {
            return [];
        }

        return Servicio::query()
            ->where('activo', true)
            ->whereHas('tarifaServicios', fn (Builder $query): Builder => $query->where('tarifa_id', $tarifaId))
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('folio')
                    ->label('Folio de guía')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Se genera al guardar'),
                Select::make('agencia_id')
                    ->label('Agencia')
                    ->options(fn (): array => Agencia::query()->where('activa', true)->orderBy('nombre')->pluck('nombre', 'id')->all())
                    ->default(fn (): ?int => auth()->user()?->agenciaOperativa()?->id)
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('servicio_id', null))
                    ->helperText(fn (Get $get): string => 'Tarifa aplicada: '.(self::tarifaDe(null, $get)?->nombre ?? 'sin tarifa asignada a la agencia'))
                    ->visible(fn (?Guia $record): bool => $record === null && ! auth()->user()?->esAgencia()),
                TextInput::make('agencia_info')
                    ->label('Agencia')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?Guia $record): string => self::agenciaDe($record)?->nombre ?? 'Sin agencia asignada al usuario')
                    ->helperText(fn (?Guia $record): string => 'Tarifa aplicada: '.(self::tarifaDe($record)?->nombre ?? 'sin tarifa asignada a la agencia'))
                    ->visible(fn (?Guia $record): bool => $record !== null || (bool) auth()->user()?->esAgencia()),
                Select::make('remitente_id')
                    ->label('Remitente')
                    ->relationship('remitente', 'nombre')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(ContactoForm::altaRapida())
                    ->createOptionModalHeading('Nuevo remitente')
                    ->required(),
                Select::make('destinatario_id')
                    ->label('Destinatario')
                    ->relationship('destinatario', 'nombre')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(ContactoForm::altaRapida())
                    ->createOptionModalHeading('Nuevo destinatario')
                    ->required(),
                Select::make('pais_destino_id')
                    ->label('País de destino')
                    ->options(Pais::query()->where('activo', true)->orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->disabled(fn (?Guia $record): bool => $record !== null),
                Select::make('ciudad_destino_id')
                    ->label('Ciudad de destino')
                    ->relationship(
                        'ciudadDestino',
                        'nombre',
                        modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                            ->where('pais_id', $get('pais_destino_id'))
                            ->with('estado'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Ciudad $record): string => collect([
                        $record->nombre,
                        $record->estado?->nombre,
                    ])->filter()->implode(', '))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Get $get, ?Guia $record): bool => $record !== null || blank($get('pais_destino_id'))),
                Select::make('servicio_id')
                    ->label('Servicio')
                    ->options(fn (?Guia $record, Get $get): array => self::serviciosDe(self::tarifaDe($record, $get)?->id))
                    ->searchable()
                    ->required()
                    ->placeholder(fn (?Guia $record, Get $get): string => self::tarifaDe($record, $get) === null ? 'La agencia no tiene tarifa asignada' : 'Selecciona un servicio')
                    ->helperText(fn (?Guia $record, Get $get): ?string => self::tarifaDe($record, $get) !== null && self::serviciosDe(self::tarifaDe($record, $get)->id) === []
                        ? 'La tarifa de la agencia no tiene servicios. Agrégalos en Tarifas → editar → Servicios de la tarifa.'
                        : null)
                    ->disabled(fn (?Guia $record, Get $get): bool => $record !== null || self::tarifaDe($record, $get) === null),
                Toggle::make('asegurada')
                    ->label('Asegurar envío')
                    ->helperText('Aplica el seguro configurado para el país de destino.')
                    ->disabled(fn (?Guia $record): bool => $record !== null),
                TextInput::make('peso_libras')
                    ->label('Peso real (lb)')
                    ->numeric()
                    ->minValue(0.001)
                    ->required()
                    ->disabled(fn (?Guia $record): bool => $record !== null),
                TextInput::make('largo_pulg')
                    ->label('Largo (pulg)')
                    ->numeric()
                    ->minValue(0.01)
                    ->nullable()
                    ->disabled(fn (?Guia $record): bool => $record !== null),
                TextInput::make('ancho_pulg')
                    ->label('Ancho (pulg)')
                    ->numeric()
                    ->minValue(0.01)
                    ->nullable()
                    ->disabled(fn (?Guia $record): bool => $record !== null),
                TextInput::make('alto_pulg')
                    ->label('Alto (pulg)')
                    ->numeric()
                    ->minValue(0.01)
                    ->nullable()
                    ->disabled(fn (?Guia $record): bool => $record !== null),
                TextInput::make('valor_declarado')
                    ->label('Valor declarado')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->disabled(fn (?Guia $record): bool => $record !== null),
            ]);
    }
}
