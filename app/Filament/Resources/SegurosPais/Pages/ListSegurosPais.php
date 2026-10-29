<?php

namespace App\Filament\Resources\SegurosPais\Pages;

use App\Filament\Resources\SegurosPais\SeguroPaisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSegurosPais extends ListRecords
{
    protected static string $resource = SeguroPaisResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
