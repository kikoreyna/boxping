<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SoloAdministrador
{
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->is_admin;
    }
}
