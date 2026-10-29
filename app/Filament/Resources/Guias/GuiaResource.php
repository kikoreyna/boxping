<?php

namespace App\Filament\Resources\Guias;

use App\Filament\Resources\Guias\Pages\CreateGuia;
use App\Filament\Resources\Guias\Pages\EditGuia;
use App\Filament\Resources\Guias\Pages\ListGuias;
use App\Filament\Resources\Guias\Schemas\GuiaForm;
use App\Filament\Resources\Guias\Tables\GuiasTable;
use App\Models\Guia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuiaResource extends Resource
{
    protected static ?string $model = Guia::class;

    protected static ?string $modelLabel = 'guía';

    protected static ?string $pluralModelLabel = 'guías';

    protected static ?string $navigationLabel = 'Guías';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        return $user?->esAgencia() ? $query->where('agencia_id', $user->agencia_id) : $query;
    }

    public static function form(Schema $schema): Schema
    {
        return GuiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuiasTable::configure($table);
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
            'index' => ListGuias::route('/'),
            'create' => CreateGuia::route('/create'),
            'edit' => EditGuia::route('/{record}/edit'),
        ];
    }
}
