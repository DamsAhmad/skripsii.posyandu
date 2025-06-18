<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Member;

class ImtuGirlChartController extends Controller
{
    public function show($id)
    {
        $member = Member::with(['examinations.checkup'])->findOrFail($id);

        $dataPoints = $member->examinations
            ->sortBy(function ($examination) {
                return optional($examination->checkup)->checkup_date;
            })
            ->filter(function ($examination) {
                return $examination->checkup && $examination->anthropometric_value;
            })
            ->map(function ($examination) use ($member) {
                $birth = Carbon::parse($member->birthdate);
                $checkupDate = optional($examination->checkup)->checkup_date;

                if (!$checkupDate) {
                    logger("Missing checkup_date for examination ID {$examination->id}");
                    return null;
                }

                $checkupDate = Carbon::parse($checkupDate);
                $ageInMonths = $birth->diffInMonths($checkupDate);
                $ageInYears = round($ageInMonths / 12, 2);

                $imt = round((float) $examination->anthropometric_value, 1);

                if (!is_numeric($ageInYears) || !is_numeric($imt)) {
                    logger("NaN Detected - Age: {$ageInYears}, IMT: {$imt}");
                    return null;
                }

                return ['x' => $ageInYears, 'y' => $imt];
            })
            ->filter()
            ->values();

        return view('imtugirl-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints,
        ]);
    }
}
