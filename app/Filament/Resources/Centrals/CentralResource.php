<?php

namespace App\Filament\Resources\Centrals;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Centrals\Pages\CreateCentral;
use App\Filament\Resources\Centrals\Pages\EditCentral;
use App\Filament\Resources\Centrals\Pages\ListCentrals;
use App\Filament\Resources\Centrals\Schemas\CentralForm;
use App\Filament\Resources\Centrals\Tables\CentralsTable;
use App\Models\Central;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CentralResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Central::class;

    protected static ?string $slug = 'centrales';

    protected static ?string $modelLabel = 'central';

    protected static ?string $pluralModelLabel = 'centrales';

    protected static ?string $navigationLabel = 'Centrales';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CentralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentralsTable::configure($table);
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
            'index' => ListCentrals::route('/'),
            'create' => CreateCentral::route('/create'),
            'edit' => EditCentral::route('/{record}/edit'),
        ];
    }
}
