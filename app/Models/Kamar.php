<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * <<entity>> Kamar (CD-09), memakai tabel `kamar` pada database/schema.sql.
 * Di UC-09 dipakai untuk menghitung kamar sebelum kost dipublikasikan.
 */
class Kamar extends Model
{
    protected $table = 'kamar';
    protected $primaryKey = 'idKamar';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'idKamar',
        'idKost',
        'nomorKamar',
        'tipeKamar',
        'ukuranKamar',
        'hargaSewa',
        'fasilitasKamar',
        'fotoKamar',
        'statusKamar',
    ];

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class, 'idKost', 'idKost');
    }

    /** cekKamar(idKost): jumlah kamar milik kost (jumlahKamar). */
    public static function cekKamar(int $idKost): int
    {
        return DB::table('kamar')->where('idKost', $idKost)->count();
    }

    /** cekKamarTerisi(idKamar): status kamar (statusKamar), null jika kamar tidak ditemukan. */
    public static function cekKamarTerisi(int $idKamar): ?string
    {
        $status = DB::table('kamar')->where('idKamar', $idKamar)->value('statusKamar');

        return $status === null ? null : (string) $status;
    }
}