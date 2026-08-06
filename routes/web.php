<?php

use App\Http\Controllers\ReportPdfDataController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::get('storage/{path}', function ($path) {
    return redirect(Illuminate\Support\Facades\Storage::url($path));
})->where('path', '.*');

// Report PDF data endpoint (for client-side PDF generation in Filament)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/reports/{report}/pdf-data', ReportPdfDataController::class)
        ->name('admin.reports.pdf-data');
});
