<?php

use App\Http\Controllers\BbuBoysChartController;
use App\Http\Controllers\BbuGirlsChartController;
use App\Http\Controllers\ExaminationExportController;
use App\Http\Controllers\ImtAdultChartController;
use App\Http\Controllers\ImtuBoyChartController;
use App\Http\Controllers\ImtuGirlChartController;
use App\Http\Controllers\PregnantChartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export-examinations/{checkup}', [ExaminationExportController::class, 'export'])
    ->name('export.examinations');

Route::get('/members/{id}/bbuboy-chart', [BbuBoysChartController::class, 'show'])->name('bbuboy-chart.show');
Route::get('/members/{id}/bbugirl-chart', [BbuGirlsChartController::class, 'show'])->name('bbugirl-chart.show');
Route::get('/members/{id}/imtuboy-chart', [ImtuBoyChartController::class, 'show'])->name('imtuboy-chart.show');
Route::get('/members/{id}/imtugirl-chart', [ImtuGirlChartController::class, 'show'])->name('imtugirl-chart.show');
Route::get('/members/{id}/imtadult-chart', [ImtAdultChartController::class, 'show'])->name('imtadult-chart.show');
