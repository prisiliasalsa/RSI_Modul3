<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * <<entity>> Akun (CD-01), memakai tabel `Akun` pada database/schema.sql.
 * Schema tidak diubah, jadi model ini menyesuaikan diri dengan schema:
 * nama tabel `Akun`, kunci `idAkun` (bukan auto increment), tanpa created_at/updated_at.
 */
class Akun extends Model
{
    /** Pilihan dropdown "Jenis akun" (UI-01.01). Kolom jenisAkun = VARCHAR(30). */
    public const JENIS_AKUN_PILIHAN = ['Calon Penghuni', 'Pemilik Kost'];

    protected $table = 'Akun';
    protected $primaryKey = 'idAkun';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'idAkun', 'namaLengkap', 'email', 'noTelepon', 'kataSandi', 'jenisAkun',
    ];

    /** cekEmail(email): true jika email sudah ada di tabel Akun (statusEmail). */
    public static function cekEmail(string $email): bool
    {
        return self::where('email', $email)->exists();
    }

    /** Nama method pada PC-01; isinya sama dengan cekEmail() pada CD-01. */
    public static function cekEmailTerdaftar(string $email): bool
    {
        return self::cekEmail($email);
    }

    /**
     * simpanAkun(...): true jika akun tersimpan (statusSimpan).
     * Urutan parameter mengikuti PC-01 (jenisAkun terakhir).
     */
    public static function simpanAkun(
        string $nama,
        string $email,
        string $noTelepon,
        string $kataSandiTerenkripsi,
        string $jenisAkun
    ): bool {
        try {
            DB::transaction(function () use ($nama, $email, $noTelepon, $kataSandiTerenkripsi, $jenisAkun) {
                // idAkun pada schema bukan AUTO_INCREMENT, jadi id berikutnya dihitung di sini.
                $idAkun = (int) DB::selectOne(
                    'SELECT COALESCE(MAX(`idAkun`), 0) + 1 AS id FROM `Akun` FOR UPDATE'
                )->id;

                // Atribut "nama" pada diagram = kolom namaLengkap pada schema.
                self::create([
                    'idAkun'      => $idAkun,
                    'namaLengkap' => $nama,
                    'email'       => $email,
                    'noTelepon'   => $noTelepon,
                    'kataSandi'   => $kataSandiTerenkripsi,
                    'jenisAkun'   => $jenisAkun,
                ]);
            });
            return true;
        } catch (Throwable $e) {
            report($e); // tercatat di storage/logs/laravel.log
            return false;
        }
    }
}