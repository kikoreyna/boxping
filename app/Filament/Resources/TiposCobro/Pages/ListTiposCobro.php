<?php

namespace App\Filament\Resources\TiposCobro\Pages;

use App\Filament\Resources\TiposCobro\TipoCobroResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTiposCobro extends ListRecords
{
    protected static string $resource = TipoCobroResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
