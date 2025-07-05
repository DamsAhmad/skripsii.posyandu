<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use App\Services\NutritionalStatusCalculator;

class PregnantChartController extends Controller
{
    public function show($id)
    {
        $member = Member::with(['examinations.checkup'])->findOrFail($id);

        $dataPoints = $member->examinations
            ->filter(fn($exam) => $exam->gestational_week && $exam->arm_circumference)
            ->map(function ($exam) use ($member) {
                $result = NutritionalStatusCalculator::calculatePregnantStatus($exam->arm_circumference, $exam->gestational_week);
                return [
                    'week' => $exam->gestational_week,
                    'value' => $exam->arm_circumference,
                    'status' => $result['status'],
                ];
            })
            ->values();

        return view('pregnant-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints,
        ]);

        dd($dataPoints);
    }
}
