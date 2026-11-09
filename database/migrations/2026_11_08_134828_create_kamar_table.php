<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id('id_kamar');
            $table->foreignId('id_kost')->constrained('kost', 'id_kost')->cascadeOnDelete();
            $table->string('nomor_kamar', 20);
            $table->string('tipe_kamar', 50);
            $table->string('ukuran_kamar', 50);
            $table->decimal('harga_sewa', 15, 2);
            $table->string('fasilitas_kamar', 1000)->nullable();
            $table->string('foto_kamar', 255)->nullable();
            $table->string('status_kamar', 30)->default('Tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
