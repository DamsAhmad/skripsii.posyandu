<?php

use App\Http\Controllers\BbuChartController;
use App\Http\Controllers\ExaminationExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export-examinations/{checkup}', [ExaminationExportController::class, 'export'])
    ->name('export.examinations');

Route::get('/members/{id}/bbu-chart', [BbuChartController::class, 'show'])->name('bbu-chart.show');
