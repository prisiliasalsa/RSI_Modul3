<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * <<entity>> Kost (CD-09), memakai tabel `kost` pada database/schema.sql.
 * idKost pada schema bukan AUTO_INCREMENT, jadi id berikutnya dihitung di simpanDraft().
 */
class Kost extends Model
{
    public const STATUS_DRAFT          = 'Draft';
    public const STATUS_DIPUBLIKASIKAN = 'Dipublikasikan';

    protected $table = 'kost';
    protected $primaryKey = 'idKost';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    /**
     * cariKost(idPemilik) pada PC-09: daftar kost milik pemilik beserta jumlah kamarnya.
     * Koleksi kosong berarti pemilik belum punya kost.
     */
    public static function cariKost(int $idPemilik): Collection
    {
        return DB::table('kost as k')
            ->leftJoin('kamar as m', 'm.idKost', '=', 'k.idKost')
            ->where('k.idPemilik', $idPemilik)
            ->groupBy('k.idKost', 'k.namaKost', 'k.tipeKost', 'k.status')
            ->orderBy('k.idKost')
            ->get([
                'k.idKost',
                'k.namaKost',
                'k.tipeKost',
                'k.status',
                DB::raw('COUNT(m.idKamar) as jumlahKamar'),
            ]);
    }

    /** cariKost(id) pada CD-09 untuk satu kost (dataKost). Null jika tidak ditemukan. */
    public static function ambilKost(int $idKost): ?object
    {
        return DB::table('kost')->where('idKost', $idKost)->first();
    }

    /**
     * simpanDraft(data): menyimpan kost baru dengan status Draft.
     * Mengembalikan idKost yang baru, atau null jika gagal (hasilSimpan).
     */
    public static function simpanDraft(array $data): ?int
    {
        try {
            return DB::transaction(function () use ($data) {
                $idKost = (int) DB::selectOne(
                    'SELECT COALESCE(MAX(`idKost`), 0) + 1 AS id FROM `kost` FOR UPDATE'
                )->id;

                DB::table('kost')->insert([
                    'idKost'        => $idKost,
                    'idPemilik'     => $data['idPemilik'],
                    'namaKost'      => $data['namaKost'],
                    'alamat'        => $data['alamat'],
                    'lokasi'        => $data['lokasi'],
                    'tipeKost'      => $data['tipeKost'],
                    'fasilitasUmum' => $data['fasilitasUmum'],
                    'deskripsi'     => $data['deskripsi'],
                    'peraturan'     => $data['peraturan'],
                    'kontakPemilik' => $data['kontakPemilik'],
                    'fotoUtama'     => $data['fotoUtama'],
                    'fotoKost'      => $data['fotoKost'],
                    'status'        => self::STATUS_DRAFT,
                ]);

                return $idKost;
            });
        } catch (Throwable $e) {
            report($e); // tercatat di storage/logs/laravel.log
            return null;
        }
    }

    /** ubahData(id, data): memperbarui data kost, status kembali Draft (hasilUbah). */
    public static function ubahData(int $idKost, array $data): bool
    {
        try {
            DB::table('kost')->where('idKost', $idKost)->update([
                'namaKost'      => $data['namaKost'],
                'alamat'        => $data['alamat'],
                'lokasi'        => $data['lokasi'],
                'tipeKost'      => $data['tipeKost'],
                'fasilitasUmum' => $data['fasilitasUmum'],
                'deskripsi'     => $data['deskripsi'],
                'peraturan'     => $data['peraturan'],
                'kontakPemilik' => $data['kontakPemilik'],
                'fotoUtama'     => $data['fotoUtama'],
                'fotoKost'      => $data['fotoKost'],
                'status'        => self::STATUS_DRAFT,
            ]);
            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    /** ubahStatus(id, status) pada PC-09 (hasilUbah). */
    public static function ubahStatus(int $idKost, string $status): bool
    {
        try {
            DB::table('kost')->where('idKost', $idKost)->update(['status' => $status]);
            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }
}