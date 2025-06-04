<?php

namespace App\Filament\Resources\MemberResource\Widgets;

use App\Models\Result;
use Filament\Widgets\ChartWidget;

class GrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pertumbuhan';
    protected static ?string $pollingInterval = null;
    public ?\App\Models\Member $record = null;

    protected function getData(): array
    {
        $results = $this->record->results()
            ->orderBy('checkup.checkup_date')
            ->with('checkup')
            ->get();

        $labels = $results->map(fn($r) => $r->checkup->checkup_date->format('M Y'));

        $datasets = [
            [
                'label' => 'Berat Badan (kg)',
                'data' => $results->pluck('weight'),
                'borderColor' => '#3b82f6',
                'yAxisID' => 'y',
            ]
        ];

        // Hanya tambahkan tinggi jika tersedia
        if ($results->whereNotNull('height')->isNotEmpty()) {
            $datasets[] = [
                'label' => 'Tinggi Badan (cm)',
                'data' => $results->pluck('height'),
                'borderColor' => '#ef4444',
                'yAxisID' => 'y',
            ];
        }

        // Tambahkan Z-Score jika tersedia
        if ($results->whereNotNull('z_score')->isNotEmpty()) {
            $datasets[] = [
                'label' => 'Z-Score',
                'data' => $results->pluck('z_score'),
                'borderColor' => '#10b981',
                'yAxisID' => 'z',
                'borderDash' => [5, 5],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
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
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Berat/Tinggi'],
                ],
                'z' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => ['display' => true, 'text' => 'Z-Score'],
                    'grid' => ['drawOnChartArea' => false],
                    'suggestedMin' => -3,
                    'suggestedMax' => 3,
                ],
            ],
            'plugins' => [
                'legend' => ['position' => 'top'],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(2);
                        }"
                    ]
                ]
            ]
        ];
    }
}
