<?php

namespace App\Filament\Resources\Centrals\Pages;

use App\Filament\Resources\Centrals\CentralResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCentral extends EditRecord
{
    protected static string $resource = CentralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
