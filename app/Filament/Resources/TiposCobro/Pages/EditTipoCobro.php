<?php

namespace App\Filament\Resources\TiposCobro\Pages;

use App\Filament\Resources\TiposCobro\TipoCobroResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoCobro extends EditRecord
{
    protected static string $resource = TipoCobroResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
