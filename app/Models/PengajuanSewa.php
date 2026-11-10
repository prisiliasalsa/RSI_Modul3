<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * <<entity>> PengajuanSewa (CD-06), memakai tabel `pengajuansewa` pada database/schema.sql.
 * Model ini hanya membaca data (UC-06 Melihat pengajuan sewa).
 *
 * Nama kost dan nomor kamar diambil lewat JOIN ke tabel `kost` (namaKost)
 * dan `kamar` (nomorKamar) agar bisa tampil seperti "Cemara · A01" (UI-06.01).
 */
class PengajuanSewa extends Model
{
    protected $table = 'pengajuansewa';
    protected $primaryKey = 'idPengajuan';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    private static function kueriDasar(): Builder
    {
        return DB::table('pengajuansewa as p')
            ->join('kost as k', 'k.idKost', '=', 'p.idKost')
            ->join('kamar as m', 'm.idKamar', '=', 'p.idKamar')
            ->select(
                'p.idPengajuan',
                'p.idCalonPenghuni',
                'p.tanggalMulaiSewa',
                'p.durasiSewa',
                'p.jumlahPenghuni',
                'p.status',
                'p.alasanPenolakan',
                'p.tanggalPengajuan',
                'k.namaKost',
                'm.nomorKamar'
            );
    }

    /**
     * cariPengajuan(idCalonPenghuni): daftar pengajuan milik calon penghuni (dataPengajuan),
     * terbaru di atas. Koleksi kosong berarti belum ada pengajuan.
     */
    public static function cariPengajuan(int $idCalonPenghuni): Collection
    {
        return self::kueriDasar()
            ->where('p.idCalonPenghuni', $idCalonPenghuni)
            ->orderByDesc('p.tanggalPengajuan')
            ->orderByDesc('p.idPengajuan')
            ->get();
    }

    /**
     * ambilStatus(idPengajuan): data satu pengajuan beserta status dan alasan penolakan (dataStatus).
     * Mengembalikan null jika pengajuan tidak ditemukan.
     */
    public static function ambilStatus(int $idPengajuan): ?object
    {
        return self::kueriDasar()
            ->where('p.idPengajuan', $idPengajuan)
            ->first();
    }

    // UC-11: daftar pengajuan berstatus Menunggu untuk kost milik pemilik
    public static function getPengajuanMenunggu(int $idPemilik): Collection
    {
        return DB::table('pengajuansewa as ps')
            ->join('kost as k', 'k.idKost', '=', 'ps.idKost')
            ->join('kamar as km', 'km.idKamar', '=', 'ps.idKamar')
            ->where('k.idPemilik', $idPemilik)
            ->where('ps.status', 'Menunggu')
            ->orderByDesc('ps.tanggalPengajuan')
            ->get([
                'ps.idPengajuan',
                'ps.idCalonPenghuni',
                'ps.tanggalPengajuan',
                'ps.durasiSewa',
                'ps.jumlahPenghuni',
                'k.namaKost',
                'km.nomorKamar',
            ]);
    }

    // UC-11: detail satu pengajuan, hanya jika kost-nya milik pemilik yang login
    public static function getDetailPengajuan(int $idPengajuan, int $idPemilik): ?object
    {
        return DB::table('pengajuansewa as ps')
            ->join('kost as k', 'k.idKost', '=', 'ps.idKost')
            ->join('kamar as km', 'km.idKamar', '=', 'ps.idKamar')
            ->where('ps.idPengajuan', $idPengajuan)
            ->where('k.idPemilik', $idPemilik)
            ->first([
                'ps.*',
                'k.namaKost',
                'km.nomorKamar',
                'km.hargaSewa',
            ]);
    }

    // UC-11: ubah status pengajuan (Disetujui / Ditolak)
    public static function updateStatus(int $idPengajuan, string $status, ?string $alasan = null): bool
    {
        try {
            DB::table('pengajuansewa')
                ->where('idPengajuan', $idPengajuan)
                ->update([
                    'status' => $status,
                    'alasanPenolakan' => $alasan,
                ]);
            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }
}