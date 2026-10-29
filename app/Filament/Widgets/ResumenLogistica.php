<?php

namespace App\Filament\Widgets;

use App\Models\Agencia;
use App\Models\Consolidado;
use App\Models\Guia;
use App\Models\Tarifa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenLogistica extends StatsOverviewWidget
{
    protected ?string $heading = 'Resumen de operaciones';

    protected function getStats(): array
    {
        return [
            Stat::make('Guías registradas', Guia::query()->count())
                ->description('Total de guías en el sistema')
                ->color('primary'),
            Stat::make('Agencias activas', Agencia::query()->where('activa', true)->count())
                ->description('Agencias habilitadas para operar')
                ->color('success'),
            Stat::make('Consolidados abiertos', Consolidado::query()->where('estatus', 'abierto')->count())
                ->description('Pendientes de cierre o despacho')
                ->color('warning'),
            Stat::make('Tarifas activas', Tarifa::query()->where('activa', true)->count())
                ->description('Versiones disponibles para cotizar')
                ->color('info'),
        ];
    }
}
