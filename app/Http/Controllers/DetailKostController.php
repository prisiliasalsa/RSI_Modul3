<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DetailKostController extends Controller
{
    // Mengambil identitas pengguna dari sesi Laravel
    private function akunLogin(Request $request): ?array
    {
        $idAkun = (int) $request->session()->get('idAkun', 0);

        if ($idAkun <= 0) {
            return null;
        }

        $jenisAkun = (string) $request->session()
            ->get('jenisAkun', '');

        if ($jenisAkun !== 'Calon Penghuni') {
            abort(403, 'Halaman khusus Calon Penghuni.');
        }

        $akun = DB::table('akun')
            ->where('idAkun', $idAkun)
            ->first(['idAkun', 'namaLengkap']);

        if (!$akun) {
            $request->session()->forget([
                'idAkun',
                'idPenghuni',
                'jenisAkun'
            ]);

            return null;
        }

        return [
            'idAkun' => (int) $akun->idAkun,
            'namaLengkap' => (string) $akun->namaLengkap,
            'jenisAkun' => $jenisAkun
        ];
    }

    // Mengubah lokasi foto ke URL Laravel
    private function urlFoto(?string $foto): string
    {
        $foto = trim((string) $foto);

        if ($foto === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $foto)) {
            return $foto;
        }

        $foto = ltrim($foto, '/');

        if (str_starts_with($foto, 'public/')) {
            $foto = substr($foto, 7);
        }

        if (str_starts_with($foto, 'storage/')) {
            return asset($foto);
        }

        if (is_file(public_path($foto))) {
            return asset($foto);
        }

        if (Storage::disk('public')->exists($foto)) {
            return Storage::disk('public')->url($foto);
        }

        return '';
    }

    // Menyesuaikan format foto kamar asli
    private function fotoKamar(?string $foto): string
    {
        $foto = trim((string) $foto);

        if ($foto === '') {
            return '[]';
        }

        $daftar = json_decode($foto, true);

        if (!is_array($daftar)) {
            $daftar = explode(',', $foto);
        }

        $hasil = [];

        foreach ($daftar as $item) {
            if (!is_string($item)) {
                continue;
            }

            $url = $this->urlFoto($item);

            if ($url !== '') {
                $hasil[] = $url;
            }
        }

        return json_encode(
            $hasil,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UC-04: Menampilkan Detail Kost
    |--------------------------------------------------------------------------
    */
    public function detailKost(Request $request, int $idKost)
    {
        $akunLoginLaravel = $this->akunLogin($request);

        if (!$akunLoginLaravel) {
            return redirect()->route('login');
        }

        // Kost harus sudah dipublikasikan
        $dataKost = DB::table('kost')
            ->where('idKost', $idKost)
            ->where('status', 'Dipublikasikan')
            ->first();

        if (!$dataKost) {
            abort(404, 'Data kost tidak tersedia.');
        }

        $kost = (array) $dataKost;

        // Menyesuaikan URL foto
        $kost['fotoUtama'] = $this->urlFoto(
            $kost['fotoUtama'] ?? null
        );

        // Mengambil kamar dari kost yang dipilih
        $daftarKamar = DB::table('kamar')
            ->where('idKost', $idKost)
            ->orderBy('nomorKamar')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->all();

        // Menggunakan tampilan asli teman
        return view('kost.detail', compact(
            'kost',
            'daftarKamar',
            'akunLoginLaravel'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UC-04: Menampilkan Detail Kamar
    |--------------------------------------------------------------------------
    */
    public function detailKamar(Request $request, int $idKamar)
    {
        $akunLoginLaravel = $this->akunLogin($request);

        if (!$akunLoginLaravel) {
            return redirect()->route('login');
        }

        // Mengambil kamar beserta informasi kost
        $dataKamar = DB::table('kamar as m')
            ->join(
                'kost as k',
                'm.idKost',
                '=',
                'k.idKost'
            )
            ->where('m.idKamar', $idKamar)
            ->where('k.status', 'Dipublikasikan')
            ->select(
                'm.*',
                'k.namaKost',
                'k.alamat',
                'k.lokasi',
                'k.tipeKost',
                'k.kontakPemilik',
                'k.fotoUtama',
                'k.status as statusKost'
            )
            ->first();

        if (!$dataKamar) {
            abort(404, 'Data kamar tidak tersedia.');
        }

        $kamar = (array) $dataKamar;

        // Menyesuaikan sumber foto
        $kamar['fotoUtama'] = $this->urlFoto(
            $kamar['fotoUtama'] ?? null
        );

        $kamar['fotoKamar'] = $this->fotoKamar(
            $kamar['fotoKamar'] ?? null
        );

        // Menggunakan tampilan detail kamar asli
        return view('kamar.detail', compact(
            'kamar',
            'akunLoginLaravel'
        ));
    }
}
