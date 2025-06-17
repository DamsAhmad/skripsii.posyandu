<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;

class BbuBoysChartController extends Controller
{
    public function show($memberId)
    {
        $member = Member::with('examinations.checkup')->findOrFail($memberId);
        $birthDate = Carbon::parse($member->birthdate);

        $dataPoints = $member->examinations
            ->filter(fn($exam) => $exam->checkup && $exam->weight)
            ->map(function ($exam) use ($birthDate) {
                $checkupDate = Carbon::parse($exam->checkup->checkup_date);
                $ageInMonths = round($birthDate->floatDiffInRealMonths($checkupDate), 1);

                return [
                    'age' => $ageInMonths,
                    'weight' => $exam->weight,
                ];
            })
            ->sortBy('age')
            ->values();

        return view('bbuboy-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints,
        ]);
    }
}
