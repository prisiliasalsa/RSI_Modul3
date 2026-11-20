<?php

namespace App\Http\Controllers;

use App\Models\DataKeuangan;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\TagihanSewa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * KontrolVerifikasiPembayaran (CD-13)
 * UC-13 Memverifikasi Pembayaran
 */
class KontrolVerifikasiPembayaran extends Controller
{
    private int $maksAlasan = 1000;

    // Menampilkan daftar pembayaran yang menunggu verifikasi
    public function lihatDaftarPembayaran(Request $request): View
    {
        $pemilik = $this->pemilikAktif($request);
        $cari = trim((string) $request->query('cari', ''));
        $gangguan = false;
        $jumlahMenunggu = 0;
        $totalNominal = 0;

        try {
            $query = Pembayaran::getMenungguVerifikasi($pemilik->idAkun);

            $jumlahMenunggu = (clone $query)->count();
            $totalNominal = (float) (clone $query)
                ->sum('pb.jumlahPembayaran');

            $daftar = Pembayaran::getMenungguVerifikasi(
                $pemilik->idAkun,
                $cari
            )
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($baris) => $this->formatPembayaran($baris));

        } catch (Throwable $error) {
            report($error);
            $gangguan = true;
            $daftar = new LengthAwarePaginator([], 0, 10);
        }

