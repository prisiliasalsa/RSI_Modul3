<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagihanController;

Route::get('/', function () {
    return view('welcome');
});

// Route Keluhan
require __DIR__ . '/keluhan.php';

// Route Tagihan (UC-07)
Route::get('/tagihan', [TagihanController::class, 'lihatTagihan'])
    ->name('tagihan.daftar');

Route::get('/tagihan/{idTagihan}/bayar', [TagihanController::class, 'ambilDetailTagihan'])
    ->name('tagihan.bayar');

Route::post('/tagihan/{idTagihan}/bukti', [TagihanController::class, 'submitBukti'])
    ->name('tagihan.submitBukti');

Route::get('/tagihan/berhasil/{idPembayaran}', [TagihanController::class, 'buktiTerkirim'])
    ->name('tagihan.berhasil');
