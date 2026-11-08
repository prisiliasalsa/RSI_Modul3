<?php

use App\Http\Controllers\LaporanKeuanganController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/pemilik/keuangan',
    [LaporanKeuanganController::class, 'index']
)->name('pemilik.keuangan.index');
