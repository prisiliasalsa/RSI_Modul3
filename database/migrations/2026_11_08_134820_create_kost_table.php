<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kost', function (Blueprint $table) {
            $table->increments('idKost');
            $table->foreignId('idPemilik')->constrained('users');
            $table->string('namaKost', 100);
            $table->string('alamat', 255);
            $table->string('lokasi', 100);
            $table->string('tipeKost', 30);
            $table->string('fasilitasUmum', 1000)->nullable();
            $table->string('deskripsi', 2000)->nullable();
            $table->string('peraturan', 2000)->nullable();
            $table->string('kontakPemilik', 100)->nullable();
            $table->string('fotoUtama', 255)->nullable();
            $table->string('fotoKost', 255)->nullable();
            $table->string('status', 30)->default('Draft');
            $table->decimal('hargaMulai', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kost');
    }
};