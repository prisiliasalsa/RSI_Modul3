<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/keluhan.php';

// UC-15: Laporan keuangan pemilik kost
require __DIR__ . '/keuangan.php';