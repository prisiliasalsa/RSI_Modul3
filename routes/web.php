<?php

use App\Http\Controllers\RegistrasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/registrasi', [RegistrasiController::class, 'pilihMenuRegistrasi'])->name('registrasi.form');
Route::post('/registrasi', [RegistrasiController::class, 'klikDaftar'])->name('registrasi.simpan');
Route::get('/registrasi/berhasil', [RegistrasiController::class, 'halamanBerhasil'])->name('registrasi.berhasil');
Route::get('/registrasi/masuk', [RegistrasiController::class, 'arahkanKeHalamanLogin'])->name('registrasi.login');

require __DIR__ . '/keluhan.php';