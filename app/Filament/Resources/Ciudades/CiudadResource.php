<?php

namespace App\Filament\Resources\Ciudades;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Ciudades\Pages\CreateCiudad;
use App\Filament\Resources\Ciudades\Pages\EditCiudad;
use App\Filament\Resources\Ciudades\Pages\ListCiudades;
use App\Filament\Resources\Ciudades\Schemas\CiudadForm;
use App\Filament\Resources\Ciudades\Tables\CiudadesTable;
use App\Models\Ciudad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CiudadResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Ciudad::class;

    protected static ?string $slug = 'ciudades';

    protected static ?string $modelLabel = 'ciudad';

    protected static ?string $pluralModelLabel = 'ciudades';

    protected static ?string $navigationLabel = 'Ciudades';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CiudadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CiudadesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCiudades::route('/'),
            'create' => CreateCiudad::route('/create'),
            'edit' => EditCiudad::route('/{record}/edit'),
        ];
    }
}
