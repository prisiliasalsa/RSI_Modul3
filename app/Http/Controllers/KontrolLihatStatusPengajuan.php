<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\PengajuanSewa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * <<control>> KontrolLihatStatusPengajuan (CD-06, SD-06, PC-06)
 * Boundary HalamanStatusPengajuan = resources/views/pengajuan/status.blade.php (UI-06.01, UI-06.03)
 *                                   resources/views/pengajuan/detail.blade.php (UI-06.02)
 */
class KontrolLihatStatusPengajuan extends Controller
{
    private const PESAN_GAGAL = 'Data pengajuan belum dapat dimuat. Silakan coba lagi.';

    // Teks keterangan status. Untuk "Disetujui" memakai teks UI-06.02
    // (PC-06 menulis: "Tagihan sudah terbit di menu Tagihan Sewa").
    private const INFO_DISETUJUI = 'tagihan sewa telah diterbitkan.';
    private const INFO_MENUNGGU  = 'pengajuan masih dalam proses verifikasi.';

    // ---------- Identitas calon penghuni yang sedang masuk ----------

    /**
     * ID calon penghuni diambil dari sesi login (session 'idAkun').
     * Selama halaman login belum ada, di lingkungan lokal boleh memakai ?idCalonPenghuni=...
     * HAPUS cadangan lewat URL ini setelah login selesai.
     */
    private function idCalonPenghuniAktif(Request $request): ?int
    {
        $id = session('idAkun');

        if ($id === null && app()->environment('local')) {
            $id = $request->query('idCalonPenghuni');
        }

        return ($id !== null && ctype_digit((string) $id)) ? (int) $id : null;
    }

    /** Parameter URL yang ikut dibawa saat memakai ?idCalonPenghuni (hanya untuk pengujian lokal). */
    private function parameterUji(): array
    {
        $id = request()->query('idCalonPenghuni');

        if (session('idAkun') === null && app()->environment('local') && $id !== null) {
            return ['idCalonPenghuni' => (string) $id];
        }

        return [];
    }

    // ---------- Navigasi ----------

    /** Calon penghuni memilih menu "Status Pengajuan" (GET /status-pengajuan). */
    public function pilihMenuStatus(Request $request): View|RedirectResponse
    {
        $id = $this->idCalonPenghuniAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        return $this->lihatStatus($id);
    }

    /** Memuat ulang daftar (tombol "Muat ulang" saat gagal). */
    public function muatUlang(): RedirectResponse
    {
        return redirect()->route('pengajuan.status', $this->parameterUji());
    }

    // ---------- Daftar pengajuan (PC-06 bagian 1) ----------

    public function lihatStatus(int $idCalonPenghuni): View
    {
        try {
            $daftar = PengajuanSewa::cariPengajuan($idCalonPenghuni);
        } catch (Throwable $e) {
            report($e); // tercatat di storage/logs/laravel.log
            return $this->tampilPesanGagal($idCalonPenghuni);
        }

        if ($daftar->isEmpty()) {
            return $this->tampilBelumAda($idCalonPenghuni);
        }

        $uji = $this->parameterUji();

        $baris = $daftar->map(fn ($p) => [
            'id'        => $p->idPengajuan,
            'kostKamar' => $p->namaKost . ' · ' . $p->nomorKamar,
            'tanggal'   => $this->tanggal($p->tanggalPengajuan),
            'status'    => (string) $p->status,
            'gaya'      => $this->gaya((string) $p->status),
            'url'       => route('pengajuan.detail', ['idPengajuan' => $p->idPengajuan] + $uji),
        ])->all();

        return view('pengajuan.status', $this->dataKerangka($idCalonPenghuni) + [
            'mode'    => 'daftar',
            'heading' => 'Status pengajuan',
            'baris'   => $baris,
        ]);
    }

    // ---------- Detail keputusan (PC-06 bagian 2) ----------

