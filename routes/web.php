<?php

use App\Http\Controllers\KontrolLihatStatusPengajuan;
use App\Http\Controllers\RegistrasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// UC-01: Registrasi Pengguna
Route::get('/registrasi', [RegistrasiController::class, 'pilihMenuRegistrasi'])->name('registrasi.form');
Route::post('/registrasi', [RegistrasiController::class, 'klikDaftar'])->name('registrasi.simpan');
Route::get('/registrasi/berhasil', [RegistrasiController::class, 'halamanBerhasil'])->name('registrasi.berhasil');
Route::get('/registrasi/masuk', [RegistrasiController::class, 'arahkanKeHalamanLogin'])->name('registrasi.login');

// UC-06: Melihat Status Pengajuan Sewa
Route::get('/status-pengajuan', [KontrolLihatStatusPengajuan::class, 'pilihMenuStatus'])->name('pengajuan.status');
Route::get('/status-pengajuan/muat-ulang', [KontrolLihatStatusPengajuan::class, 'muatUlang'])->name('pengajuan.muatUlang');
Route::get('/status-pengajuan/{idPengajuan}', [KontrolLihatStatusPengajuan::class, 'lihatDetailStatus'])->whereNumber('idPengajuan')->name('pengajuan.detail');

// UC-08: Pengajuan Keluhan Penghuni
require __DIR__ . '/keluhan.php';

// UC-14: Melihat dan Menangani Keluhan Pemilik
require __DIR__ . '/keluhan-pemilik.php';

// UC-15: Laporan Keuangan Pemilik
require __DIR__ . '/keuangan.php';