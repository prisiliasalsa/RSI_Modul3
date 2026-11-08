<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kost extends Model
{
    protected $table = 'kost';
    protected $primaryKey = 'id_kost';

    protected $fillable = [
        'id_pemilik',
        'nama_kost',
        'alamat',
        'lokasi',
        'tipe_kost',
        'fasilitas_umum',
        'deskripsi',
        'peraturan',
        'kontak_pemilik',
        'foto_utama',
        'status',
        'harga_mulai',
    ];

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pemilik');
    }

    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class, 'id_kost');
    }
}
