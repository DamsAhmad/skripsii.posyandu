<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Member;
use App\Models\GrowthReference;
use App\Services\NutritionalStatusCalculator;

class ImtuBoyChartController extends Controller
{
    public function show($id)
    {
        $member = Member::with(['examinations.checkup'])->findOrFail($id);
        $birthDate = Carbon::parse($member->birthdate);

        $dataPoints = $member->examinations
            ->sortBy(function ($examination) {
                return optional($examination->checkup)->checkup_date;
            })
            ->filter(function ($examination) {
                return $examination->checkup && $examination->anthropometric_value;
            })
            ->map(function ($examination) use ($member, $birthDate) {
                $checkupDate = optional($examination->checkup)->checkup_date;

                if (!$checkupDate) {
                    logger("Missing checkup_date for examination ID {$examination->id}");
                    return null;
                }

                $checkupDate = Carbon::parse($checkupDate);
                $ageInMonths = $birthDate->diffInMonths($checkupDate);
                $ageInYears = round($ageInMonths / 12, 2);

                $imt = round((float) $examination->anthropometric_value, 1);

                if (!is_numeric($ageInYears) || !is_numeric($imt)) {
                    logger("NaN Detected - Age: {$ageInYears}, IMT: {$imt}");
                    return null;
                }

                $status = NutritionalStatusCalculator::generateStatus($member, $examination);
                $zScore = NutritionalStatusCalculator::generateZscore($member, $examination);

                return [
                    'x' => $ageInYears,
                    'y' => $imt,
                    'z_score' => $zScore,
                    'status' => $status,
                ];
            })
            ->filter()
            ->values();

        $whoCurves = GrowthReference::where('indicator', 'imtu')
            ->where('gender', $member->gender)
            ->orderBy('age_months')
            ->get()
            ->mapWithKeys(function ($row) {
                $ageInYears = round($row->age_months / 12, 2);
                return [
                    $ageInYears => [
                        '-3' => $row->sd_minus_3,
                        '-2' => $row->sd_minus_2,
                        '-1' => $row->sd_minus_1,
                        '0'  => $row->median,
                        '+1' => $row->sd_plus_1,
                        '+2' => $row->sd_plus_2,
                        '+3' => $row->sd_plus_3,
                    ],
                ];
            });

        return view('imtuboy-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints,
            'whoCurves' => $whoCurves,
        ]);
    }
}
