<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CariKostController;
use App\Http\Controllers\DetailKostController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KontrolLihatStatusPengajuan;
use App\Http\Controllers\KontrolPengajuanSewa;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\TagihanSewaController;
use App\Http\Controllers\VerifikasiPengajuanSewaController;

/*
|--------------------------------------------------------------------------
| Halaman Utama
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| UC-01: Registrasi
|--------------------------------------------------------------------------
*/

Route::get('/registrasi', [RegistrasiController::class, 'pilihMenuRegistrasi'])
    ->name('registrasi.form');

Route::post('/registrasi', [RegistrasiController::class, 'klikDaftar'])
    ->name('registrasi.simpan');

Route::get('/registrasi/berhasil', [RegistrasiController::class, 'halamanBerhasil'])
    ->name('registrasi.berhasil');

Route::get('/registrasi/masuk', [RegistrasiController::class, 'arahkanKeHalamanLogin'])
    ->name('registrasi.login');

/*
|--------------------------------------------------------------------------
| UC-02: Login
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'pilihMenuLogin'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.proses');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    if (!$request->session()->has('idAkun')) {
        return redirect()->route('login');
    }

    $peran = $request->session()->get('jenisAkun');

    if ($peran === 'Pemilik Kost') {
        return redirect()->route('kost.daftar');
    }

    abort_unless(in_array($peran, ['Calon Penghuni', 'Penghuni'], true), 403);

    return redirect()->route('kost.cari');
})->name('penghuni.dashboard');

/*
|--------------------------------------------------------------------------
| UC-03: Cari Kost
|--------------------------------------------------------------------------
*/

Route::get('/cari-kost', [CariKostController::class, 'index'])
    ->name('kost.cari');

/*
|--------------------------------------------------------------------------
| UC-04: Detail Kost dan Kamar
|--------------------------------------------------------------------------
*/

Route::get('/cari-kost/{idKost}', [DetailKostController::class, 'detailKost'])
    ->whereNumber('idKost')
    ->name('kost.detail');

Route::get('/cari-kost/kamar/{idKamar}', [DetailKostController::class, 'detailKamar'])
    ->whereNumber('idKamar')
    ->name('kamar.detail');

/*
|--------------------------------------------------------------------------
| UC-05: Pengajuan Sewa
|--------------------------------------------------------------------------
*/

Route::get('/cari-kost/kamar/{idKamar}/ajukan-sewa', [KontrolPengajuanSewa::class, 'tampilFormAjukanSewa'])
    ->whereNumber('idKamar')
    ->name('pengajuan.ajukan');

Route::post('/cari-kost/kamar/{idKamar}/ajukan-sewa', [KontrolPengajuanSewa::class, 'simpanPengajuan'])
    ->whereNumber('idKamar')
    ->name('pengajuan.simpan');

/*
|--------------------------------------------------------------------------
| UC-06: Status Pengajuan Sewa
|--------------------------------------------------------------------------
*/

Route::get('/status-pengajuan', [KontrolLihatStatusPengajuan::class, 'pilihMenuStatus'])
    ->name('pengajuan.status');

Route::get('/status-pengajuan/muat-ulang', [KontrolLihatStatusPengajuan::class, 'muatUlang'])
    ->name('pengajuan.muatUlang');

Route::get('/status-pengajuan/{idPengajuan}', [KontrolLihatStatusPengajuan::class, 'lihatDetailStatus'])
    ->whereNumber('idPengajuan')
    ->name('pengajuan.detail');

/*
|--------------------------------------------------------------------------
| UC-07: Tagihan dan Pembayaran Sewa
|--------------------------------------------------------------------------
*/

Route::get('/tagihan-sewa', [TagihanSewaController::class, 'index'])
    ->name('tagihan-sewa.index');

Route::get('/tagihan-sewa/{idTagihan}/bayar', [TagihanSewaController::class, 'index'])
    ->whereNumber('idTagihan')
    ->name('tagihan-sewa.form-bayar');

Route::post('/tagihan-sewa/{idTagihan}/bayar', [TagihanSewaController::class, 'bayar'])
    ->whereNumber('idTagihan')
    ->name('tagihan-sewa.bayar');

/*
|--------------------------------------------------------------------------
| UC-10: Kelola Kamar
|--------------------------------------------------------------------------
*/

Route::get('/kost/{idKost}/kamar', [KamarController::class, 'bukaDaftarKamar'])
    ->name('kamar.index');

Route::get('/kost/{idKost}/kamar/tambah', [KamarController::class, 'bukaFormKamar'])
    ->name('kamar.create');

Route::post('/kost/{idKost}/kamar', [KamarController::class, 'simpanKamar'])
    ->name('kamar.store');

Route::get('/kost/{idKost}/kamar/{idKamar}/ubah', [KamarController::class, 'bukaFormUbah'])
    ->name('kamar.edit');

Route::put('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'simpanKamar'])
    ->name('kamar.update');

Route::get('/kost/{idKost}/kamar/{idKamar}/hapus', [KamarController::class, 'hapusKamar'])
    ->name('kamar.hapus');

Route::delete('/kost/{idKost}/kamar/{idKamar}', [KamarController::class, 'konfirmasiHapus'])
    ->name('kamar.destroy');

/*
|--------------------------------------------------------------------------
| UC-11: Verifikasi Pengajuan Sewa
|--------------------------------------------------------------------------
*/

Route::get('/pemilik/pengajuan-sewa', [VerifikasiPengajuanSewaController::class, 'index'])
    ->name('pemilik.pengajuan-sewa.index');

Route::post('/pemilik/pengajuan-sewa/{idPengajuan}/keputusan', [VerifikasiPengajuanSewaController::class, 'putuskan'])
    ->whereNumber('idPengajuan')
    ->name('pemilik.pengajuan-sewa.putuskan');

/*
|--------------------------------------------------------------------------
| Route Fitur Kelompok
|--------------------------------------------------------------------------
*/

// UC-08 dan UC-15: Keluhan Penghuni
require __DIR__ . '/keluhan.php';

// UC-14: Keluhan Pemilik
require __DIR__ . '/keluhan-pemilik.php';

// Fitur Keuangan
require __DIR__ . '/keuangan.php';

// UC-09: Kelola dan Publikasi Kost
require __DIR__ . '/kelola-kost.php';

// UC-12: Mengelola Data Penghuni
require __DIR__ . '/data-penghuni.php';

// UC-13: Memverifikasi Pembayaran
require __DIR__ . '/verifikasi-pembayaran.php';

/* Keluar dari akun Kost.io (session-based login) */
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');
