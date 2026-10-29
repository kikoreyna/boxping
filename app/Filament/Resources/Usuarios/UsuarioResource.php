<?php

namespace App\Filament\Resources\Usuarios;

use App\Filament\Concerns\SoloAdministrador;
use App\Filament\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\Resources\Usuarios\Pages\EditUsuario;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsuarioResource extends Resource
{
    use SoloAdministrador;

    protected static ?string $model = User::class;

    protected static ?string $slug = 'usuarios';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 99;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            TextInput::make('email')->label('Correo electrónico')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->required(fn (?User $record): bool => $record === null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(8),
            Toggle::make('is_admin')
                ->label('Administrador (central)')
                ->helperText('Los administradores ven todo. Si no lo es, debe pertenecer a una agencia.')
                ->live()
                ->default(false),
            Select::make('agencia_id')
                ->label('Agencia')
                ->relationship('agencia', 'nombre')
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => ! $get('is_admin'))
                ->helperText('El usuario solo verá y creará guías de esta agencia.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->label('Correo')->searchable(),
                IconColumn::make('is_admin')->label('Administrador')->boolean(),
                TextColumn::make('agencia.nombre')->label('Agencia'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (User $record): bool => $record->is(auth()->user())),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuario::route('/create'),
            'edit' => EditUsuario::route('/{record}/edit'),
        ];
    }
}
