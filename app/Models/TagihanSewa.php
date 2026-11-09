<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * <<entity>> TagihanSewa (CD-13), memakai tabel `tagihansewa`
 * pada database/schema.sql.
 */
class TagihanSewa extends Model
{
    public const STATUS_BELUM_LUNAS = 'Belum Lunas';
    public const STATUS_LUNAS = 'Lunas';

    protected $table = 'tagihansewa';
    protected $primaryKey = 'idTagihan';
    public $incrementing = false;
    public $timestamps = false;

    /** getTagihan(idTagihan): data tagihan atau null. */
    public static function getTagihan(int $idTagihan): ?object
    {
        return DB::table('tagihansewa')
            ->where('idTagihan', $idTagihan)
            ->first();
    }

    /** ubahStatus(idTagihan, status): true jika tagihan ditemukan. */
    public static function ubahStatus(int $idTagihan, string $status): bool
    {
        if (!DB::table('tagihansewa')->where('idTagihan', $idTagihan)->exists()) {
            return false;
        }

        DB::table('tagihansewa')
            ->where('idTagihan', $idTagihan)
            ->update(['status' => $status]);

        return true;
    }
}
