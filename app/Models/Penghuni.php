<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * <<entity>> Penghuni (CD-12), memakai tabel `penghuni`
 * pada database/schema.sql.
 */
class Penghuni extends Model
{
    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_TIDAK_AKTIF = 'Tidak Aktif';

    protected $table = 'penghuni';
    protected $primaryKey = 'idPenghuni';
    public $incrementing = false;
    public $timestamps = false;

    /** Query penghuni beserta kamar dan kost milik pemilik. */
    private static function queryMilik(int $idPemilikKost): Builder
    {
        return DB::table('penghuni as p')
            ->join('kost as k', 'k.idKost', '=', 'p.idKost')
            ->join('kamar as km', 'km.idKamar', '=', 'p.idKamar')
            ->where('k.idPemilik', $idPemilikKost)
            ->select(
                'p.*',
                'k.namaKost',
                'k.lokasi',
                'k.fotoUtama',
                'km.nomorKamar',
                'km.hargaSewa',
                'km.fotoKamar'
            );
    }

    /**
     * getPenghuni(): daftar penghuni pada seluruh kost milik
     * pemilik, dapat difilter status dan kata kunci.
     */
    public static function getPenghuni(
        int $idPemilikKost,
        string $status = '',
        string $cari = ''
    ): Builder {
        $query = self::queryMilik($idPemilikKost);

        if ($status !== '') {
            $query->where('p.status', $status);
        }

        if ($cari !== '') {
            $query->where(function (Builder $q) use ($cari) {
                $q->where('p.namaLengkap', 'like', "%{$cari}%")
                    ->orWhere('k.namaKost', 'like', "%{$cari}%")
                    ->orWhere('km.nomorKamar', 'like', "%{$cari}%");
            });
        }

        return $query
            ->orderBy('k.namaKost')
            ->orderBy('km.nomorKamar');
    }

    /** getDetail(idPenghuni): satu penghuni milik kost pemilik. */
    public static function getDetail(
        int $idPenghuni,
        int $idPemilikKost
    ): ?object {
        return self::queryMilik($idPemilikKost)
            ->where('p.idPenghuni', $idPenghuni)
            ->first();
    }

    /** simpanPerubahan(data): menyimpan data diri dan periode sewa. */
    public static function simpanPerubahan(int $idPenghuni, array $data): bool
    {
        DB::table('penghuni')
            ->where('idPenghuni', $idPenghuni)
            ->update([
                'namaLengkap' => $data['namaLengkap'],
                'noTelepon' => $data['noTelepon'],
                'tanggalMulaiSewa' => $data['tanggalMulaiSewa'],
                'tanggalAkhirSewa' => $data['tanggalAkhirSewa'],
            ]);

        return true;
    }

    /** ubahStatus(idPenghuni, status): true jika penghuni ditemukan. */
    public static function ubahStatus(int $idPenghuni, string $status): bool
    {
        $jumlahBaris = DB::table('penghuni')
            ->where('idPenghuni', $idPenghuni)
            ->where('status', '!=', $status)
            ->update(['status' => $status]);

        return $jumlahBaris === 1;
    }
}
