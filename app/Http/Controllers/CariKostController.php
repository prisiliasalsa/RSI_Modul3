<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CariKostController extends Controller
{
    public const OPSI_HARGA = [
        '' => 'Semua harga',
        '0-500000' => 'Di bawah Rp500.000',
        '500000-1500000' => 'Rp500.000 – Rp1.500.000',
        '1500000-' => 'Di atas Rp1.500.000',
    ];

    public const OPSI_TIPE = [
        'Putra', 'Putri', 'Campur',
    ];

    public const OPSI_FASILITAS = [
        'Wi-Fi', 'AC', 'Kamar mandi dalam',
        'Lemari', 'Meja belajar',
        'Parkir', 'Dapur bersama',
    ];

    public function index(Request $request)
    {
        $idAkun = $request->session()->get('idAkun');
        $peran = $request->session()->get('jenisAkun');

        if (!$idAkun) {
            return redirect()->route('login');
        }

        if (!in_array($peran, ['Calon Penghuni', 'Penghuni'], true)) {
            abort(403, 'Halaman khusus Calon Penghuni dan Penghuni.');
        }

        $akun = DB::table('akun')
            ->where('idAkun', $idAkun)
            ->first(['idAkun', 'namaLengkap', 'jenisAkun']);

        if (!$akun) {
            $request->session()->forget([
                'idAkun', 'idPenghuni', 'jenisAkun',
            ]);

            return redirect()->route('login');
        }

        $akunLogin = [
            'idAkun' => (int) $akun->idAkun,
            'namaLengkap' => $akun->namaLengkap,
            'jenisAkun' => $peran,
        ];

        // Membaca kriteria pencarian.
        $kataKunci = mb_substr(
            trim((string) $request->query('q', '')),
            0,
            100
        );

        $harga = (string) $request->query('rentangHarga', '');
        $tipe = (string) $request->query('tipeKost', '');
        $fasilitas = (string) $request->query('fasilitas', '');

        $kriteria = [
            'kataKunci' => $kataKunci,
            'rentangHarga' => array_key_exists($harga, self::OPSI_HARGA)
                ? $harga
                : '',
            'tipeKost' => in_array($tipe, self::OPSI_TIPE, true)
                ? $tipe
                : '',
            'fasilitas' => in_array($fasilitas, self::OPSI_FASILITAS, true)
                ? $fasilitas
                : '',
        ];

        // Mengambil harga kamar termurah untuk setiap kost.
        $hargaKamar = DB::table('kamar')
            ->select('idKost')
            ->selectRaw('MIN(hargaSewa) AS hargaKamarMulai')
            ->groupBy('idKost');

        // Subquery agar harga hasil perhitungan dapat difilter dan diurutkan.
        $query = DB::query()
            ->fromSub(
                DB::table('kost as k')
                    ->leftJoinSub($hargaKamar, 'hk', function ($join) {
                        $join->on('hk.idKost', '=', 'k.idKost');
                    })
                    ->select('k.*')
                    ->selectRaw(
                        'COALESCE(hk.hargaKamarMulai, 0) AS hargaKamarMulai'
                    ),
                'hasil'
            )
            ->where('status', 'Dipublikasikan');

        if ($kriteria['kataKunci'] !== '') {
            $keyword = $kriteria['kataKunci'];

            $query->where(function ($q) use ($keyword) {
                $q->where('namaKost', 'like', "%{$keyword}%")
                    ->orWhere('alamat', 'like', "%{$keyword}%")
                    ->orWhere('lokasi', 'like', "%{$keyword}%");
            });
        }

        if ($kriteria['rentangHarga'] !== '') {
            [$min, $max] = array_pad(
                explode('-', $kriteria['rentangHarga'], 2),
                2,
                ''
            );

            if ($min !== '') {
                $query->where('hargaKamarMulai', '>=', (int) $min);
            }

            if ($max !== '') {
                $query->where('hargaKamarMulai', '<=', (int) $max);
            }
        }

        if ($kriteria['tipeKost'] !== '') {
            $query->where('tipeKost', $kriteria['tipeKost']);
        }

        if ($kriteria['fasilitas'] !== '') {
            $query->where(
                'fasilitasUmum',
                'like',
                '%' . $kriteria['fasilitas'] . '%'
            );
        }

        $daftarKost = $query
            ->orderBy('hargaKamarMulai')
            ->get()
            ->map(fn ($item) => new KartuKostUC03($item))
            ->all();

        // Adapter untuk tampilan UC-03.
        $kontrol = new class($kriteria, $daftarKost) {
            public function __construct(
                private array $kriteria,
                private array $daftar
            ) {}

            public function getKriteria(): array
            {
                return $this->kriteria;
            }

            public function getDaftarKost(): array
            {
                return $this->daftar;
            }

            public function pesanTidakDitemukan(): string
            {
                return 'Kost tidak ditemukan';
            }

            public function ubahKriteria(): string
            {
                return route('kost.cari') . '?' .
                    http_build_query([
                        'q' => $this->kriteria['kataKunci'],
                        'rentangHarga' => $this->kriteria['rentangHarga'],
                        'tipeKost' => $this->kriteria['tipeKost'],
                        'fasilitas' => $this->kriteria['fasilitas'],
                    ]) . '#q';
            }
        };

        return view('kost.cari', compact(
            'akunLogin',
            'kontrol'
        ));
    }
}

// Adapter data untuk tampilan kartu kost.
class KartuKostUC03
{
    public function __construct(
        private object $data
    ) {}

    public function getIdKost(): int
    {
        return (int) $this->data->idKost;
    }

    public function getNamaKost(): string
    {
        return (string) ($this->data->namaKost ?? '');
    }

    public function getAlamat(): string
    {
        return (string) ($this->data->alamat ?? '');
    }

    public function getLokasi(): string
    {
        return (string) ($this->data->lokasi ?? '');
    }

    public function getTipeKost(): string
    {
        return (string) ($this->data->tipeKost ?? '');
    }

    public function getHargaMulai(): float
    {
        return (float) ($this->data->hargaKamarMulai ?? 0);
    }

    public function getDaftarFasilitas(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(
                ',',
                (string) ($this->data->fasilitasUmum ?? '')
            )
        )));
    }

    public function getFotoUtama(): ?string
    {
        $foto = trim((string) ($this->data->fotoUtama ?? ''));

        if ($foto === '') {
            return null;
        }

        if (filter_var($foto, FILTER_VALIDATE_URL)) {
            return str_starts_with($foto, 'https://')
                || str_starts_with($foto, 'http://')
                ? $foto
                : null;
        }

        if (str_starts_with($foto, 'storage/')) {
            return asset($foto);
        }

        return Storage::disk('public')->url($foto);
    }
}