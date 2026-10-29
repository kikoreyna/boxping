<?php

namespace App\Filament\Resources\Guias\Pages;

use App\Filament\Resources\Guias\GuiaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuia extends EditRecord
{
    protected static string $resource = GuiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
