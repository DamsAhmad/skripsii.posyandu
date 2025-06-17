<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Member;

class ImtuBoyChartController extends Controller
{
    public function show($id)
    {
        $member = Member::with('examinations')->findOrFail($id);
        // dd($member->examinations);
        // dd($member->birthdate);
        $dataPoints = $member->examinations
            ->sortBy('created_at')
            ->filter(function ($examination) {
                return $examination->created_at && $examination->anthropometric_value;
            })
            ->map(function ($examination) use ($member) {
                try {
                    $birth = Carbon::parse($member->birthdate);
                    $checkup = Carbon::parse($examination->created_at);

                    $ageInMonths = $birth->diffInMonths($checkup);
                    $ageInYears = round($ageInMonths / 12, 2);
                    $imt = round($examination->anthropometric_value, 1);

                    return ['x' => $ageInYears, 'y' => $imt];
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter() // buang yang null
            ->values();

        return view('imtuboy-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints,
        ]);
    }
}
