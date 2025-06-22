<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Examination;
use Filament\Widgets\ChartWidget;

class ChartGiziObesitas extends ChartWidget
{
    protected static ?string $heading = 'Grafik Peserta Obesitas Tahun Ini';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = collect();
        $year = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $count = Examination::whereHas('checkup', function ($query) use ($year, $month) {
                $query->whereYear('checkup_date', $year)
                    ->whereMonth('checkup_date', $month);
            })
                ->where('weight_status', 'like', '%Obesitas%')
                ->count();

            $monthName = Carbon::createFromDate($year, $month, 1)
                ->locale('id')
                ->translatedFormat('F');

            $data->put($monthName, $count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Obesitas',
                    'data' => $data->values(),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => '#7dd3fc',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