        return view('verifikasi-pembayaran.daftar', [
            'pemilik' => $pemilik,
            'namaKostHeader' => $this->namaKostHeader($pemilik->idAkun),
            'daftar' => $daftar,
            'cari' => $cari,
            'jumlahMenunggu' => $jumlahMenunggu,
            'totalNominal' => $this->rupiah($totalNominal),
            'gangguan' => $gangguan,
        ]);
    }

    // Menampilkan detail pembayaran dan bukti transfer
    public function lihatDetailPembayaran(
        Request $request,
        int $idPembayaran
    ): View|RedirectResponse {

        $pemilik = $this->pemilikAktif($request);

        try {
            $pembayaran = Pembayaran::getDetail(
                $idPembayaran,
                $pemilik->idAkun
            );

            $tagihan = $pembayaran
                ? TagihanSewa::getTagihan($pembayaran->idTagihan)
                : null;

        } catch (Throwable $error) {
            report($error);

            return redirect()
                ->route('pemilik.verifikasi.daftar')
                ->with('pesanGagal', 'Terjadi gangguan data');
        }

        abort_unless(
            $pembayaran && $tagihan,
            404,
            'Pembayaran tidak ditemukan.'
        );

        $aksi = $request->query('aksi');

        $bisaDiverifikasi = $pembayaran->statusPembayaran
            === Pembayaran::STATUS_MENUNGGU;

        if (
            !$bisaDiverifikasi ||
            !in_array($aksi, ['terima', 'tolak'], true)
        ) {
            $aksi = null;
        }

        return view('verifikasi-pembayaran.detail', [
            'pemilik' => $pemilik,
            'namaKostHeader' => $pembayaran->namaKost,
            'pembayaran' => $this->formatPembayaran($pembayaran),
            'tagihan' => $tagihan,
            'aksi' => $aksi,
            'bisaDiverifikasi' => $bisaDiverifikasi,
            'buktiTersedia' => $pembayaran->buktiBayar
                && Storage::disk('local')->exists(
                    $pembayaran->buktiBayar
                ),
        ]);
    }

    // Menerima pembayaran dan mengaktifkan penghuni
    public function terimaPembayaran(
        Request $request,
        int $idPembayaran
    ): RedirectResponse {

        $pemilik = $this->pemilikAktif($request);

        $pembayaran = Pembayaran::getDetail(
            $idPembayaran,
            $pemilik->idAkun
        );

        abort_unless(
            $pembayaran,
            404,
            'Pembayaran tidak ditemukan.'
        );

        $periode = $this->labelPeriode($pembayaran->periode);

        try {
            DB::transaction(function () use ($pembayaran, $periode) {

                $id = (int) $pembayaran->idPembayaran;

                // Mengambil tagihan terkait pembayaran
                $tagihan = DB::table('tagihansewa')
                    ->where('idTagihan', $pembayaran->idTagihan)
                    ->lockForUpdate()
                    ->first(['idPenghuni', 'idKamar']);

                if (
                    !$tagihan ||
                    (int) $tagihan->idPenghuni !==
                    (int) $pembayaran->idPenghuni
                ) {
                    throw new RuntimeException(
                        'Tagihan dan data penghuni pembayaran tidak cocok.'
                    );
                }

                // Memeriksa kamar yang disewa
                $kamar = DB::table('kamar')
                    ->where('idKamar', $tagihan->idKamar)
                    ->where('idKost', $pembayaran->idKost)
                    ->lockForUpdate()
                    ->first([
                        'idKamar',
                        'idKost',
                        'statusKamar'
                    ]);

                if (!$kamar) {
                    throw new RuntimeException(
                        'Kamar pada tagihan tidak ditemukan.'
                    );
                }

                // Mengambil data penghuni
                $penghuni = DB::table('penghuni')
                    ->where('idPenghuni', $tagihan->idPenghuni)
                    ->where('idKamar', $kamar->idKamar)
                    ->where('idKost', $kamar->idKost)
                    ->lockForUpdate()
                    ->first([
                        'idPenghuni',
                        'status'
                    ]);

                if (!$penghuni) {
                    throw new RuntimeException(
                        'Data penghuni tidak cocok dengan kamar pada tagihan.'
                    );
                }

                // Mengaktifkan penghuni yang menunggu pembayaran
                if ($penghuni->status === 'Menunggu Pembayaran') {

                    $penghuniAktifLain = DB::table('penghuni')
                        ->where('idKamar', $kamar->idKamar)
                        ->where(
                            'idPenghuni',
                            '!=',
                            $penghuni->idPenghuni
                        )
                        ->where('status', 'Aktif')
                        ->exists();

                    if (
                        !in_array($kamar->statusKamar, ['Tersedia', 'Terisi'], true) ||
                        $penghuniAktifLain
                    ) {
                        throw new RuntimeException(
                            'Kamar sudah tidak tersedia untuk aktivasi hunian.'
                        );
                    }

                    // Mengubah status penghuni menjadi aktif
                    DB::table('penghuni')
                        ->where('idPenghuni', $penghuni->idPenghuni)
                        ->update([
                            'status' => 'Aktif'
                        ]);

                    // Menangani pengajuan lama yang disetujui saat kamar masih Tersedia
                    DB::table('kamar')
                        ->where('idKamar', $kamar->idKamar)
                        ->update([
                            'statusKamar' => 'Terisi'
                        ]);
                }

                // Memverifikasi status pembayaran
                if (
                    !Pembayaran::ubahStatus(
                        $id,
                        Pembayaran::STATUS_LUNAS,
                        null
                    )
                ) {
                    throw new RuntimeException(
                        'Pembayaran sudah diverifikasi.'
                    );
                }

                // Memperbarui status tagihan
                TagihanSewa::ubahStatus(
                    (int) $pembayaran->idTagihan,
                    TagihanSewa::STATUS_LUNAS
                );

                // Mencatat transaksi keuangan
                DataKeuangan::catatTransaksi($id);

                // Mengirim notifikasi kepada penghuni
                Notifikasi::kirimNotif(
                    $id,
                    "Pembayaran tagihan {$periode} telah diverifikasi dan Lunas."
                );
            });

        } catch (Throwable $error) {
            report($error);

            return back()->with(
                'pesanGagal',
                'Pembayaran gagal diverifikasi, silakan coba kembali'
            );
        }

        return redirect()
            ->route('pemilik.verifikasi.daftar')
            ->with(
                'pesanBerhasil',
                'Pembayaran berhasil diverifikasi'
            );
    }

    // Memastikan alasan penolakan tidak kosong
    public function validasiAlasan(?string $alasan): bool
    {
        return trim((string) $alasan) !== '';
    }

    // Menolak pembayaran dengan alasan
    public function tolakPembayaran(
        Request $request,
        int $idPembayaran
    ): RedirectResponse {

        $pemilik = $this->pemilikAktif($request);

        $pembayaran = Pembayaran::getDetail(
            $idPembayaran,
            $pemilik->idAkun
        );

        abort_unless(
            $pembayaran,
            404,
            'Pembayaran tidak ditemukan.'
        );

        $alasan = trim(
            (string) $request->input('alasanPenolakan')
        );

        if (!$this->validasiAlasan($alasan)) {
            return back()->withErrors([
                'alasanPenolakan' => 'Alasan harus diisi'
            ]);
        }

        if (mb_strlen($alasan) > $this->maksAlasan) {
            return back()
                ->withInput()
                ->withErrors([
                    'alasanPenolakan' => 'Alasan maksimal 1000 karakter'
                ]);
        }

        $periode = $this->labelPeriode($pembayaran->periode);

        try {
            DB::transaction(function () use (
                $pembayaran,
                $alasan,
                $periode
            ) {

                $id = (int) $pembayaran->idPembayaran;

                // Mengubah status pembayaran menjadi ditolak
                if (
                    !Pembayaran::ubahStatus(
                        $id,
                        Pembayaran::STATUS_DITOLAK,
                        $alasan
                    )
                ) {
                    throw new RuntimeException(
                        'Pembayaran sudah diverifikasi.'
                    );
                }

                // Mengembalikan tagihan menjadi belum lunas
                TagihanSewa::ubahStatus(
                    (int) $pembayaran->idTagihan,
                    TagihanSewa::STATUS_BELUM_LUNAS
                );

                // Memberikan notifikasi penolakan
                Notifikasi::kirimNotif(
                    $id,
                    "Pembayaran tagihan {$periode} ditolak. Alasan: {$alasan}"
                );
            });

        } catch (Throwable $error) {
            report($error);

            return back()
                ->withInput()
                ->with(
                    'pesanGagal',
                    'Pembayaran gagal ditolak, silakan coba kembali'
                );
        }

        return redirect()
            ->route('pemilik.verifikasi.daftar')
            ->with(
                'pesanBerhasil',
                'Pembayaran ditolak dan notifikasi dikirim ke Penghuni'
            );
    }

    // Menampilkan bukti pembayaran
    public function lihatBukti(
        Request $request,
        int $idPembayaran
    ): Response {

        $pemilik = $this->pemilikAktif($request);

        $pembayaran = Pembayaran::getDetail(
            $idPembayaran,
            $pemilik->idAkun
        );

        abort_unless(
            $pembayaran
                && $pembayaran->buktiBayar
                && Storage::disk('local')->exists(
                    $pembayaran->buktiBayar
                ),
            404,
            'Bukti pembayaran tidak ditemukan.'
        );

        return Storage::disk('local')
            ->response($pembayaran->buktiBayar);
    }

    // Menyiapkan format tampilan pembayaran
    private function formatPembayaran(object $baris): object
    {
        $baris->periodeLabel = $this->labelPeriode(
            $baris->periode
        );

        $baris->periodeSingkat = $baris->periode
            ? Carbon::createFromFormat('!Y-m', $baris->periode)
                ->locale('id')
                ->translatedFormat('M Y')
            : '-';

        $baris->nominalRupiah = $this->rupiah(
            $baris->jumlahPembayaran
        );

        $baris->tagihanRupiah = $this->rupiah(
            $baris->jumlahTagihan
        );

        $baris->hargaSewaRupiah = $this->rupiah(
            $baris->hargaSewa
        );

        $baris->nominalSesuai =
            (float) $baris->jumlahPembayaran
            === (float) $baris->jumlahTagihan;

        $baris->tanggalLabel = $baris->tanggalPembayaran
            ? Carbon::parse($baris->tanggalPembayaran)
                ->locale('id')
                ->translatedFormat('d F Y')
            : '-';

        $baris->metodeLabel =
            $baris->metode === 'Transfer Bank'
            && $baris->namaBank
                ? 'transfer ' . $baris->namaBank
                : ($baris->metode ?: '-');

        $ekstensi = strtolower(
            pathinfo(
                (string) $baris->buktiBayar,
                PATHINFO_EXTENSION
            )
        );

        $baris->buktiAdalahPdf = $ekstensi === 'pdf';

        $baris->statusKelas = match (
            $baris->statusPembayaran
        ) {
            Pembayaran::STATUS_LUNAS => 'status-lunas',
            Pembayaran::STATUS_DITOLAK => 'status-ditolak',
            default => 'status-menunggu',
        };

        return $baris;
    }

    // Format periode pembayaran
    private function labelPeriode(?string $periode): string
    {
        if (!$periode) {
            return '-';
        }

        return Carbon::createFromFormat('!Y-m', $periode)
            ->locale('id')
            ->translatedFormat('F Y');
    }

    // Format nominal menjadi rupiah
    private function rupiah(
        float|int|string|null $nilai
    ): string {
        return 'Rp' . number_format(
            (float) $nilai,
            0,
            ',',
            '.'
        );
    }

    // Mengambil nama kost milik pemilik
    private function namaKostHeader(int $idPemilik): string
    {
        return DB::table('kost')
            ->where('idPemilik', $idPemilik)
            ->orderBy('idKost')
            ->value('namaKost') ?: 'Kost.io';
    }

    // Memastikan akun yang login adalah pemilik kost
    private function pemilikAktif(Request $request): object
    {
        $idAkun = $request->session()->get('idAkun')
            ?? Auth::id();

        abort_unless(
            $idAkun,
            403,
            'Silakan login sebagai pemilik kost.'
        );

        $pemilik = DB::table('akun')
            ->where('idAkun', $idAkun)
            ->where('jenisAkun', 'Pemilik Kost')
            ->first();

        abort_unless(
            $pemilik,
            403,
            'Akses khusus pemilik kost.'
        );

        return $pemilik;
    }
}
