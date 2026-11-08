<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResponKeluhanController extends Controller
{
    private function penghuniAktif(Request $request): object
    {
        $idPenghuni = $request->session()->get('idPenghuni');
        $idAkun = $request->session()->get('idAkun');

        abort_unless(
            $idPenghuni || $idAkun,
            403,
            'Silakan login sebagai penghuni.'
        );

        $penghuni = DB::table('penghuni as p')
            ->leftJoin('kost as k', 'p.idKost', '=', 'k.idKost')
            ->leftJoin('kamar as km', 'p.idKamar', '=', 'km.idKamar')
            ->select(
                'p.*',
                'k.namaKost',
                'k.alamat',
                'k.lokasi',
                'k.fotoUtama',
                'k.fotoKost',
                'km.nomorKamar',
                'km.fotoKamar'
            )
            ->when(
                $idPenghuni,
                fn ($q) => $q->where('p.idPenghuni', $idPenghuni),
                fn ($q) => $q->where('p.idAkun', $idAkun)
            )
            ->where('p.status', 'Aktif')
            ->first();

        abort_unless(
            $penghuni,
            403,
            'Tidak ada sewa penghuni aktif.'
        );

        return $penghuni;
    }

    private function uraikanDeskripsi(?string $teks): array
    {
        $teks = (string) $teks;

        $pola = '/^Judul: ([^\r\n]*)\r?\n'
            . 'Lokasi: ([^\r\n]*)\r?\n'
            . 'Deskripsi:\r?\n(.*)$/s';

        if (preg_match($pola, $teks, $m)) {
            return [
                'judul' => $m[1],
                'lokasi' => $m[2],
                'isi' => $m[3],
            ];
        }

        return [
            'judul' => 'Keluhan / kerusakan',
            'lokasi' => '-',
            'isi' => $teks,
        ];
    }

    public function tampil(
        Request $request,
        int $idKeluhan
    ): View {
        $penghuni = $this->penghuniAktif($request);

        $laporan = DB::table('keluhan')
            ->where('idKeluhan', $idKeluhan)
            ->where('idPenghuni', $penghuni->idPenghuni)
            ->first();

        abort_unless($laporan, 404);

        $detail = $this->uraikanDeskripsi($laporan->deskripsi);

        $info = json_decode(
            (string) $laporan->informasiTambahan,
            true
        );

        $info = is_array($info)
            && ($info['uc14'] ?? false) === true
            ? $info
            : null;

        $pratinjau = false;

        return view(
            'keluhan.pemilik.tanggapan-penghuni',
            compact(
                'penghuni',
                'laporan',
                'detail',
                'info',
                'pratinjau'
            )
        );
    }

    public function kirim(
        Request $request,
        int $idKeluhan
    ): RedirectResponse {
        $penghuni = $this->penghuniAktif($request);

        $data = $request->validate([
            'jawaban' => [
                'required',
                'string',
                'max:1000'
            ],
        ], [
            'jawaban.required' =>
                'Informasi tambahan wajib diisi.',
            'jawaban.max' =>
                'Jawaban maksimal 1.000 karakter.',
        ]);

        $berhasil = DB::transaction(function () use (
            $penghuni,
            $idKeluhan,
            $data
        ) {
            $laporan = DB::table('keluhan')
                ->where('idKeluhan', $idKeluhan)
                ->where('idPenghuni', $penghuni->idPenghuni)
                ->lockForUpdate()
                ->first();

            abort_unless($laporan, 404);

            $info = json_decode(
                (string) $laporan->informasiTambahan,
                true
            );

            if (
                !is_array($info)
                || ($info['uc14'] ?? false) !== true
                || blank($info['pertanyaan'] ?? null)
                || filled($info['jawaban'] ?? null)
            ) {
                return false;
            }

            $info['jawaban'] = trim($data['jawaban']);

            $json = json_encode(
                $info,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );

            if (mb_strlen($json, 'UTF-8') > 2000) {
                return false;
            }

            return DB::table('keluhan')
                ->where('idKeluhan', $idKeluhan)
                ->where('idPenghuni', $penghuni->idPenghuni)
                ->update([
                    'informasiTambahan' => $json
                ]) === 1;
        });

        if (!$berhasil) {
            return back()->withErrors([
                'jawaban' =>
                    'Permintaan tidak tersedia, sudah dijawab, atau jawaban terlalu panjang.',
            ])->withInput();
        }

        return redirect()
            ->route('keluhan.tanggapan', $idKeluhan)
            ->with(
                'success',
                'Informasi berhasil dikirim.'
            );
    }
}
