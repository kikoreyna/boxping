<?php

namespace App\Filament\Resources\Agencias;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Agencias\Pages\CreateAgencia;
use App\Filament\Resources\Agencias\Pages\EditAgencia;
use App\Filament\Resources\Agencias\Pages\ListAgencias;
use App\Filament\Resources\Agencias\Schemas\AgenciaForm;
use App\Filament\Resources\Agencias\Tables\AgenciasTable;
use App\Models\Agencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgenciaResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Agencia::class;

    protected static ?string $modelLabel = 'agencia';

    protected static ?string $pluralModelLabel = 'agencias';

    protected static ?string $navigationLabel = 'Agencias';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AgenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgenciasTable::configure($table);
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
            'index' => ListAgencias::route('/'),
            'create' => CreateAgencia::route('/create'),
            'edit' => EditAgencia::route('/{record}/edit'),
        ];
    }
}
