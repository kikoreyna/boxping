<?php

namespace App\Filament\Resources\Paises\Pages;

use App\Filament\Resources\Paises\PaisResource;
use App\Models\Pais;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPaises extends ListRecords
{
    protected static string $resource = PaisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activarTodos')
                ->label('Activar todos')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    Pais::query()->update(['activo' => true]);

                    Notification::make()
                        ->title('Todos los países están activos')
                        ->success()
                        ->send();
                }),
            Action::make('desactivarTodos')
                ->label('Desactivar todos')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    Pais::query()->update(['activo' => false]);

                    Notification::make()
                        ->title('Todos los países están inactivos')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
