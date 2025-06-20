<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\ChartGiziBuruk;
use App\Filament\Widgets\ChartGiziObesitas;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Dashboard Grafik';

    protected static string $view = 'filament.pages.dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            DashboardStats::class,
            ChartGiziBuruk::class,
            ChartGiziObesitas::class,
        ];
    }
}
