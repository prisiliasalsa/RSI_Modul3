<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class KontrolPengajuanSewa extends Controller
{
    public function tampilFormAjukanSewa(Request $request, int $idKamar)
    {
        if (!$request->session()->has('idAkun')) {
            return redirect()->route('login');
        }

        abort_unless(
            $request->session()->get('jenisAkun') === 'Calon Penghuni',
            403
        );

        $kamar = $this->kamarTersedia($idKamar)->first();

        abort_unless($kamar, 404, 'Kamar tidak tersedia untuk diajukan.');

        return view('pengajuan.ajukan', compact('kamar'));
    }

    public function simpanPengajuan(Request $request, int $idKamar)
    {
        if (!$request->session()->has('idAkun')) {
            return redirect()->route('login');
        }

        abort_unless(
            $request->session()->get('jenisAkun') === 'Calon Penghuni',
            403
        );

        $data = $request->validate([
            'tanggalMulaiSewa' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'durasiSewa' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'jumlahPenghuni' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'berkasKTP' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $idAkun = (int) $request->session()->get('idAkun');
        $berkas = null;

        try {
            $berkas = $request->file('berkasKTP')
                ->store('pengajuan-sewa/ktp', 'local');

            if ($berkas === false) {
                throw new RuntimeException('Berkas KTP gagal disimpan.');
            }

            DB::transaction(function () use ($idAkun, $idKamar, $data, $berkas) {
                $kamar = $this->kamarTersedia($idKamar)
                    ->lockForUpdate()
                    ->first();

                if (!$kamar) {
                    throw ValidationException::withMessages([
                        'kamar' => 'Kamar sudah tidak tersedia.',
                    ]);
                }

                $masihMenunggu = DB::table('pengajuansewa')
                    ->where('idCalonPenghuni', $idAkun)
                    ->where('idKamar', $idKamar)
                    ->whereIn('status', ['Menunggu', 'Menunggu Verifikasi'])
                    ->exists();

                if ($masihMenunggu) {
                    throw ValidationException::withMessages([
                        'kamar' => 'Pengajuan untuk kamar ini masih menunggu verifikasi.',
                    ]);
                }

                $idPengajuan = (int) DB::table('pengajuansewa')
                    ->lockForUpdate()
                    ->max('idPengajuan') + 1;

                DB::table('pengajuansewa')->insert([
                    'idPengajuan' => $idPengajuan,
                    'idCalonPenghuni' => $idAkun,
                    'idKamar' => $kamar->idKamar,
                    'idKost' => $kamar->idKost,
                    'tanggalMulaiSewa' => $data['tanggalMulaiSewa'],
                    'durasiSewa' => $data['durasiSewa'],
                    'jumlahPenghuni' => $data['jumlahPenghuni'],
                    'catatan' => $data['catatan'] ?? null,
                    'berkasKTP' => $berkas,
                    'status' => 'Menunggu Verifikasi',
                    'tanggalPengajuan' => now()->toDateString(),
                ]);

                Notifikasi::catatPengajuan(
                    $idPengajuan,
                    'Ada pengajuan sewa baru untuk kamar ' . $kamar->nomorKamar . '.',
                    (int) $kamar->idPemilik
                );
            }, 3);
        } catch (Throwable $error) {
            if (is_string($berkas)) {
                try {
                    Storage::disk('local')->delete($berkas);
                } catch (Throwable $errorHapus) {
                    report($errorHapus);
                }
            }

            if ($error instanceof ValidationException) {
                throw $error;
            }

            report($error);

            return back()->withInput()->withErrors([
                'pengajuan' => 'Pengajuan gagal disimpan. Silakan coba lagi.',
            ]);
        }

        return redirect()->route('pengajuan.status')
            ->with('pesan', 'Pengajuan sewa berhasil dikirim.');
    }

    private function kamarTersedia(int $idKamar)
    {
        return DB::table('kamar as m')
            ->join('kost as k', 'k.idKost', '=', 'm.idKost')
            ->where('m.idKamar', $idKamar)
            ->where('m.statusKamar', 'Tersedia')
            ->where('k.status', 'Dipublikasikan')
            ->select('m.idKamar', 'm.idKost', 'm.nomorKamar', 'm.hargaSewa', 'k.namaKost', 'k.idPemilik');
    }
}