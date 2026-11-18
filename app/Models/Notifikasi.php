<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * <<entity>> Notifikasi (CD-13), memakai tabel `notifikasi`.
 * idNotifikasi pada schema bukan AUTO_INCREMENT.
 */
class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'idNotifikasi';
    public $incrementing = false;
    public $timestamps = false;

    public static function catatPengajuan(
        int $idPengajuan,
        string $pesan,
        ?int $idPemilikKost = null,
        ?int $idPenghuni = null
    ): void {
        $idNotifikasi = (int) DB::table('notifikasi')
            ->lockForUpdate()
            ->max('idNotifikasi') + 1;

        DB::table('notifikasi')->insert([
            'idNotifikasi' => $idNotifikasi,
            'idPemilikKost' => $idPemilikKost,
            'idPenghuni' => $idPenghuni,
            'idPengajuan' => $idPengajuan,
            'pesan' => $pesan,
            'waktuKirim' => now(),
            'status' => 'Belum Dibaca',
        ]);
    }

    /** kirimNotif(idPembayaran, pesan): notifikasi ke penghuni pembayar. */
    public static function kirimNotif(int $idPembayaran, string $pesan): bool
    {
        $idPenghuni = DB::table('pembayaran')
            ->where('idPembayaran', $idPembayaran)
            ->value('idPenghuni');

        if (!$idPenghuni) {
            return false;
        }

        $idNotifikasi = (int) DB::table('notifikasi')
            ->lockForUpdate()
            ->max('idNotifikasi') + 1;

        return DB::table('notifikasi')->insert([
            'idNotifikasi' => $idNotifikasi,
            'idPemilikKost' => null,
            'idPenghuni' => $idPenghuni,
            'idPengajuan' => null,
            'idPembayaran' => $idPembayaran,
            'pesan' => $pesan,
            'waktuKirim' => now(),
            'status' => 'Belum Dibaca',
        ]);
    }
}
