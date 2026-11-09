<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Penghuni;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * <<control>> KontrolPenghuni (CD-12)
 * UC-12 Mengelola Data Penghuni, pseudocode PC-12.
 * Boundary: resources/views/data-penghuni (HalamanDataPenghuni)
 */
class KontrolPenghuni extends Controller
{
    // Pilihan filter status pada daftar penghuni
    private array $pilihanStatus = ['Aktif', 'Tidak Aktif', 'Semua'];

    // Pesan galat hasil validasiForm()
    private array $galat = [];

    // UC-12 langkah 1-3: daftar penghuni pada kost milik pemilik
    public function lihatDaftarPenghuni(Request $request): View
    {
        $pemilik = $this->pemilikAktif($request);
        $cari = trim((string) $request->query('cari', ''));
        $status = $request->query('status', 'Aktif');
        if (!in_array($status, $this->pilihanStatus, true)) {
            $status = 'Aktif';
        }

        $gangguan = false;
        $adaPenghuni = false;

        try {
            // Alternative Flow 1: dicek tanpa filter apa pun
            $adaPenghuni = Penghuni::getPenghuni($pemilik->idAkun)->exists();

            $daftar = Penghuni::getPenghuni(
                $pemilik->idAkun,
                $status === 'Semua' ? '' : $status,
                $cari
            )
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($baris) => $this->formatPenghuni($baris));
        } catch (Throwable $error) {
            // Pesan "Terjadi gangguan data"
            report($error);
            $gangguan = true;
            $daftar = new LengthAwarePaginator([], 0, 10);
        }

