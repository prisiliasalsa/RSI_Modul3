<?php

use App\Http\Controllers\KeluhanController;
use Illuminate\Support\Facades\Route;

// Halaman pengajuan keluhan
Route::get(
    '/keluhan/ajukan',
    [KeluhanController::class, 'mintaFormPengajuan']
)->name('keluhan.ajukan');

// Simpan keluhan
Route::post(
    '/keluhan',
    [KeluhanController::class, 'ajukanKeluhan']
)->name('keluhan.simpan');

// Daftar status keluhan
Route::get(
    '/keluhan/status',
    [KeluhanController::class, 'statusKeluhan']
)->name('keluhan.status');

// Melihat foto keluhan
Route::get(
    '/keluhan/{idKeluhan}/foto',
    [KeluhanController::class, 'lihatFoto']
)->whereNumber('idKeluhan')
 ->name('keluhan.foto');

// Detail keluhan
Route::get(
    '/keluhan/{idKeluhan}',
    [KeluhanController::class, 'detailKeluhan']
)->whereNumber('idKeluhan')
 ->name('keluhan.detail');
 