    /** Calon penghuni memilih satu pengajuan dari daftar (GET /status-pengajuan/{idPengajuan}). */
    public function lihatDetailStatus(Request $request, int $idPengajuan): View|RedirectResponse
    {
        $id = $this->idCalonPenghuniAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        try {
            $data = PengajuanSewa::ambilStatus($idPengajuan);
        } catch (Throwable $e) {
            report($e);
            return $this->tampilPesanGagal($id);
        }

        // Pengajuan harus ada dan milik calon penghuni yang sedang masuk
        if ($data === null || (int) $data->idCalonPenghuni !== $id) {
            abort(404);
        }

        return match ($data->status) {
            'Ditolak'             => $this->tampilStatus($id, $data, 'Ditolak', $data->alasanPenolakan, null),
            'Disetujui'           => $this->tampilStatus($id, $data, 'Disetujui', null, self::INFO_DISETUJUI),
            'Menunggu Verifikasi' => $this->tampilStatus($id, $data, 'Menunggu Verifikasi', null, self::INFO_MENUNGGU),
            default               => $this->tampilStatus($id, $data, (string) $data->status, null, null),
        };
    }

    // ---------- Tampilan (dipanggil boundary) ----------

    /** UI-06.02: informasi status beserta alasan atau keterangan tambahan. */
    public function tampilStatus(
        int $idCalonPenghuni,
        object $data,
        string $status,
        ?string $alasan,
        ?string $info
    ): View {
        $keterangan = null;

        if ($alasan !== null && trim($alasan) !== '') {
            $keterangan = 'alasan penolakan: ' . $alasan;
        } elseif ($info !== null) {
            $keterangan = $info;
        }

        $rincian = [
            'Mulai ' . $this->tanggal($data->tanggalMulaiSewa),
            $data->durasiSewa !== null ? $data->durasiSewa . ' bulan' : null,
            $data->jumlahPenghuni !== null ? $data->jumlahPenghuni . ' penghuni' : null,
        ];

        return view('pengajuan.detail', $this->dataKerangka($idCalonPenghuni) + [
            'heading'    => 'Detail keputusan pengajuan',
            'ringkasan'  => 'Kost ' . $data->namaKost . ' · ' . $data->nomorKamar,
            'rincian'    => implode(' · ', array_filter($rincian, fn ($r) => $r !== null)),
            'status'     => $status,
            'gaya'       => $this->gaya($status),
            'keterangan' => $keterangan,
            'urlTagihan' => url('/tagihan-sewa'),
            'urlCariKost' => url('/cari-kost'),
        ]);
    }

    /** UI-06.03: calon penghuni belum punya pengajuan. */
    public function tampilBelumAda(int $idCalonPenghuni): View
    {
        return view('pengajuan.status', $this->dataKerangka($idCalonPenghuni) + [
            'mode'    => 'kosong',
            'heading' => 'Belum ada pengajuan sewa',
        ]);
    }

    /** Pesan gagal saat terjadi gangguan data. */
    public function tampilPesanGagal(int $idCalonPenghuni): View
    {
        return view('pengajuan.status', $this->dataKerangka($idCalonPenghuni) + [
            'mode'    => 'gagal',
            'heading' => 'Status pengajuan',
            'pesan'   => self::PESAN_GAGAL,
        ]);
    }

    // ---------- Pembantu ----------

    /** Data yang dipakai kerangka halaman (nama pengguna di pojok kanan atas). */
    private function dataKerangka(int $idCalonPenghuni): array
    {
        $nama = 'Calon Penghuni';

        try {
            $akun = Akun::find($idCalonPenghuni);
            if ($akun && trim((string) $akun->namaLengkap) !== '') {
                $nama = $akun->namaLengkap;
            }
        } catch (Throwable $e) {
            report($e);
        }

        $kata    = preg_split('/\s+/', trim($nama));
        $inisial = mb_strtoupper(mb_substr($kata[0], 0, 1) . (isset($kata[1]) ? mb_substr($kata[1], 0, 1) : ''));

        return [
            'pengguna' => ['nama' => $nama, 'inisial' => $inisial, 'peran' => 'Calon Penghuni'],
            'qs'       => $this->parameterUji(),
        ];
    }

    private function tanggal(?string $tanggal): string
    {
        if ($tanggal === null || $tanggal === '') {
            return '-';
        }

        return Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
    }

    private function gaya(string $status): string
    {
        return match ($status) {
            'Disetujui'           => 'disetujui',
            'Ditolak'             => 'ditolak',
            'Menunggu Verifikasi' => 'menunggu',
            default               => 'netral',
        };
    }
}