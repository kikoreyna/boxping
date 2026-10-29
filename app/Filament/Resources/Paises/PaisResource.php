<?php

namespace App\Filament\Resources\Paises;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Paises\Pages\CreatePais;
use App\Filament\Resources\Paises\Pages\EditPais;
use App\Filament\Resources\Paises\Pages\ListPaises;
use App\Filament\Resources\Paises\Schemas\PaisForm;
use App\Filament\Resources\Paises\Tables\PaisesTable;
use App\Models\Pais;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaisResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Pais::class;

    protected static ?string $slug = 'paises';

    protected static ?string $modelLabel = 'país';

    protected static ?string $pluralModelLabel = 'países';

    protected static ?string $navigationLabel = 'Países';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function form(Schema $schema): Schema
    {
        return PaisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaisesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaises::route('/'),
            'create' => CreatePais::route('/create'),
            'edit' => EditPais::route('/{record}/edit'),
        ];
    }
}
