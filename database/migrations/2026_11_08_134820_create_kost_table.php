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
        Schema::create('kost', function (Blueprint $table) {
            $table->id('id_kost');
            $table->foreignId('id_pemilik')->constrained('users');
            $table->string('nama_kost', 100);
            $table->string('alamat', 255);
            $table->string('lokasi', 100);
            $table->string('tipe_kost', 30);
            $table->string('fasilitas_umum', 1000)->nullable();
            $table->string('deskripsi', 2000)->nullable();
            $table->string('peraturan', 2000)->nullable();
            $table->string('kontak_pemilik', 100)->nullable();
            $table->string('foto_utama', 255)->nullable();
            $table->string('status', 30)->default('Draft');
            $table->decimal('harga_mulai', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kost');
    }
};
