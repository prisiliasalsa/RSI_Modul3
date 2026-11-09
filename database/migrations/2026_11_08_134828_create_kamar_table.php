<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->increments('idKamar');
            $table->unsignedInteger('idKost');
            $table->string('nomorKamar', 20);
            $table->string('tipeKamar', 50);
            $table->string('ukuranKamar', 50);
            $table->decimal('hargaSewa', 15, 2);
            $table->string('fasilitasKamar', 1000)->nullable();
            $table->string('fotoKamar', 255)->nullable();
            $table->string('statusKamar', 30)->default('Tersedia');
            $table->timestamps();

            $table->foreign('idKost')->references('idKost')->on('kost')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};