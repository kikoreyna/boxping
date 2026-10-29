<?php

namespace App\Filament\Resources\Consolidados\Pages;

use App\Filament\Resources\Consolidados\ConsolidadoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConsolidados extends ListRecords
{
    protected static string $resource = ConsolidadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
