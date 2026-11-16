<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihansewa', function (Blueprint $table) {
            $table->unsignedInteger('idTagihan')->primary();
            $table->unsignedInteger('idPengajuan');
            $table->unsignedInteger('idPenghuni')->nullable();
            $table->unsignedInteger('idKamar');
            $table->string('periode', 7)->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->date('jatuhTempo')->nullable();
            $table->string('status', 30)->nullable();

            $table->foreign('idPengajuan')->references('idPengajuan')->on('pengajuansewa');
            $table->foreign('idKamar')->references('idKamar')->on('kamar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihansewa');
    }
};