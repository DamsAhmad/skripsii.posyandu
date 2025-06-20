<?php

namespace App\Http\Controllers;

use App\Models\Checkup;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExaminationExportController extends Controller
{
    public function export(Checkup $checkup)
    {
        $categories = ['balita', 'anak-remaja', 'dewasa', 'lansia', 'ibu hamil'];

        $groupedExaminations = [];
        foreach ($categories as $cat) {
            $groupedExaminations[$cat] = $checkup->examinations()
                ->whereHas('member', fn($q) => $q->where('category', $cat))
                ->with('member')
                ->get();
        }
        $columns = [
            'balita' => [
                'No',
                'No. KK',
                'NIK',
                'Nama Anak',
                'Tgl Lahir',
                'L/P',
                'Ayah',
                'Ibu',
                'NIK Ortu',
                'HP Ortu',
                'BB',
                'TB',
                'LLA',
                'LIKA',
                'Status Gizi',
            ],
            'anak-remaja' => [
                'No',
                'No. KK',
                'NIK',
                'Nama',
                'Tgl Lahir',
                'L/P',
                'Ayah',
                'Ibu',
                'NIK Ortu',
                'HP Ortu',
                'BB',
                'TB',
                'LLA',
                'Lingkar Perut',
                'Tensi',
                'Status Gizi',
            ],
            'dewasa' => [
                'No',
                'No. KK',
                'NIK',
                'Nama',
                'Tgl Lahir',
                'L/P',
                'BB',
                'TB',
                'LLA',
                'Lingkar Perut',
                'Tensi',
                'Kolestrol',
                'Asam Urat',
                'Gula Darah',
                'Status Gizi',
            ],
            'lansia' => [
                'No',
                'No. KK',
                'NIK',
                'Nama',
                'Tgl Lahir',
                'L/P',
                'BB',
                'TB',
                'LLA',
                'Lingkar Perut',
                'Tensi',
                'Kolestrol',
                'Asam Urat',
                'Gula Darah',
                'Status Gizi',
            ],
            'ibu hamil' => [
                'No',
                'No. KK',
                'NIK',
                'Nama',
                'Tgl Lahir',
                'L/P',
                'BB',
                'TB',
                'LLA',
                'Lingkar Perut',
                'Tensi',
                'Kolestrol',
                'Asam Urat',
                'Gula Darah',
                'Usia Kehamilan',
                'Status Gizi',
            ],
        ];

        $rekap = collect($groupedExaminations)->map(fn($group) => $group->count());
        $rekap['total'] = $rekap->sum();

        $pdf = Pdf::loadView('pdf.examinations', [
            'categories' => $groupedExaminations,
            'checkup' => $checkup,
            'columns' => $columns,
            'rekap' => $rekap,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('laporan-pemeriksaan.pdf');
    }
}