        // Daftar kosong ditangani view: pesanBelumAdaData()
        return view('data-penghuni.daftar', [
            'pemilik' => $pemilik,
            'namaKostHeader' => $this->namaKostHeader($pemilik->idAkun),
            'daftar' => $daftar,
            'cari' => $cari,
            'status' => $status,
            'pilihanStatus' => $this->pilihanStatus,
            'adaPenghuni' => $adaPenghuni,
            'gangguan' => $gangguan,
        ]);
    }

    // UC-12 langkah 4-7: detail penghuni dalam formulir ubah.
    // ?aksi=akhiri menampilkan konfirmasi akhiri sewa.
    public function lihatDetailPenghuni(
        Request $request,
        int $idPenghuni
    ): View|RedirectResponse {
        $pemilik = $this->pemilikAktif($request);

        try {
            $penghuni = Penghuni::getDetail($idPenghuni, $pemilik->idAkun);
        } catch (Throwable $error) {
            report($error);
            return redirect()->route('pemilik.penghuni.daftar')
                ->with('pesanGagal', 'Terjadi gangguan data');
        }

        abort_unless($penghuni, 404, 'Penghuni tidak ditemukan.');

        $aktif = $penghuni->status === Penghuni::STATUS_AKTIF;
        $aksi = $aktif && $request->query('aksi') === 'akhiri' ? 'akhiri' : null;

        return view('data-penghuni.detail', [
            'pemilik' => $pemilik,
            'namaKostHeader' => $penghuni->namaKost,
            'penghuni' => $this->formatPenghuni($penghuni),
            'aktif' => $aktif,
            'aksi' => $aksi,
        ]);
    }

    // Langkah 10 dan Alternative Flow 1 (10a): kelengkapan isian
    public function validasiForm(array $data): bool
    {
        $this->galat = [];
        $wajib = [
            'namaLengkap',
            'noTelepon',
            'tanggalMulaiSewa',
            'tanggalAkhirSewa',
        ];

        foreach ($wajib as $isian) {
            if (trim((string) ($data[$isian] ?? '')) === '') {
                $this->galat[$isian] = 'Form belum lengkap';
            }
        }

        if (!isset($this->galat['noTelepon'])
            && !preg_match('/^[0-9+][0-9\s-]{7,19}$/', trim($data['noTelepon']))) {
            $this->galat['noTelepon'] = 'Nomor telepon tidak valid';
        }

        foreach (['tanggalMulaiSewa', 'tanggalAkhirSewa'] as $isian) {
            if (!isset($this->galat[$isian]) && !$this->tanggalValid($data[$isian])) {
                $this->galat[$isian] = 'Format tanggal tidak valid';
            }
        }

        // Akhir sewa tidak boleh mendahului mulai sewa
        if ($this->galat === []
            && $data['tanggalAkhirSewa'] < $data['tanggalMulaiSewa']) {
            $this->galat['tanggalAkhirSewa'] = 'Akhir sewa harus setelah mulai sewa';
        }

        return $this->galat === [];
    }

    // UC-12 langkah 8-12: menyimpan perubahan data penghuni
    public function ubahDataPenghuni(
        Request $request,
        int $idPenghuni
    ): RedirectResponse {
        $pemilik = $this->pemilikAktif($request);
        $penghuni = Penghuni::getDetail($idPenghuni, $pemilik->idAkun);
        abort_unless($penghuni, 404, 'Penghuni tidak ditemukan.');

        $data = [
            'namaLengkap' => trim((string) $request->input('namaLengkap')),
            'noTelepon' => trim((string) $request->input('noTelepon')),
            'tanggalMulaiSewa' => (string) $request->input('tanggalMulaiSewa'),
            'tanggalAkhirSewa' => (string) $request->input('tanggalAkhirSewa'),
        ];

        if (!$this->validasiForm($data)) {
            // Pemilik Kost kembali ke langkah 8
            return back()->withInput()->withErrors($this->galat);
        }

        $data['namaLengkap'] = mb_substr($data['namaLengkap'], 0, 100);

        try {
            $hasil = Penghuni::simpanPerubahan($idPenghuni, $data);
        } catch (Throwable $error) {
            report($error);
            $hasil = false;
        }

        if (!$hasil) {
            return back()->withInput()->with(
                'pesanGagal',
                'Data penghuni gagal diperbarui, silakan coba kembali'
            );
        }

        return redirect()
            ->route('pemilik.penghuni.detail', $idPenghuni)
            ->with('pesanBerhasil', 'Data penghuni berhasil diperbarui');
    }

    // Alternative Flow 2: dipanggil setelah pemilik memilih "Ya"
    public function akhiriSewa(
        Request $request,
        int $idPenghuni
    ): RedirectResponse {
        $pemilik = $this->pemilikAktif($request);
        $penghuni = Penghuni::getDetail($idPenghuni, $pemilik->idAkun);
        abort_unless($penghuni, 404, 'Penghuni tidak ditemukan.');

        try {
            DB::transaction(function () use ($penghuni) {
                $hasilPenghuni = Penghuni::ubahStatus(
                    (int) $penghuni->idPenghuni,
                    Penghuni::STATUS_TIDAK_AKTIF
                );
                $hasilKamar = Kamar::ubahStatus(
                    (int) $penghuni->idKamar,
                    'Tersedia'
                );

                if (!$hasilPenghuni || !$hasilKamar) {
                    throw new RuntimeException('Sewa tidak dapat diakhiri.');
                }
            });
        } catch (Throwable $error) {
            report($error);
            return redirect()
                ->route('pemilik.penghuni.detail', $idPenghuni)
                ->with('pesanGagal', 'Sewa penghuni gagal diakhiri, silakan coba kembali');
        }

        return redirect()
            ->route('pemilik.penghuni.detail', $idPenghuni)
            ->with('pesanBerhasil', 'Sewa penghuni berhasil diakhiri');
    }

    // Menyiapkan label periode sewa dan nilai tanggal untuk form
    private function formatPenghuni(object $baris): object
    {
        $mulai = $baris->tanggalMulaiSewa
            ? Carbon::parse($baris->tanggalMulaiSewa)->locale('id')
            : null;
        $akhir = $baris->tanggalAkhirSewa
            ? Carbon::parse($baris->tanggalAkhirSewa)->locale('id')
            : null;

        $baris->periodeSewa = ($mulai?->translatedFormat('M Y') ?? '-')
            . ' – ' . ($akhir?->translatedFormat('M Y') ?? '-');
        $baris->mulaiInput = $mulai?->format('Y-m-d') ?? '';
        $baris->akhirInput = $akhir?->format('Y-m-d') ?? '';
        $baris->namaKostPendek = trim(preg_replace('/^Kost\s+/i', '', (string) $baris->namaKost));
        $baris->hargaSewaRupiah = 'Rp' . number_format((float) $baris->hargaSewa, 0, ',', '.');
        $baris->statusKelas = $baris->status === Penghuni::STATUS_AKTIF
            ? 'status-aktif'
            : 'status-nonaktif';

        return $baris;
    }

    private function tanggalValid(string $tanggal): bool
    {
        $hasil = \DateTime::createFromFormat('!Y-m-d', $tanggal);

        return $hasil !== false && $hasil->format('Y-m-d') === $tanggal;
    }

    // Nama kost pertama milik pemilik untuk label di header
    private function namaKostHeader(int $idPemilik): string
    {
        return DB::table('kost')
            ->where('idPemilik', $idPemilik)
            ->orderBy('idKost')
            ->value('namaKost') ?: 'Kost.io';
    }

    // Pemilik Kost yang login dari sesi UC-02
    private function pemilikAktif(Request $request): object
    {
        $idAkun = $request->session()->get('idAkun') ?? Auth::id();
        abort_unless($idAkun, 403, 'Silakan login sebagai pemilik kost.');

        $pemilik = DB::table('akun')
            ->where('idAkun', $idAkun)
            ->where('jenisAkun', 'Pemilik Kost')
            ->first();
        abort_unless($pemilik, 403, 'Akses khusus pemilik kost.');

        return $pemilik;
    }
}
