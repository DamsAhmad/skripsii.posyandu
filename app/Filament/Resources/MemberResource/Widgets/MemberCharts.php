<?php

namespace App\Filament\Resources\MemberResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;
use App\Models\Examination;
use App\Models\Member;
use Carbon\Carbon;

class MemberCharts extends Widget
{
    protected static string $view = 'components.growth-chart';
    protected static ?string $heading = 'Grafik Pertumbuhan Berat Badan';
    public ?int $memberId = null;

    protected function getViewData(): array
    {
        return [
            'title' => static::$heading,
            'chartData' => $this->prepareChartData(),
            'zScoreColors' => $this->zScoreColors()
        ];
    }

    protected function prepareChartData(): array
    {
        return array_merge(
            $this->getWhoReferenceCurves(),
            [$this->getActualGrowthData()]
        );
    }

    protected function getWhoReferenceCurves(): array
    {
        $whoData = json_decode(Storage::disk('public')->get('view_chart.json'), true);
        $curves = [];
        Storage::get('public/view_chart.json');
        // dd(Storage::get('app/public/view_chart.json'));

        foreach (['-3', '-2', '-1', '0', '+1', '+2'] as $zScore) {
            $data = [];
            foreach ($whoData['ages'] as $index => $age) {
                $data[] = [
                    'x' => $age,
                    'y' => $whoData[$zScore][$index] ?? null
                ];
            }

            $curves[] = [
                'label' => 'Z-score ' . $zScore,
                'data' => $data,
                'borderColor' => $this->zScoreColors()[$zScore],
                'borderWidth' => 1,
                'pointRadius' => 0,
                'fill' => false
            ];
        }

        return $curves;
    }

    protected function getActualGrowthData(): array
    {
        $member = Member::findOrFail($this->memberId);
        $examinations = Examination::where('member_id', $this->memberId)
            ->orderBy('created_at')
            ->get();

        $data = $examinations->map(function ($exam) use ($member) {
            return [
                'x' => Carbon::parse($member->birthdate)->diffInMonths($exam->created_at),
                'y' => $exam->weight
            ];
        })->toArray();

        return [
            'label' => 'Berat Badan Anak',
            'data' => $data,
            'borderColor' => '#000000',
            'backgroundColor' => '#000000',
            'borderWidth' => 2,
            'pointRadius' => 4
        ];
    }

    protected function zScoreColors(): array
    {
        return [
            '-3' => 'rgba(255, 99, 132, 0.8)',
            '-2' => 'rgba(255, 159, 64, 0.8)',
            '-1' => 'rgba(255, 205, 86, 0.8)',
            '0' => 'rgba(75, 192, 192, 0.8)',
            '+1' => 'rgba(54, 162, 235, 0.8)',
            '+2' => 'rgba(153, 102, 255, 0.8)'
        ];
    }
}
