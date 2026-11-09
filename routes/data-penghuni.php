<?php

use App\Http\Controllers\KontrolPenghuni;
use Illuminate\Support\Facades\Route;

// UC-12: Mengelola Data Penghuni (Pemilik Kost)

// UI-12 daftar penghuni
Route::get(
    '/pemilik/penghuni',
    [KontrolPenghuni::class, 'lihatDaftarPenghuni']
)->name('pemilik.penghuni.daftar');

// UI-12 detail / ubah penghuni, konfirmasi akhiri sewa (?aksi=akhiri)
Route::get(
    '/pemilik/penghuni/{idPenghuni}',
    [KontrolPenghuni::class, 'lihatDetailPenghuni']
)->whereNumber('idPenghuni')
 ->name('pemilik.penghuni.detail');

// "Simpan perubahan"
Route::put(
    '/pemilik/penghuni/{idPenghuni}',
    [KontrolPenghuni::class, 'ubahDataPenghuni']
)->whereNumber('idPenghuni')
 ->name('pemilik.penghuni.ubah');

// "Ya, Akhiri Sewa"
Route::post(
    '/pemilik/penghuni/{idPenghuni}/akhiri',
    [KontrolPenghuni::class, 'akhiriSewa']
)->whereNumber('idPenghuni')
 ->name('pemilik.penghuni.akhiri');
