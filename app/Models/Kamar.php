<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kamar extends Model
{
    protected $table = 'kamar';
    protected $primaryKey = 'id_kamar';

    protected $fillable = [
        'id_kost',
        'nomor_kamar',
        'tipe_kamar',
        'ukuran_kamar',
        'harga_sewa',
        'fasilitas_kamar',
        'foto_kamar',
        'status_kamar',
    ];

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class, 'id_kost');
    }
}