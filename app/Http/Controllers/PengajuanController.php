<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PengajuanController extends Controller
{
    // UC-11: daftar pengajuan Menunggu
    public function bukaDaftarPengajuan(Request $request)
    {
        $idPemilik = $this->idPemilikAktif($request);

        try {
            $daftar = PengajuanSewa::getPengajuanMenunggu($idPemilik);
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors([
                'pengajuan' => 'Data pengajuan tidak dapat dimuat, silakan coba kembali.',
            ]);
        }

        return view('verifikasi-pengajuan.daftar', array_merge(
            ['daftar' => $daftar],
            $this->dataLayout($idPemilik)
        ));
    }

    // UC-11: detail pengajuan termasuk berkas KTP
    public function bukaDetail(Request $request, int $idPengajuan)
    {
        $idPemilik = $this->idPemilikAktif($request);

        try {
            $detail = PengajuanSewa::getDetailPengajuan($idPengajuan, $idPemilik);
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors([
                'pengajuan' => 'Detail pengajuan tidak dapat dimuat, silakan coba kembali.',
            ]);
        }

        abort_unless($detail, 404);

        return view('verifikasi-pengajuan.detail', array_merge(
            ['detail' => $detail],
            $this->dataLayout($idPemilik)
        ));
    }

    // UC-11: setujui pengajuan, buat tagihan, tandai kamar Terisi
    public function setujuiPengajuan(Request $request, int $idPengajuan)
    {
        $idPemilik = $this->idPemilikAktif($request);

        try {
            $hasil = DB::transaction(function () use ($idPengajuan, $idPemilik) {
                $pengajuan = DB::table('pengajuansewa as ps')
                    ->join('kost as k', 'k.idKost', '=', 'ps.idKost')
                    ->where('ps.idPengajuan', $idPengajuan)
                    ->where('k.idPemilik', $idPemilik)
                    ->lockForUpdate()
                    ->first(['ps.*']);

                if (!$pengajuan) {
                    return 'tidak_ditemukan';
                }

                if ($pengajuan->status !== 'Menunggu') {
                    return 'sudah_diproses';
                }

                $kamar = DB::table('kamar')
                    ->where('idKamar', $pengajuan->idKamar)
                    ->lockForUpdate()
                    ->first();

                if (!$kamar) {
                    return 'kamar_tidak_ditemukan';
                }

                // PC-11: kamar sudah terisi, pengajuan tetap Menunggu
                if ($kamar->statusKamar === 'Terisi') {
                    return 'kamar_terisi';
                }

                DB::table('pengajuansewa')
                    ->where('idPengajuan', $idPengajuan)
                    ->update(['status' => 'Disetujui']);

                // idTagihan di schema tidak AUTO_INCREMENT, jadi dihitung manual
                $idTagihan = (int) DB::selectOne(
                    'SELECT COALESCE(MAX(`idTagihan`), 0) + 1 AS id FROM `tagihansewa` FOR UPDATE'
                )->id;

                DB::table('tagihansewa')->insert([
                    'idTagihan' => $idTagihan,
                    'idPengajuan' => $idPengajuan,
                    'idPenghuni' => null, // penghuni dibuat di UC lain
                    'idKamar' => $pengajuan->idKamar,
                    'periode' => now()->format('Y-m'),
                    'jumlah' => $kamar->hargaSewa,
                    'jatuhTempo' => $pengajuan->tanggalMulaiSewa,
                    'status' => 'Belum Lunas',
                ]);

                DB::table('kamar')
                    ->where('idKamar', $pengajuan->idKamar)
                    ->update(['statusKamar' => 'Terisi']);

                return 'ok';
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors([
                'pengajuan' => 'Pengajuan gagal disetujui, silakan coba kembali.',
            ]);
        }

        return match ($hasil) {
            'ok' => redirect()->route('verifikasi.daftar')
                ->with('pesan', 'Pengajuan berhasil disetujui.'),
            'kamar_terisi' => back()->withErrors([
                'pengajuan' => 'Kamar sudah terisi, pengajuan tidak dapat disetujui.',
            ]),
            'kamar_tidak_ditemukan' => back()->withErrors([
                'pengajuan' => 'Data kamar tidak ditemukan.',
            ]),
            'sudah_diproses' => back()->withErrors([
                'pengajuan' => 'Pengajuan ini sudah diproses sebelumnya.',
            ]),
            default => abort(404),
        };
    }

    // UC-11: tolak pengajuan dengan alasan wajib
    public function tolakPengajuan(Request $request, int $idPengajuan)
    {
        $idPemilik = $this->idPemilikAktif($request);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ], [
            'alasan.required' => 'Alasan harus diisi.',
        ]);

        $alasan = trim($data['alasan']);

        if ($alasan === '') {
            return back()->withErrors(['alasan' => 'Alasan harus diisi.']);
        }

        $pengajuan = PengajuanSewa::getDetailPengajuan($idPengajuan, $idPemilik);
        abort_unless($pengajuan, 404);

        if ($pengajuan->status !== 'Menunggu') {
            return back()->withErrors([
                'pengajuan' => 'Pengajuan ini sudah diproses sebelumnya.',
            ]);
        }

        if (!PengajuanSewa::updateStatus($idPengajuan, 'Ditolak', $alasan)) {
            return back()->withErrors([
                'pengajuan' => 'Pengajuan gagal ditolak, silakan coba kembali.',
            ]);
        }

        return redirect()->route('verifikasi.daftar')
            ->with('pesan', 'Pengajuan berhasil ditolak.');
    }

    // Mengambil id pemilik dari sesi login (UC-02)
    private function idPemilikAktif(Request $request): int
    {
        $idAkun = $request->session()->get('idAkun');
        abort_unless($idAkun, 403, 'Silakan masuk sebagai pemilik kost.');

        return (int) $idAkun;
    }

    // Data yang dibutuhkan layout ruang-pemilik (header, sidebar, menu aktif)
    private function dataLayout(int $idAkun): array
    {
        $pemilik = DB::table('akun')
            ->where('idAkun', $idAkun)
            ->first();

        abort_unless($pemilik, 403, 'Akun pemilik tidak ditemukan.');

        $namaKostHeader = DB::table('kost')
            ->where('idPemilik', $idAkun)
            ->value('namaKost') ?? 'Belum ada kost';

        return [
            'pemilik' => $pemilik,
            'namaKostHeader' => $namaKostHeader,
            'menuAktif' => 'pengajuan',
        ];
    }
}