<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * <<entity>> Pembayaran (CD-13), memakai tabel `pembayaran`
 * pada database/schema.sql.
 */
class Pembayaran extends Model
{
    public const STATUS_MENUNGGU = 'Menunggu Verifikasi';
    public const STATUS_LUNAS = 'Lunas';
    public const STATUS_DITOLAK = 'Ditolak';

    protected $table = 'pembayaran';
    protected $primaryKey = 'idPembayaran';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Query dasar pembayaran beserta tagihan, penghuni, kamar,
     * kost, dan metode. Hanya kost milik pemilik yang login.
     */
    private static function queryMilik(int $idPemilikKost): Builder
    {
        return DB::table('pembayaran as pb')
            ->join('tagihansewa as t', 't.idTagihan', '=', 'pb.idTagihan')
            ->join('penghuni as p', 'p.idPenghuni', '=', 'pb.idPenghuni')
            ->join('kamar as km', 'km.idKamar', '=', 't.idKamar')
            ->join('kost as k', 'k.idKost', '=', 'km.idKost')
            ->leftJoin(
                'metodepembayaran as mp',
                'mp.idMetode',
                '=',
                'pb.idMetode'
            )
            ->where('k.idPemilik', $idPemilikKost)
            ->select(
                'pb.*',
                't.periode',
                't.jumlah as jumlahTagihan',
                't.status as statusTagihan',
                'p.namaLengkap',
                'km.nomorKamar',
                'km.hargaSewa',
                'km.fotoKamar',
                'k.idKost',
                'k.namaKost',
                'k.lokasi',
                'k.fotoUtama',
                'mp.namaBank',
                'mp.atasNama'
            );
    }

    /**
     * getMenungguVerifikasi(idPemilikKost): daftar pembayaran
     * berstatus "Menunggu Verifikasi" (dapat difilter kata kunci).
     */
    public static function getMenungguVerifikasi(
        int $idPemilikKost,
        string $cari = ''
    ): Builder {
        $query = self::queryMilik($idPemilikKost)
            ->where('pb.statusPembayaran', self::STATUS_MENUNGGU);

        if ($cari !== '') {
            $query->where(function (Builder $q) use ($cari) {
                $q->where('p.namaLengkap', 'like', "%{$cari}%")
                    ->orWhere('km.nomorKamar', 'like', "%{$cari}%")
                    ->orWhere('t.periode', 'like', "%{$cari}%");
            });
        }

        return $query
            ->orderBy('pb.tanggalPembayaran')
            ->orderBy('pb.idPembayaran');
    }

    /** getDetail(idPembayaran): satu pembayaran milik kost pemilik. */
    public static function getDetail(
        int $idPembayaran,
        int $idPemilikKost
    ): ?object {
        return self::queryMilik($idPemilikKost)
            ->where('pb.idPembayaran', $idPembayaran)
            ->first();
    }

    /**
     * ubahStatus(idPembayaran, status, alasan): hanya mengubah
     * pembayaran yang masih menunggu, supaya tidak diverifikasi dua kali.
     */
    public static function ubahStatus(
        int $idPembayaran,
        string $status,
        ?string $alasan
    ): bool {
        $jumlahBaris = DB::table('pembayaran')
            ->where('idPembayaran', $idPembayaran)
            ->where('statusPembayaran', self::STATUS_MENUNGGU)
            ->update([
                'statusPembayaran' => $status,
                'alasanPenolakan' => $alasan,
            ]);

        return $jumlahBaris === 1;
    }
}
