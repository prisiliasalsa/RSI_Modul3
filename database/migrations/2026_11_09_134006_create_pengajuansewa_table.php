<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuansewa', function (Blueprint $table) {
            $table->unsignedInteger('idPengajuan')->primary();
            $table->unsignedInteger('idCalonPenghuni');
            $table->unsignedInteger('idKamar');
            $table->unsignedInteger('idKost');
            $table->date('tanggalMulaiSewa')->nullable();
            $table->integer('durasiSewa')->nullable();
            $table->integer('jumlahPenghuni')->nullable();
            $table->string('catatan', 1000)->nullable();
            $table->string('berkasKTP', 255)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('alasanPenolakan', 1000)->nullable();
            $table->date('tanggalPengajuan')->nullable();

            $table->foreign('idKamar')->references('idKamar')->on('kamar');
            $table->foreign('idKost')->references('idKost')->on('kost');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuansewa');
    }
};