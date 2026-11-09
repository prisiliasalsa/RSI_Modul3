<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Model Kamar untuk integrasi UC-09 dan UC-10.
 * Menggunakan struktur database/schema.sql.
 */
class Kamar extends Model
{
    protected $table = 'kamar';
    protected $primaryKey = 'idKamar';

    // idKamar sudah AUTO_INCREMENT di MySQL
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'idKost',
        'nomorKamar',
        'tipeKamar',
        'ukuranKamar',
        'hargaSewa',
        'fasilitasKamar',
        'fotoKamar',
        'statusKamar',
    ];

    /**
     * UC-10: Relasi kamar dengan kost.
     */
    public function kost(): BelongsTo
    {
        return $this->belongsTo(
            Kost::class,
            'idKost',
            'idKost'
        );
    }

    /**
     * UC-09: Menghitung jumlah kamar dalam kost.
     * Digunakan sebelum mempublikasikan kost.
     */
    public static function cekKamar(int $idKost): int
    {
        return DB::table('kamar')
            ->where('idKost', $idKost)
            ->count();
    }

    /**
     * UC-09: Mengambil status kamar berdasarkan ID.
     * Mengembalikan null jika kamar tidak ditemukan.
     */
    public static function cekKamarTerisi(int $idKamar): ?string
    {
        $status = DB::table('kamar')
            ->where('idKamar', $idKamar)
            ->value('statusKamar');

        return $status === null
            ? null
            : (string) $status;
    }
}
