<?php

use App\Http\Controllers\KeluhanPemilikController;
use App\Http\Controllers\ResponKeluhanController;
use Illuminate\Support\Facades\Route;

// UI-14.01: Daftar keluhan pemilik
Route::get(
    '/pemilik/keluhan',
    [KeluhanPemilikController::class, 'mintaDaftarKeluhan']
)->name('pemilik.keluhan.daftar');

// UI-14.02: Detail keluhan pemilik
Route::get(
    '/pemilik/keluhan/{idKeluhan}',
    [KeluhanPemilikController::class, 'mintaDetailKeluhan']
)->whereNumber('idKeluhan')
 ->name('pemilik.keluhan.detail');

// UI-14.04: Mengubah status penanganan
Route::patch(
    '/pemilik/keluhan/{idKeluhan}/status',
    [KeluhanPemilikController::class, 'perbaruiStatusKeluhan']
)->whereNumber('idKeluhan')
 ->name('pemilik.keluhan.status');

// Meminta informasi tambahan
Route::post(
    '/pemilik/keluhan/{idKeluhan}/informasi',
    [KeluhanPemilikController::class, 'mintaInformasiTambahan']
)->whereNumber('idKeluhan')
 ->name('pemilik.keluhan.minta-info');

// Melihat foto bukti
Route::get(
    '/pemilik/keluhan/{idKeluhan}/foto',
    [KeluhanPemilikController::class, 'lihatFoto']
)->whereNumber('idKeluhan')
 ->name('pemilik.keluhan.foto');

// Pratinjau halaman penghuni, khusus pemilik terkait
Route::get(
    '/pemilik/keluhan/{idKeluhan}/pratinjau-penghuni',
    [KeluhanPemilikController::class, 'pratinjauPenghuni']
)->whereNumber('idKeluhan')
 ->name('pemilik.keluhan.pratinjau');

// UI-14.03: Jawaban penghuni
Route::get(
    '/keluhan/{idKeluhan}/tanggapan',
    [ResponKeluhanController::class, 'tampil']
)->whereNumber('idKeluhan')
 ->name('keluhan.tanggapan');

Route::post(
    '/keluhan/{idKeluhan}/tanggapan',
    [ResponKeluhanController::class, 'kirim']
)->whereNumber('idKeluhan')
 ->name('keluhan.tanggapan-kirim');
