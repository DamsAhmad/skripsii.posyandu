<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Member;

class ImtAdultChartController extends Controller
{
    public function show($id)
    {
        $member = Member::with(['examinations.checkup'])
            ->findOrFail($id);

        $dataPoints = $member->examinations->map(function ($exam) use ($member) {
            $checkupDate = optional($exam->checkup)->checkup_date;

            if (!$checkupDate || !$member->birthdate) {
                return null; // lewati kalau ada data kosong
            }

            // Hitung usia dari tanggal lahir ke tanggal pemeriksaan
            $days = Carbon::parse($member->birthdate)->diffInDays(Carbon::parse($checkupDate));
            $usiaTahun = round($days / 365.25, 2); // gunakan angka desimal

            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);

            return [
                'x' => $usiaTahun,
                'y' => round($imt, 2),
                'date' => Carbon::parse($checkupDate)->format('Y-m-d'),
                'weight' => $exam->weight,
                'height' => $exam->height,
            ];
        })->filter(); // filter() untuk buang null


        return view('imtadult-chart', [
            'member' => $member,
            'dataPoints' => $dataPoints->values(),
            'category' => $member->category,
        ]);
    }
}
