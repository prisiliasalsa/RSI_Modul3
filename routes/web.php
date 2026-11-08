<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// UC-08: Pengajuan dan melihat keluhan penghuni
require __DIR__ . '/keluhan.php';

// UC-14: Melihat dan menangani keluhan pemilik
require __DIR__ . '/keluhan-pemilik.php';
