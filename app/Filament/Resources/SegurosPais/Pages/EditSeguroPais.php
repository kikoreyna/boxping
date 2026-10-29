<?php

namespace App\Filament\Resources\SegurosPais\Pages;

use App\Filament\Resources\SegurosPais\SeguroPaisResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeguroPais extends EditRecord
{
    protected static string $resource = SeguroPaisResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
