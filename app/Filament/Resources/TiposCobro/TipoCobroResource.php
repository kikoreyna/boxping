<?php

namespace App\Filament\Resources\TiposCobro;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\TiposCobro\Pages\CreateTipoCobro;
use App\Filament\Resources\TiposCobro\Pages\EditTipoCobro;
use App\Filament\Resources\TiposCobro\Pages\ListTiposCobro;
use App\Models\TipoCobro;
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

class TipoCobroResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = TipoCobro::class;

    protected static ?string $slug = 'tipos-de-cobro';

    protected static ?string $modelLabel = 'tipo de cobro';

    protected static ?string $pluralModelLabel = 'tipos de cobro';

    protected static ?string $navigationLabel = 'Tipos de cobro';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    /**
     * @return array<int, mixed>
     */
    public static function formSchema(): array
    {
        return [
            TextInput::make('codigo')->label('Código')->required()->maxLength(40)->unique(ignoreRecord: true),
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            Select::make('base_calculo')
                ->label('Cómo se calcula')
                ->options(TipoCobro::BASES)
                ->required()
                ->helperText('Define qué unidad se multiplica por el precio del servicio en la tarifa.'),
            Toggle::make('activo')->label('Activo')->default(true),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('base_calculo')->label('Cómo se calcula')
                    ->formatStateUsing(fn (string $state): string => TipoCobro::BASES[$state] ?? $state),
                IconColumn::make('activo')->label('Activo')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('nombre');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTiposCobro::route('/'),
            'create' => CreateTipoCobro::route('/create'),
            'edit' => EditTipoCobro::route('/{record}/edit'),
        ];
    }
}
