<?php

namespace App\Filament\Resources\Consolidados;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Consolidados\Pages\CreateConsolidado;
use App\Filament\Resources\Consolidados\Pages\EditConsolidado;
use App\Filament\Resources\Consolidados\Pages\ListConsolidados;
use App\Filament\Resources\Consolidados\Schemas\ConsolidadoForm;
use App\Filament\Resources\Consolidados\Tables\ConsolidadosTable;
use App\Models\Consolidado;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConsolidadoResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Consolidado::class;

    protected static ?string $modelLabel = 'consolidado';

    protected static ?string $pluralModelLabel = 'consolidados';

    protected static ?string $navigationLabel = 'Consolidados';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ConsolidadoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsolidadosTable::configure($table);
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
            'index' => ListConsolidados::route('/'),
            'create' => CreateConsolidado::route('/create'),
            'edit' => EditConsolidado::route('/{record}/edit'),
        ];
    }
}
