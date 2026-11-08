
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// UC-08: Pengajuan Keluhan Penghuni
require __DIR__ . '/keluhan.php';

// UC-14: Melihat dan Menangani Keluhan Pemilik
require __DIR__ . '/keluhan-pemilik.php';

// UC-15: Laporan Keuangan Pemilik
require __DIR__ . '/keuangan.php';
