<?php

namespace App\Filament\Resources\Zonas;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Zonas\Pages\CreateZona;
use App\Filament\Resources\Zonas\Pages\EditZona;
use App\Filament\Resources\Zonas\Pages\ListZonas;
use App\Filament\Resources\Zonas\Schemas\ZonaForm;
use App\Filament\Resources\Zonas\Tables\ZonasTable;
use App\Models\Zona;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ZonaResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Zona::class;

    protected static ?string $modelLabel = 'zona';

    protected static ?string $pluralModelLabel = 'zonas';

    protected static ?string $navigationLabel = 'Zonas de destino';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ZonaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ZonasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZonas::route('/'),
            'create' => CreateZona::route('/create'),
            'edit' => EditZona::route('/{record}/edit'),
        ];
    }
}
