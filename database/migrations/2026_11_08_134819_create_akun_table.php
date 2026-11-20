<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->increments('idAkun');
            $table->string('namaLengkap', 100)->nullable();
            $table->string('email', 254)->unique();
            $table->string('noTelepon', 20)->nullable();
            $table->string('kataSandi', 255);
            $table->string('jenisAkun', 30)->default('Calon Penghuni');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};