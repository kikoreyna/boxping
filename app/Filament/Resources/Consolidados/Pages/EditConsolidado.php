<?php

namespace App\Filament\Resources\Consolidados\Pages;

use App\Filament\Resources\Consolidados\ConsolidadoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConsolidado extends EditRecord
{
    protected static string $resource = ConsolidadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
