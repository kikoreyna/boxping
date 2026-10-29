<?php

namespace App\Filament\Resources\Guias\Pages;

use App\Filament\Resources\Guias\GuiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuias extends ListRecords
{
    protected static string $resource = GuiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
