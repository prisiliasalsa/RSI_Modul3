<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VerifikasiPengajuanSewaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('idAkun')) {
            return redirect()->route('login');
        }

        $idPemilik = $this->idPemilik($request);

        $pengajuan = DB::table('pengajuansewa as p')
            ->join('kost as k', 'k.idKost', '=', 'p.idKost')
            ->join('kamar as m', function ($join) {
                $join->on('m.idKamar', '=', 'p.idKamar')
                    ->on('m.idKost', '=', 'p.idKost');
            })
            ->join('akun as a', 'a.idAkun', '=', 'p.idCalonPenghuni')
            ->where('k.idPemilik', $idPemilik)
            ->select('p.*', 'k.namaKost', 'm.nomorKamar', 'a.namaLengkap')
            ->orderByDesc('p.idPengajuan')
            ->paginate(10);

        $pemilik = DB::table('akun')
    ->where('idAkun', $idPemilik)
    ->first(['namaLengkap']);

abort_unless($pemilik, 404);

return view('pengajuan.verifikasi-pemilik', [
    'pengajuan' => $pengajuan,
    'pemilik' => $pemilik,
    'menuAktif' => 'pengajuan',
    'namaKostHeader' => 'Kost.io',
]);
    }
    
   public function putuskan(Request $request, int $idPengajuan)
{
    if (!$request->session()->has('idAkun')) {
        return redirect()->route('login');
    }

    $idPemilik = $this->idPemilik($request);

    $data = $request->validate([
        'keputusan' => ['required', Rule::in(['Disetujui', 'Ditolak'])],
        'alasanPenolakan' => [
            'nullable', 'required_if:keputusan,Ditolak', 'string', 'max:1000',
        ],
    ]);

    $diproses = DB::transaction(function () use ($idPengajuan, $idPemilik, $data) {
        $pengajuan = DB::table('pengajuansewa as p')
            ->join('kost as k', 'k.idKost', '=', 'p.idKost')
            ->join('kamar as m', function ($join) {
                $join->on('m.idKamar', '=', 'p.idKamar')
                    ->on('m.idKost', '=', 'p.idKost');
            })
            ->where('p.idPengajuan', $idPengajuan)
            ->where('k.idPemilik', $idPemilik)
            ->select(
                'p.idCalonPenghuni',
                'p.idKost',
                'p.idKamar',
                'p.tanggalMulaiSewa',
                'p.durasiSewa',
                'p.status',
                'm.hargaSewa',
                'm.statusKamar'
            )
            ->lockForUpdate()
            ->first();

        abort_unless($pengajuan, 404);

        if (!in_array($pengajuan->status, ['Menunggu', 'Menunggu Verifikasi'], true)) {
            return false;
        }

        $idPenghuni = null;

        if ($data['keputusan'] === 'Disetujui') {
            if (!$pengajuan->tanggalMulaiSewa || $pengajuan->hargaSewa === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'keputusan' => 'Tanggal mulai sewa dan harga kamar harus tersedia untuk membuat tagihan.',
                ]);
            }

            $tanggalMulai = \Illuminate\Support\Carbon::parse($pengajuan->tanggalMulaiSewa);
            $periode = $tanggalMulai->format('Y-m');

            $tagihan = DB::table('tagihansewa')
                ->where('idPengajuan', $idPengajuan)
                ->where('periode', $periode)
                ->first(['idPenghuni']);

            $adaSewaLain = DB::table('penghuni')
                ->where('idKamar', $pengajuan->idKamar)
                ->where('idKost', $pengajuan->idKost)
                ->whereIn('status', ['Aktif', 'Menunggu Pembayaran'])
                ->when($tagihan?->idPenghuni, fn ($query, $id) =>
                    $query->where('idPenghuni', '!=', $id)
                )
                ->exists();

            if ($pengajuan->statusKamar !== 'Tersedia' || $adaSewaLain) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'keputusan' => 'Kamar sudah tidak tersedia untuk pengajuan ini.',
                ]);
            }

            if (!$tagihan) {
                $akun = DB::table('akun')
                    ->where('idAkun', $pengajuan->idCalonPenghuni)
                    ->first(['namaLengkap', 'noTelepon']);

                if (!$akun || (int) $pengajuan->durasiSewa < 1) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'keputusan' => 'Data calon penghuni atau durasi sewa belum lengkap.',
                    ]);
                }

                $idPenghuni = (int) DB::table('penghuni')
                    ->lockForUpdate()
                    ->max('idPenghuni') + 1;

                DB::table('penghuni')->insert([
                    'idPenghuni' => $idPenghuni,
                    'idAkun' => $pengajuan->idCalonPenghuni,
                    'idKost' => $pengajuan->idKost,
                    'idKamar' => $pengajuan->idKamar,
                    'namaLengkap' => $akun->namaLengkap,
                    'noTelepon' => $akun->noTelepon,
                    'tanggalMulaiSewa' => $tanggalMulai->toDateString(),
                    'tanggalAkhirSewa' => $tanggalMulai->copy()
                        ->addMonthsNoOverflow((int) $pengajuan->durasiSewa)
                        ->toDateString(),
                    'status' => 'Menunggu Pembayaran',
                ]);
                $idTagihan = (int) DB::table('tagihansewa')
                    ->lockForUpdate()
                    ->max('idTagihan') + 1;

                DB::table('tagihansewa')->insert([
                    'idTagihan' => $idTagihan,
                    'idPengajuan' => $idPengajuan,
                    'idPenghuni' => $idPenghuni,
                    'idKamar' => $pengajuan->idKamar,
                    'periode' => $periode,
                    'jumlah' => $pengajuan->hargaSewa,
                    'jatuhTempo' => $tanggalMulai->toDateString(),
                    'status' => 'Belum Dibayar',
                ]);
            } else {
                $idPenghuni = $tagihan->idPenghuni;
            }

            DB::table('kamar')
                ->where('idKost', $pengajuan->idKost)
                ->where('idKamar', $pengajuan->idKamar)
                ->update(['statusKamar' => 'Terisi']);
        }

        DB::table('pengajuansewa')
            ->where('idPengajuan', $idPengajuan)
            ->update([
                'status' => $data['keputusan'],
                'alasanPenolakan' => $data['keputusan'] === 'Ditolak'
                    ? $data['alasanPenolakan']
                    : null,
            ]);

        Notifikasi::catatPengajuan(
            $idPengajuan,
            $data['keputusan'] === 'Disetujui'
                ? 'Pengajuan sewa disetujui. Tagihan sewa sudah tersedia.'
                : 'Pengajuan sewa ditolak. Alasan: ' . $data['alasanPenolakan'],
            idPenghuni: $idPenghuni
        );

        return true;
    }, 3);

    return redirect()->route('pemilik.pengajuan-sewa.index')->with(
        $diproses ? 'pesan' : 'error',
        $diproses ? 'Keputusan berhasil disimpan.' : 'Pengajuan ini sudah diproses.'
    );
}

    private function idPemilik(Request $request): int
    {
        abort_unless($request->session()->get('jenisAkun') === 'Pemilik Kost', 403);

        return (int) $request->session()->get('idAkun');
    }
}