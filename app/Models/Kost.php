<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Model Kost untuk UC-09 dan UC-10.
 * Menggunakan nama kolom sesuai schema.sql.
 */
class Kost extends Model
{
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_DIPUBLIKASIKAN = 'Dipublikasikan';

    protected $table = 'kost';
    protected $primaryKey = 'idKost';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'idKost',
        'idPemilik',
        'namaKost',
        'alamat',
        'lokasi',
        'tipeKost',
        'fasilitasUmum',
        'deskripsi',
        'peraturan',
        'kontakPemilik',
        'fotoUtama',
        'fotoKost',
        'status',
        'hargaMulai',
    ];

    // Relasi Kost dengan Akun Pemilik
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(
            Akun::class,
            'idPemilik',
            'idAkun'
        );
    }

    // Relasi Kost dengan Kamar
    public function kamar(): HasMany
    {
        return $this->hasMany(
            Kamar::class,
            'idKost',
            'idKost'
        );
    }

    // UC-09: Mengambil daftar kost milik pemilik
    public static function cariKost(int $idPemilik): Collection
    {
        return DB::table('kost as k')
            ->leftJoin(
                'kamar as m',
                'm.idKost',
                '=',
                'k.idKost'
            )
            ->where('k.idPemilik', $idPemilik)
            ->groupBy(
                'k.idKost',
                'k.namaKost',
                'k.tipeKost',
                'k.status'
            )
            ->orderBy('k.idKost')
            ->get([
                'k.idKost',
                'k.namaKost',
                'k.tipeKost',
                'k.status',
                DB::raw('COUNT(m.idKamar) as jumlahKamar'),
            ]);
    }

    // UC-09: Mengambil detail satu kost
    public static function ambilKost(int $idKost): ?object
    {
        return DB::table('kost')
            ->where('idKost', $idKost)
            ->first();
    }

    // UC-09: Menyimpan kost baru sebagai Draft
    public static function simpanDraft(array $data): ?int
    {
        try {
            return DB::transaction(function () use ($data) {

                // ID dihitung karena idKost bukan AUTO_INCREMENT
                $idKost = (int) DB::selectOne(
                    'SELECT COALESCE(MAX(`idKost`), 0) + 1 AS id
                     FROM `kost` FOR UPDATE'
                )->id;

                DB::table('kost')->insert([
                    'idKost' => $idKost,
                    'idPemilik' => $data['idPemilik'],
                    'namaKost' => $data['namaKost'],
                    'alamat' => $data['alamat'],
                    'lokasi' => $data['lokasi'],
                    'tipeKost' => $data['tipeKost'],
                    'fasilitasUmum' => $data['fasilitasUmum'],
                    'deskripsi' => $data['deskripsi'],
                    'peraturan' => $data['peraturan'],
                    'kontakPemilik' => $data['kontakPemilik'],
                    'fotoUtama' => $data['fotoUtama'],
                    'fotoKost' => $data['fotoKost'],
                    'status' => self::STATUS_DRAFT,
                ]);

                return $idKost;
            });

        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }

    // UC-09: Memperbarui informasi kost
    public static function ubahData(
        int $idKost,
        array $data
    ): bool {
        try {
            DB::table('kost')
                ->where('idKost', $idKost)
                ->update([
                    'namaKost' => $data['namaKost'],
                    'alamat' => $data['alamat'],
                    'lokasi' => $data['lokasi'],
                    'tipeKost' => $data['tipeKost'],
                    'fasilitasUmum' => $data['fasilitasUmum'],
                    'deskripsi' => $data['deskripsi'],
                    'peraturan' => $data['peraturan'],
                    'kontakPemilik' => $data['kontakPemilik'],
                    'fotoUtama' => $data['fotoUtama'],
                    'fotoKost' => $data['fotoKost'],
                    'status' => self::STATUS_DRAFT,
                ]);

            return true;

        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    // UC-09: Memperbarui status publikasi kost
    public static function ubahStatus(
        int $idKost,
        string $status
    ): bool {
        try {
            DB::table('kost')
                ->where('idKost', $idKost)
                ->update([
                    'status' => $status
                ]);

            return true;

        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }
}
