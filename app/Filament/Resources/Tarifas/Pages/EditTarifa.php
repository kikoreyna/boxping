<?php

namespace App\Filament\Resources\Tarifas\Pages;

use App\Filament\Resources\Tarifas\TarifaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTarifa extends EditRecord
{
    protected static string $resource = TarifaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
