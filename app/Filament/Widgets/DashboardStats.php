<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Member;
use App\Models\Checkup;
use Carbon\Carbon;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Member::count();

        $lastCheckup = Checkup::where('status', 'completed')->latest('checkup_date')->first();
        $lastCheckupDate = $lastCheckup && $lastCheckup->checkup_date
            ? Carbon::parse($lastCheckup->checkup_date)->format('d M Y')
            : '-';
        $lastCheckupCount = $lastCheckup?->examinations()->count() ?? 0;

        $nextCheckup = Checkup::where('status', 'active')
            ->whereDate('checkup_date', '>=', Carbon::today())
            ->orderBy('checkup_date')
            ->first();

        $nextCheckupDate = $nextCheckup?->checkup_date
            ? Carbon::parse($nextCheckup->checkup_date)->format('d M Y')
            : '-';
        return [
            Stat::make('Total Peserta', $total),
            Stat::make('Peserta Pemeriksaan Terakhir', $lastCheckupCount),
            Stat::make('Pemeriksaan Terakhir', $lastCheckupDate),
            Stat::make('Pemeriksaan Akan Datang', $nextCheckupDate),
        ];
    }
}
