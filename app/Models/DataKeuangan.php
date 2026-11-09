<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * <<entity>> DataKeuangan (CD-13), memakai tabel `datakeuangan`.
 * Data ini dibaca oleh Laporan Keuangan (UC-15).
 * idTransaksi pada schema bukan AUTO_INCREMENT, jadi id
 * berikutnya dihitung dari MAX(idTransaksi) + 1.
 */
class DataKeuangan extends Model
{
    protected $table = 'datakeuangan';
    protected $primaryKey = 'idTransaksi';
    public $incrementing = false;
    public $timestamps = false;

    /** catatTransaksi(idPembayaran): mencatat pembayaran yang diterima. */
    public static function catatTransaksi(int $idPembayaran): bool
    {
        // Nominal diambil dari pembayaran, kost dari kamar tagihan
        $sumber = DB::table('pembayaran as pb')
            ->join('tagihansewa as t', 't.idTagihan', '=', 'pb.idTagihan')
            ->join('kamar as km', 'km.idKamar', '=', 't.idKamar')
            ->where('pb.idPembayaran', $idPembayaran)
            ->select('km.idKost', 'pb.jumlahPembayaran')
            ->first();

        if (!$sumber) {
            return false;
        }

        $idTransaksi = (int) DB::table('datakeuangan')
            ->lockForUpdate()
            ->max('idTransaksi') + 1;

        return DB::table('datakeuangan')->insert([
            'idTransaksi' => $idTransaksi,
            'idKost' => $sumber->idKost,
            'idPembayaran' => $idPembayaran,
            'nominal' => $sumber->jumlahPembayaran,
            'tanggalTransaksi' => now()->toDateString(),
        ]);
    }
}
