<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\StatsOverviewWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            LatestOrdersWidget::class,
        ];
    }
}
