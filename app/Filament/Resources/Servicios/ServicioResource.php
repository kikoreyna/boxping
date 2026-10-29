<?php

namespace App\Filament\Resources\Servicios;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Servicios\Pages\CreateServicio;
use App\Filament\Resources\Servicios\Pages\EditServicio;
use App\Filament\Resources\Servicios\Pages\ListServicios;
use App\Filament\Resources\TiposCobro\TipoCobroResource;
use App\Models\Servicio;
use App\Models\TipoCobro;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicioResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Servicio::class;

    protected static ?string $slug = 'servicios';

    protected static ?string $modelLabel = 'servicio';

    protected static ?string $pluralModelLabel = 'servicios';

    protected static ?string $navigationLabel = 'Servicios';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('codigo')->label('Código')->required()->maxLength(40)->unique(ignoreRecord: true),
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            Select::make('tipo_cobro_id')
                ->label('Tipo de cobro')
                ->relationship('tipoCobro', 'nombre', fn ($query) => $query->where('activo', true))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->createOptionForm(TipoCobroResource::formSchema())
                ->createOptionUsing(fn (array $data): int => TipoCobro::query()->create($data)->getKey()),
            Toggle::make('activo')->label('Activo')->default(true),
            TextInput::make('largo_pulg')->label('Largo (pulg)')->numeric()->minValue(0.01)
                ->visible(fn (Get $get): bool => self::esPieza($get('tipo_cobro_id'))),
            TextInput::make('ancho_pulg')->label('Ancho (pulg)')->numeric()->minValue(0.01)
                ->visible(fn (Get $get): bool => self::esPieza($get('tipo_cobro_id'))),
            TextInput::make('alto_pulg')->label('Alto (pulg)')->numeric()->minValue(0.01)
                ->visible(fn (Get $get): bool => self::esPieza($get('tipo_cobro_id'))),
        ]);
    }

    private static function esPieza(mixed $tipoId): bool
    {
        return $tipoId !== null && TipoCobro::query()->whereKey($tipoId)->where('base_calculo', 'pieza')->exists();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('tipoCobro.nombre')->label('Tipo de cobro'),
                IconColumn::make('activo')->label('Activo')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('nombre');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicios::route('/'),
            'create' => CreateServicio::route('/create'),
            'edit' => EditServicio::route('/{record}/edit'),
        ];
    }
}
