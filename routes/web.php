<?php

use App\Http\Controllers\KamarController;
use App\Http\Controllers\KontrolLihatStatusPengajuan;
use App\Http\Controllers\LoginController;
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

// UC-02 login
Route::get('/login', [LoginController::class, 'pilihMenuLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.proses');

// UC-10 kelola kamar
Route::get('/kost/{idKost}/kamar', [KamarController::class, 'bukaDaftarKamar'])->name('kamar.index');
Route::get('/kost/{idKost}/kamar/tambah', [KamarController::class, 'bukaFormKamar'])->name('kamar.create');
Route::post('/kost/{idKost}/kamar', [KamarController::class, 'simpanKamar'])->name('kamar.store');
Route::get('/kost/{idKost}/kamar/{idKamar}/ubah', [KamarController::class, 'bukaFormUbah'])->name('kamar.edit');
Route::put('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'simpanKamar'])->name('kamar.update');
Route::get('/kost/{idKost}/kamar/{idKamar}/hapus', [KamarController::class, 'hapusKamar'])->name('kamar.hapus');
Route::delete('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'konfirmasiHapus'])->name('kamar.destroy');

// Fitur teman (keluhan, keuangan, dll.)
require __DIR__ . '/keluhan.php';
require __DIR__ . '/keluhan-pemilik.php';
require __DIR__ . '/keuangan.php';

// UC-09: Mengelola dan Publikasi Data Kost
require __DIR__ . '/kelola-kost.php';