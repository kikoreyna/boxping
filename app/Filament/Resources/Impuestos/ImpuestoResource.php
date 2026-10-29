<?php

namespace App\Filament\Resources\Impuestos;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Impuestos\Pages\CreateImpuesto;
use App\Filament\Resources\Impuestos\Pages\EditImpuesto;
use App\Filament\Resources\Impuestos\Pages\ListImpuestos;
use App\Models\Impuesto;
use App\Services\Tarifas\FormulaImpuestos;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImpuestoResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = Impuesto::class;

    protected static ?string $slug = 'impuestos';

    protected static ?string $modelLabel = 'impuesto';

    protected static ?string $pluralModelLabel = 'impuestos';

    protected static ?string $navigationLabel = 'Impuestos';

    protected static string|\UnitEnum|null $navigationGroup = 'Paquetería';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            TextInput::make('formula')
                ->label('Fórmula')
                ->required()
                ->maxLength(255)
                ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                    if (! FormulaImpuestos::esValida($value)) {
                        $fail('Formato inválido. Ejemplo: VALORDECLARADO(2000,0:0,0:30).');
                    }
                })
                ->helperText('VALORDECLARADO(umbral, %Age:%FFW por debajo, %Age:%FFW por encima). %Age es el porcentaje de la agencia y %FFW el de la central, sobre el valor declarado. Ejemplo: VALORDECLARADO(2000,0:0,0:30) = sin impuesto hasta 2000 y 30 % para la central por encima.')
                ->columnSpanFull(),
            Textarea::make('descripcion')->label('Descripción')->columnSpanFull(),
            Toggle::make('activo')->label('Activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('formula')->label('Fórmula'),
                IconColumn::make('activo')->label('Activo')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('nombre');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImpuestos::route('/'),
            'create' => CreateImpuesto::route('/create'),
            'edit' => EditImpuesto::route('/{record}/edit'),
        ];
    }
}
