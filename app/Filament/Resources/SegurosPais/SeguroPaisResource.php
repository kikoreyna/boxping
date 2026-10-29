<?php

namespace App\Filament\Resources\SegurosPais;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\SegurosPais\Pages\CreateSeguroPais;
use App\Filament\Resources\SegurosPais\Pages\EditSeguroPais;
use App\Filament\Resources\SegurosPais\Pages\ListSegurosPais;
use App\Models\Pais;
use App\Models\SeguroPais;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeguroPaisResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = SeguroPais::class;

    protected static ?string $slug = 'seguros-por-pais';

    protected static ?string $modelLabel = 'seguro por país';

    protected static ?string $pluralModelLabel = 'seguros por país';

    protected static ?string $navigationLabel = 'Seguros por país';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('pais_id')
                ->label('País')
                ->options(fn (): array => Pais::query()->where('activo', true)->orderBy('nombre')->pluck('nombre', 'id')->all())
                ->searchable()
                ->required()
                ->unique(ignoreRecord: true),
            Toggle::make('opcional')
                ->label('Seguro opcional')
                ->helperText('Si se desactiva, el seguro se cobra siempre que haya valor declarado.')
                ->default(true),
            Toggle::make('activo')->label('Activo')->default(true),
            TextInput::make('porcentaje_agencia')->label('% para la agencia (sobre valor declarado)')
                ->numeric()->minValue(0)->default(0)->required(),
            TextInput::make('porcentaje_central')->label('% para la central (sobre valor declarado)')
                ->numeric()->minValue(0)->default(0)->required(),
            TextInput::make('minimo_agencia')->label('Mínimo agencia')
                ->numeric()->minValue(0)->default(0)->required(),
            TextInput::make('minimo_central')->label('Mínimo central')
                ->numeric()->minValue(0)->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pais.nombre')->label('País')->searchable()->sortable(),
                IconColumn::make('opcional')->label('Opcional')->boolean(),
                TextColumn::make('porcentaje_agencia')->label('% agencia')->suffix(' %'),
                TextColumn::make('porcentaje_central')->label('% central')->suffix(' %'),
                TextColumn::make('minimo_agencia')->label('Mín. agencia'),
                TextColumn::make('minimo_central')->label('Mín. central'),
                IconColumn::make('activo')->label('Activo')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSegurosPais::route('/'),
            'create' => CreateSeguroPais::route('/create'),
            'edit' => EditSeguroPais::route('/{record}/edit'),
        ];
    }
}
