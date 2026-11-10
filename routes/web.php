<?php

use App\Http\Controllers\CariKostController;
use App\Http\Controllers\DetailKostController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KontrolLihatStatusPengajuan;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegistrasiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KontrolPengajuanSewa;

Route::get('/', fn () => view('welcome'));

// UC-01: Registrasi
Route::get('/registrasi', [RegistrasiController::class, 'pilihMenuRegistrasi'])->name('registrasi.form');
Route::post('/registrasi', [RegistrasiController::class, 'klikDaftar'])->name('registrasi.simpan');
Route::get('/registrasi/berhasil', [RegistrasiController::class, 'halamanBerhasil'])->name('registrasi.berhasil');
Route::get('/registrasi/masuk', [RegistrasiController::class, 'arahkanKeHalamanLogin'])->name('registrasi.login');

// UC-02: Login
Route::get('/login', [LoginController::class, 'pilihMenuLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.proses');

// UC-03: Cari kost
Route::get('/cari-kost', [CariKostController::class, 'index'])->name('kost.cari');

// UC-04: Detail kost dan kamar
Route::get('/cari-kost/{idKost}', [DetailKostController::class, 'detailKost'])
    ->whereNumber('idKost')->name('kost.detail');
Route::get('/cari-kost/kamar/{idKamar}', [DetailKostController::class, 'detailKamar'])
    ->whereNumber('idKamar')->name('kamar.detail');

// UC-06: Status pengajuan sewa
Route::get('/status-pengajuan', [KontrolLihatStatusPengajuan::class, 'pilihMenuStatus'])
    ->name('pengajuan.status');
Route::get('/status-pengajuan/muat-ulang', [KontrolLihatStatusPengajuan::class, 'muatUlang'])
    ->name('pengajuan.muatUlang');
Route::get('/status-pengajuan/{idPengajuan}', [KontrolLihatStatusPengajuan::class, 'lihatDetailStatus'])
    ->whereNumber('idPengajuan')->name('pengajuan.detail');

// UC-10: Kelola kamar
Route::get('/kost/{idKost}/kamar', [KamarController::class, 'bukaDaftarKamar'])->name('kamar.index');
Route::get('/kost/{idKost}/kamar/tambah', [KamarController::class, 'bukaFormKamar'])->name('kamar.create');
Route::post('/kost/{idKost}/kamar', [KamarController::class, 'simpanKamar'])->name('kamar.store');
Route::get('/kost/{idKost}/kamar/{idKamar}/ubah', [KamarController::class, 'bukaFormUbah'])->name('kamar.edit');
Route::put('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'simpanKamar'])->name('kamar.update');
Route::get('/kost/{idKost}/kamar/{idKamar}/hapus', [KamarController::class, 'hapusKamar'])->name('kamar.hapus');
Route::delete('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'konfirmasiHapus'])->name('kamar.destroy');

// Rute modul lain
require __DIR__ . '/keluhan.php';
require __DIR__ . '/keluhan-pemilik.php';
require __DIR__ . '/kelola-kost.php';
require __DIR__ . '/keuangan.php';
//require __DIR__ . '/data-penghuni.php';
//require __DIR__ . '/verifikasi-pembayaran.php';

Route::get('/cari-kost/kamar/{idKamar}/ajukan-sewa', [KontrolPengajuanSewa::class, 'tampilFormAjukanSewa'])
    ->whereNumber('idKamar')
    ->name('pengajuan.ajukan');

Route::post('/cari-kost/kamar/{idKamar}/ajukan-sewa', [KontrolPengajuanSewa::class, 'simpanPengajuan'])
    ->whereNumber('idKamar')
    ->name('pengajuan.simpan');