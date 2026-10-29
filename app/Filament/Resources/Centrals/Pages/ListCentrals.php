<?php

namespace App\Filament\Resources\Centrals\Pages;

use App\Filament\Resources\Centrals\CentralResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCentrals extends ListRecords
{
    protected static string $resource = CentralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
