<?php

namespace App\Http\Controllers;

use App\Models\Checkup;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExaminationExportController extends Controller
{
    public function export(Checkup $checkup)
    {
        $examinations = $checkup->examinations()->with('member')->get();

        $pdf = Pdf::loadView('pdf.examinations', compact('examinations', 'checkup'));

        return $pdf->download('data-pemeriksaan-' . $checkup->id . '.pdf');
    }
}
