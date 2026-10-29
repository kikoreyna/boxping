<?php

namespace App\Filament\Resources\Tarifas\Pages;

use App\Filament\Resources\Tarifas\TarifaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTarifas extends ListRecords
{
    protected static string $resource = TarifaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
