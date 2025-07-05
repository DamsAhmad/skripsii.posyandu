<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use App\Services\NutritionalStatusCalculator;
use App\Models\GrowthReference;

class BbuGirlsChartController extends Controller
{
    public function show($memberId)
    {
        $member = Member::with('examinations.checkup')->findOrFail($memberId);
        $birthDate = Carbon::parse($member->birthdate);

        $dataPoints = $member->examinations
            ->filter(fn($exam) => $exam->checkup && $exam->weight)
            ->map(function ($exam) use ($birthDate, $member) {
                $checkupDate = Carbon::parse($exam->checkup->checkup_date);
                $ageInMonths = round($birthDate->floatDiffInRealMonths($checkupDate), 1);

                return [
                    'age' => $ageInMonths,
                    'weight' => $exam->weight,
                    'z_score' => NutritionalStatusCalculator::generateZscore($member, $exam),
                    'status' => NutritionalStatusCalculator::generateStatus($member, $exam),
                ];
            })
            ->sortBy('age')
            ->values();

        $whoCurves = GrowthReference::where('indicator', 'bbu')
            ->where('gender', $member->gender)
            ->orderBy('age_months')
            ->get()
            ->map(function ($row) {
                return [
                    'age' => $row->age_months,
                    '-3' => $row->sd_minus_3,
                    '-2' => $row->sd_minus_2,
                    '-1' => $row->sd_minus_1,
                    '0'  => $row->median,
                    '+1' => $row->sd_plus_1,
                    '+2' => $row->sd_plus_2,
                    '+3' => $row->sd_plus_3,
                ];
            });

        return view('bbugirl-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints,
            'whoCurves' => $whoCurves,
        ]);
    }
}
