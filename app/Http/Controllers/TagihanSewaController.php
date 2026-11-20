<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class TagihanSewaController extends Controller
{
    public function index(Request $request)
    {
        $idAkun = (int) $request->session()->get('idAkun');

        if ($idAkun < 1) {
            return redirect()->route('login');
        }

        $this->pastikanPeranPenghuni($request);

        $tagihan = $this->tagihanMilikAkun($idAkun)
            ->select('t.*', 'k.namaKost', 'm.nomorKamar', 'k.idPemilik')
            ->orderByDesc('t.jatuhTempo')
            ->orderByDesc('t.idTagihan')
            ->get();

        $metodePerPemilik = DB::table('metodepembayaran')
            ->whereIn('idPemilikKost', $tagihan->pluck('idPemilik')->unique()->values())
            ->get([
                'idMetode', 'idPemilikKost', 'jenis',
                'namaBank', 'noRekening', 'atasNama',
            ])
            ->groupBy('idPemilikKost');

        $metodePerTagihan = $tagihan->mapWithKeys(fn ($item) => [
            $item->idTagihan => $metodePerPemilik->get($item->idPemilik, collect()),
        ]);

        return view('pengajuan.tagihan-sewa', compact('tagihan', 'metodePerTagihan'));
    }

    public function bayar(Request $request, int $idTagihan)
    {
        $idAkun = (int) $request->session()->get('idAkun');

        if ($idAkun < 1) {
            return redirect()->route('login');
        }

        $this->pastikanPeranPenghuni($request);

        $data = $request->validate([
            'idMetode' => ['required', 'integer', 'min:1'],
            'buktiBayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $berkas = null;

        try {
            $berkas = $request->file('buktiBayar')->store('pembayaran/bukti', 'local');

            if ($berkas === false) {
                return back()->withErrors([
                    'buktiBayar' => 'Bukti pembayaran gagal disimpan. Coba unggah lagi.',
                ]);
            }

            DB::transaction(function () use ($idTagihan, $idAkun, $data, $berkas) {
                $tagihan = $this->tagihanMilikAkun($idAkun)
                    ->where('t.idTagihan', $idTagihan)
                    ->select('t.idPenghuni', 't.idKamar', 't.jumlah', 't.status',
                        'p.idKost', 'k.idPemilik')
                    ->lockForUpdate()
                    ->first();

                abort_unless($tagihan, 404);

                if (!in_array($tagihan->status, ['Belum Dibayar', 'Belum Lunas', 'Ditolak'], true)) {
                    throw ValidationException::withMessages([
                        'buktiBayar' => 'Tagihan ini sedang diverifikasi atau sudah lunas.',
                    ]);
                }

                $penghuniCocok = DB::table('penghuni')
                    ->where('idPenghuni', $tagihan->idPenghuni)
                    ->where('idAkun', $idAkun)
                    ->where('idKost', $tagihan->idKost)
                    ->where('idKamar', $tagihan->idKamar)
                    ->exists();

                if (!$penghuniCocok || $tagihan->jumlah === null) {
                    throw ValidationException::withMessages([
                        'buktiBayar' => 'Data penghuni atau nominal tagihan belum lengkap.',
                    ]);
                }

                $metode = DB::table('metodepembayaran')
                    ->where('idMetode', $data['idMetode'])
                    ->where('idPemilikKost', $tagihan->idPemilik)
                    ->first(['idMetode', 'jenis']);

                if (!$metode) {
                    throw ValidationException::withMessages([
                        'idMetode' => 'Pilih metode pembayaran yang disediakan pemilik kost.',
                    ]);
                }

                $idPembayaran = (int) DB::table('pembayaran')
                    ->lockForUpdate()
                    ->max('idPembayaran') + 1;

                DB::table('pembayaran')->insert([
                    'idPembayaran' => $idPembayaran,
                    'idTagihan' => $idTagihan,
                    'idPenghuni' => $tagihan->idPenghuni,
                    'idMetode' => $metode->idMetode,
                    'metode' => $metode->jenis,
                    'jumlahPembayaran' => $tagihan->jumlah,
                    'buktiBayar' => $berkas,
                    'tanggalPembayaran' => now(),
                    'statusPembayaran' => 'Menunggu Verifikasi',
                    'alasanPenolakan' => null,
                ]);

                DB::table('tagihansewa')
                    ->where('idTagihan', $idTagihan)
                    ->update(['status' => 'Menunggu Verifikasi']);
            }, 3);
        } catch (Throwable $error) {
            if (is_string($berkas)) {
                try {
                    Storage::disk('local')->delete($berkas);
                } catch (Throwable $errorHapus) {
                    report($errorHapus);
                }
            }

            if ($error instanceof ValidationException || $error instanceof HttpExceptionInterface) {
                throw $error;
            }

            report($error);

            return back()->withErrors([
                'buktiBayar' => 'Bukti pembayaran gagal dikirim. Silakan coba lagi.',
            ]);
        }

        return redirect()->route('tagihan-sewa.index')
            ->with('pesan', 'Bukti pembayaran terkirim dan menunggu verifikasi pemilik.');
    }

    private function tagihanMilikAkun(int $idAkun): Builder
    {
        return DB::table('tagihansewa as t')
            ->join('pengajuansewa as p', 'p.idPengajuan', '=', 't.idPengajuan')
            ->join('kost as k', 'k.idKost', '=', 'p.idKost')
            ->join('kamar as m', function ($join) {
                $join->on('m.idKamar', '=', 't.idKamar')
                    ->on('m.idKamar', '=', 'p.idKamar')
                    ->on('m.idKost', '=', 'k.idKost');
            })
            ->where('p.idCalonPenghuni', $idAkun)
            ->where('p.status', 'Disetujui');
    }

    private function pastikanPeranPenghuni(Request $request): void
    {
        abort_unless(
            in_array($request->session()->get('jenisAkun'), ['Calon Penghuni', 'Penghuni'], true),
            403
        );
    }
}