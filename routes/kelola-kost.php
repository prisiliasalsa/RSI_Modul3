<?php

use App\Http\Controllers\KontrolKelolaKost;
use Illuminate\Support\Facades\Route;

// UC-09: Mengelola dan Publikasi Data Kost (pemilik)
Route::get('/kelola-kost', [KontrolKelolaKost::class, 'pilihKelolaKost'])->name('kost.daftar');
Route::get('/kelola-kost/tambah', [KontrolKelolaKost::class, 'tekanTambahKost'])->name('kost.tambah');
Route::post('/kelola-kost', [KontrolKelolaKost::class, 'tekanSimpan'])->name('kost.simpan');

Route::get('/kelola-kost/{idKost}/ubah', [KontrolKelolaKost::class, 'tekanUbahKost'])->whereNumber('idKost')->name('kost.ubah');
Route::post('/kelola-kost/{idKost}/ubah', [KontrolKelolaKost::class, 'tekanSimpan'])->whereNumber('idKost')->name('kost.perbarui');

Route::get('/kelola-kost/{idKost}/publikasi', [KontrolKelolaKost::class, 'pilihPublikasi'])->whereNumber('idKost')->name('kost.publikasi');
Route::post('/kelola-kost/{idKost}/publikasi', [KontrolKelolaKost::class, 'publikasiKost'])->whereNumber('idKost')->name('kost.publikasikan');

Route::get('/kelola-kost/{idKost}/kamar', [KontrolKelolaKost::class, 'bukaKamar'])->whereNumber('idKost')->name('kost.kamar');