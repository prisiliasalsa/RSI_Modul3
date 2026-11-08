<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\KamarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'pilihMenuLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.proses');

Route::get('/kost/{idKost}/kamar', [KamarController::class, 'bukaDaftarKamar'])->name('kamar.index');
Route::get('/kost/{idKost}/kamar/tambah', [KamarController::class, 'bukaFormKamar'])->name('kamar.create');
Route::post('/kost/{idKost}/kamar', [KamarController::class, 'simpanKamar'])->name('kamar.store');
Route::get('/kost/{idKost}/kamar/{idKamar}/ubah', [KamarController::class, 'bukaFormUbah'])->name('kamar.edit');
Route::put('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'simpanKamar'])->name('kamar.update');
Route::get('/kost/{idKost}/kamar/{idKamar}/hapus', [KamarController::class, 'hapusKamar'])->name('kamar.hapus');
Route::delete('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'konfirmasiHapus'])->name('kamar.destroy');


require __DIR__ . '/keluhan.php';