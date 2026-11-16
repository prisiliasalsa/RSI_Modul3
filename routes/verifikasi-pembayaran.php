<?php

use App\Http\Controllers\KontrolVerifikasiPembayaran;
use Illuminate\Support\Facades\Route;

// UC-13: Memverifikasi Pembayaran (Pemilik Kost)

// UI-13 daftar pembayaran "Menunggu Verifikasi"
Route::get(
    '/pemilik/verifikasi-pembayaran',
    [KontrolVerifikasiPembayaran::class, 'lihatDaftarPembayaran']
)->name('pemilik.verifikasi.daftar');

// UI-13 detail, konfirmasi terima (?aksi=terima), alasan tolak (?aksi=tolak)
Route::get(
    '/pemilik/verifikasi-pembayaran/{idPembayaran}',
    [KontrolVerifikasiPembayaran::class, 'lihatDetailPembayaran']
)->whereNumber('idPembayaran')
 ->name('pemilik.verifikasi.detail');

// Berkas bukti pembayaran yang diunggah penghuni (UC-07)
Route::get(
    '/pemilik/verifikasi-pembayaran/{idPembayaran}/bukti',
    [KontrolVerifikasiPembayaran::class, 'lihatBukti']
)->whereNumber('idPembayaran')
 ->name('pemilik.verifikasi.bukti');

// "Ya, Terima"
Route::post(
    '/pemilik/verifikasi-pembayaran/{idPembayaran}/terima',
    [KontrolVerifikasiPembayaran::class, 'terimaPembayaran']
)->whereNumber('idPembayaran')
 ->name('pemilik.verifikasi.terima');

// "Kirim Penolakan"
Route::post(
    '/pemilik/verifikasi-pembayaran/{idPembayaran}/tolak',
    [KontrolVerifikasiPembayaran::class, 'tolakPembayaran']
)->whereNumber('idPembayaran')
 ->name('pemilik.verifikasi.tolak');
