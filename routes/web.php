<?php

use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// UC-01: Registrasi
Route::get('/registrasi',
    [RegistrasiController::class, 'pilihMenuRegistrasi']
)->name('registrasi.form');

Route::post('/registrasi',
    [RegistrasiController::class, 'klikDaftar']
)->name('registrasi.simpan');

Route::get('/registrasi/berhasil',
    [RegistrasiController::class, 'halamanBerhasil']
)->name('registrasi.berhasil');

Route::get('/registrasi/masuk',
    [RegistrasiController::class, 'arahkanKeHalamanLogin']
)->name('registrasi.login');

// UC-02: Login
Route::get('/login',
    [LoginController::class, 'pilihMenuLogin']
)->name('login');

Route::post('/login',
    [LoginController::class, 'login']
)->name('login.proses');

// UC-10: Kelola Kamar
Route::get('/kost/{idKost}/kamar',
    [KamarController::class, 'bukaDaftarKamar']
)->name('kamar.index');

Route::get('/kost/{idKost}/kamar/tambah',
    [KamarController::class, 'bukaFormKamar']
)->name('kamar.create');

Route::post('/kost/{idKost}/kamar',
    [KamarController::class, 'simpanKamar']
)->name('kamar.store');

Route::get('/kost/{idKost}/kamar/{idKamar}/ubah',
    [KamarController::class, 'bukaFormUbah']
)->name('kamar.edit');

Route::put('/kost/{idKost}/kamar/{idKamar}',
    [KamarController::class, 'simpanKamar']
)->name('kamar.update');

Route::get('/kost/{idKost}/kamar/{idKamar}/hapus',
    [KamarController::class, 'hapusKamar']
)->name('kamar.hapus');

Route::delete('/kost/{idKost}/kamar/{idKamar}',
    [KamarController::class, 'konfirmasiHapus']
)->name('kamar.destroy');

// UC-08: Pengajuan Keluhan
require __DIR__ . '/keluhan.php';


/*
|--------------------------------------------------------------------------
| UC-09: Mengelola dan Publikasi Kost
|--------------------------------------------------------------------------
*/
require __DIR__ . '/kelola-kost.php';


// UC-14: Keluhan Pemilik
require __DIR__ . '/keluhan-pemilik.php';

// UC-15: Laporan Keuangan
require __DIR__ . '/keuangan.php';


/*
|--------------------------------------------------------------------------
| Pengujian sementara sesi login UC-02
|--------------------------------------------------------------------------
*/
Route::get('/cek-sesi-login', function () {
    abort_unless(app()->environment('local'), 404);

    return response()->json([
        'loginBerhasil' => session()->has('idAkun'),
        'jenisAkun' => session('jenisAkun'),
    ]);
});
