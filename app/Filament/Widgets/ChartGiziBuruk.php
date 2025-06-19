<?php

namespace App\Filament\Widgets;

use App\Models\Examination;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ChartGiziBuruk extends ChartWidget
{
    protected static ?string $heading = 'Grafik Gizi Buruk Tahun Ini';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = collect();
        $year = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $count = Examination::whereHas('checkup', function ($query) use ($year, $month) {
                $query->whereYear('checkup_date', $year)
                    ->whereMonth('checkup_date', $month);
            })
                ->where('weight_status', 'Gizi Buruk')
                ->count();


            $monthName = Carbon::createFromDate($year, $month, 1)
                ->locale('id')
                ->translatedFormat('F');

            $data->put($monthName, $count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Gizi Buruk',
                    'data' => $data->values(),
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => '#fda4af',
                ],
            ],
            'labels' => $data->keys(),
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
