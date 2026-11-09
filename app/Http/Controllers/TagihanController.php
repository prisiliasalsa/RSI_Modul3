<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class TagihanController extends Controller
{
    // Atribut KontrolTagihan (CD-07)
    private int $maksUkuranBerkas = 2048; // KB, maksimal 2 MB
    private array $formatBerkas = ['jpg', 'jpeg', 'png', 'pdf'];

    // Pesan galat hasil validasiForm()
    private array $galat = [];

    // UC-07 langkah 1-2: menampilkan daftar tagihan sewa
    public function lihatTagihan(Request $request): View
    {
        $penghuni = $this->penghuniAktif($request);
        abort_unless(
            $penghuni,
            403,
            'Silakan masuk sebagai penghuni dengan sewa aktif.'
        );

        $gangguan = false;
        $belumLunas = 0;
        $idBerikutnya = null;

        try {
            $daftar = DB::table('tagihansewa')
                ->where('idPenghuni', $penghuni->idPenghuni)
                ->orderByDesc('periode')
                ->paginate(5)
                ->withQueryString()
                ->through(fn ($t) => $this->formatTagihan($t));

            $belumLunas = DB::table('tagihansewa')
                ->where('idPenghuni', $penghuni->idPenghuni)
                ->where('status', '!=', 'Lunas')
                ->sum('jumlah');

            $idBerikutnya = DB::table('tagihansewa')
                ->where('idPenghuni', $penghuni->idPenghuni)
                ->where('status', 'Belum Lunas')
                ->orderBy('periode')
                ->value('idTagihan');
        } catch (Throwable $error) {
            report($error);
            $gangguan = true;
            $daftar = new LengthAwarePaginator([], 0, 5);
        }

        // Daftar kosong ditangani view: pesanBelumAdaTagihan()
        return view('tagihan.daftar', [
            'penghuni' => $penghuni,
            'daftar' => $daftar,
            'belumLunas' => $this->rupiah($belumLunas),
            'idBerikutnya' => $idBerikutnya,
            'gangguan' => $gangguan,
        ]);
    }

    // UC-07 langkah 3-5: detail tagihan dan metode pembayaran
    public function ambilDetailTagihan(
        Request $request,
        int $idTagihan
    ): View|RedirectResponse {
        $penghuni = $this->penghuniAktif($request);
        abort_unless(
            $penghuni,
            403,
            'Silakan masuk sebagai penghuni dengan sewa aktif.'
        );

        $metode = $request->query('metode', 'Transfer Bank');
        if (!in_array($metode, ['QRIS', 'Transfer Bank'], true)) {
            $metode = 'Transfer Bank';
        }

        try {
            $tagihan = $this->tagihanMilik($penghuni, $idTagihan);
            $pembayaran = $this->ambilMetodePembayaran(
                $penghuni->idPemilik,
                $metode,
                (int) $request->query('bank')
            );
        } catch (Throwable $error) {
            report($error);
            return redirect()->route('tagihan.daftar')->withErrors([
                'gangguan' => 'Terjadi gangguan data',
            ]);
        }

        abort_unless($tagihan, 404);

        return view('tagihan.bayar', [
            'penghuni' => $penghuni,
            'tagihan' => $this->formatTagihan($tagihan),
            'bisaBayar' => $tagihan->status === 'Belum Lunas',
            'metode' => $metode,
            'qris' => $metode === 'QRIS',
            'qrisData' => $pembayaran['qris'],
            'daftarBank' => $pembayaran['daftarBank'],
            'bank' => $pembayaran['bank'],
        ]);
    }

    // UC-07 langkah 8-11: menerima bukti, menyimpan, ubah status
    public function submitBukti(
        Request $request,
        int $idTagihan
    ): RedirectResponse {
        $penghuni = $this->penghuniAktif($request);
        abort_unless(
            $penghuni,
            403,
            'Silakan masuk sebagai penghuni dengan sewa aktif.'
        );

        $tagihan = $this->tagihanMilik($penghuni, $idTagihan);
        abort_unless($tagihan, 404);

        if ($tagihan->status !== 'Belum Lunas') {
            return redirect()->route('tagihan.daftar')->withErrors([
                'gangguan' => 'Tagihan sedang diverifikasi atau lunas.',
            ]);
        }

        $isian = $request->only('metode', 'idBank', 'catatan');
        $berkas = $request->file('bukti');

        // Alternative Flow 2: metode, bank, atau bukti belum diisi
        if (!$this->validasiForm($request)) {
            return back()->withInput($isian)->withErrors($this->galat);
        }

        // Alternative Flow 3: format atau ukuran berkas tidak sesuai
        if (!$this->validasiBerkas($berkas)) {
            return back()->withInput($isian)->withErrors([
                'bukti' => 'Berkas tidak valid, gunakan format JPG/PNG/PDF '
                    . 'dengan ukuran maksimal 2 MB.',
            ]);
        }

        $pathBukti = null;

        try {
            $tersedia = $this->ambilMetodePembayaran(
                $penghuni->idPemilik,
                $request->input('metode'),
                (int) $request->input('idBank')
            );
            $dipakai = $request->input('metode') === 'QRIS'
                ? $tersedia['qris']
                : $tersedia['bank'];

            if (!$dipakai) {
                return back()->withInput($isian)->withErrors([
                    'metode' => 'Metode pembayaran tidak tersedia.',
                ]);
            }

            $pathBukti = $berkas->store('bukti', 'local');
            if ($pathBukti === false) {
                throw new RuntimeException('Penyimpanan bukti gagal.');
            }

            // Catatan tidak disimpan: PDM Pembayaran tanpa kolom catatan
            $idPembayaran = DB::transaction(
                function () use ($tagihan, $penghuni, $dipakai, $request,
                    $pathBukti) {
                    // Pembayaran.simpanBukti()
                    $id = DB::table('pembayaran')->insertGetId([
                        'idTagihan' => $tagihan->idTagihan,
                        'idPenghuni' => $penghuni->idPenghuni,
                        'idMetode' => $dipakai->idMetode,
                        'metode' => $request->input('metode'),
                        'jumlahPembayaran' => $tagihan->jumlah,
                        'buktiBayar' => $pathBukti,
                        'tanggalPembayaran' => now(),
                        'statusPembayaran' => 'Menunggu Verifikasi',
                        'alasanPenolakan' => null,
                    ]);

                    // TagihanSewa.ubahStatus()
                    DB::table('tagihansewa')
                        ->where('idTagihan', $tagihan->idTagihan)
                        ->update(['status' => 'Menunggu Verifikasi']);

                    return $id;
                }
            );
        } catch (Throwable $error) {
            report($error);

            if (is_string($pathBukti)) {
                try {
                    Storage::disk('local')->delete($pathBukti);
                } catch (Throwable $cleanupError) {
                    report($cleanupError);
                }
            }

            return back()->withInput($isian)->withErrors([
                'kirim' => 'Bukti pembayaran gagal dikirim, '
                    . 'silakan coba kembali.',
            ]);
        }

        return redirect()->route('tagihan.berhasil', $idPembayaran);
    }

    // Halaman hasil: Bukti pembayaran terkirim (tampilBerhasil)
    public function buktiTerkirim(
        Request $request,
        int $idPembayaran
    ): View {
        $penghuni = $this->penghuniAktif($request);
        abort_unless($penghuni, 403);

        $pembayaran = DB::table('pembayaran')
            ->where('idPembayaran', $idPembayaran)
            ->where('idPenghuni', $penghuni->idPenghuni)
            ->first();
        abort_unless($pembayaran, 404);

        return view('tagihan.berhasil', compact('penghuni', 'pembayaran'));
    }

    // Metode QRIS dan daftar bank milik Pemilik Kost
    private function ambilMetodePembayaran(
        int $idPemilikKost,
        ?string $metode,
        ?int $idBank
    ): array {
        $daftarMetode = DB::table('metodepembayaran')
            ->where('idPemilikKost', $idPemilikKost)
            ->get();

        $qris = $daftarMetode->firstWhere('jenis', 'QRIS');
        $daftarBank = $daftarMetode->where('jenis', 'Transfer Bank')
            ->values();
        $bank = null;

        if ($metode === 'Transfer Bank') {
            // idBank kosong: tampilDaftarBank() dengan bank pertama
            $bank = $idBank
                ? $daftarBank->firstWhere('idMetode', $idBank)
                : $daftarBank->first();
        }

        return compact('qris', 'daftarBank', 'bank');
    }

    // Validasi kelengkapan: metode, bank (transfer), dan bukti
    private function validasiForm(Request $request): bool
    {
        $this->galat = [];
        $metode = $request->input('metode');

        if (!in_array($metode, ['QRIS', 'Transfer Bank'], true)) {
            $this->galat['metode'] = 'Data wajib belum dilengkapi: '
                . 'pilih metode pembayaran.';
        }
        if ($metode === 'Transfer Bank' && !$request->filled('idBank')) {
            $this->galat['idBank'] = 'Data wajib belum dilengkapi: '
                . 'pilih bank tujuan.';
        }
        if (!$request->file('bukti')) {
            $this->galat['bukti'] = 'Data wajib belum dilengkapi: '
                . 'unggah bukti pembayaran.';
        }

        return $this->galat === [];
    }

    // Validasi berkas: tidak rusak, format sesuai, ukuran sesuai
    private function validasiBerkas(?UploadedFile $berkas): bool
    {
        if (!$berkas || !$berkas->isValid()) {
            return false;
        }

        $ekstensi = strtolower((string) $berkas->guessExtension());
        if (!in_array($ekstensi, $this->formatBerkas, true)) {
            return false;
        }

        return $berkas->getSize() <= $this->maksUkuranBerkas * 1024;
    }

    // Tagihan hanya boleh diakses penghuni pemiliknya
    private function tagihanMilik(object $penghuni, int $idTagihan): ?object
    {
        return DB::table('tagihansewa')
            ->where('idTagihan', $idTagihan)
            ->where('idPenghuni', $penghuni->idPenghuni)
            ->first();
    }

    // Menyiapkan label periode, nominal, jatuh tempo, dan status
    private function formatTagihan(object $tagihan): object
    {
        $periode = Carbon::createFromFormat('!Y-m', $tagihan->periode)
            ->locale('id');
        $tempo = Carbon::parse($tagihan->jatuhTempo)->locale('id');

        $tagihan->namaBulan = $periode->translatedFormat('F');
        $tagihan->periodeLabel = $periode->translatedFormat('F Y');
        $tagihan->jumlahRupiah = $this->rupiah($tagihan->jumlah);
        $tagihan->jatuhTempoLabel = $tempo->translatedFormat('d M Y');
        $tagihan->jatuhTempoPanjang = $tempo->translatedFormat('d F Y');
        $tagihan->statusKelas = match ($tagihan->status) {
            'Lunas' => 'badge-success',
            'Menunggu Verifikasi' => 'badge-warning',
            default => 'badge-danger',
        };

        return $tagihan;
    }

    private function rupiah(float|int|string $nilai): string
    {
        return 'Rp' . number_format((float) $nilai, 0, ',', '.');
    }

    // Mengambil penghuni aktif dari sesi login UC-02
    private function penghuniAktif(Request $request): ?object
    {
        $idPenghuni = $request->session()->get('idPenghuni');
        $idAkun = $request->session()->get('idAkun');
        if (!$idPenghuni && !$idAkun) {
            return null;
        }

        $query = DB::table('penghuni as p')
            ->join('kost as k', 'k.idKost', '=', 'p.idKost')
            ->join('kamar as km', 'km.idKamar', '=', 'p.idKamar')
            ->where('p.status', 'Aktif')
            ->select(
                'p.*',
                'k.idPemilik',
                'k.namaKost',
                'k.lokasi',
                'km.nomorKamar',
                'km.hargaSewa',
                'km.fotoKamar'
            );

        $idPenghuni
            ? $query->where('p.idPenghuni', $idPenghuni)
            : $query->where('p.idAkun', $idAkun);

        $penghuni = $query->first();
        if (!$penghuni) {
            return null;
        }

        $nama = trim(preg_replace('/^Kost\s+/i', '', $penghuni->namaKost));
        $penghuni->namaKostPendek = $nama;
        $penghuni->namaKostLengkap = 'Kost ' . $nama;
        $penghuni->hargaSewaRupiah = $this->rupiah($penghuni->hargaSewa);

        return $penghuni;
    }
}
