<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class KeluhanPemilikController extends Controller
{
    private function pemilikAktif(Request $request): object
    {
        $idAkun = $request->session()->get('idAkun');

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

    private function queryKeluhan(int $idPemilik)
    {
        return DB::table('keluhan as kl')
            ->join('kost as k', 'kl.idKost', '=', 'k.idKost')
            ->join('penghuni as p', 'kl.idPenghuni', '=', 'p.idPenghuni')
            ->leftJoin('kamar as km', 'p.idKamar', '=', 'km.idKamar')
            ->where('k.idPemilik', $idPemilik)
            ->select(
                'kl.*',
                'k.namaKost',
                'k.lokasi as lokasiKost',
                'k.alamat as alamatKost',
                'k.fotoUtama',
                'k.fotoKost',
                'p.namaLengkap',
                'km.nomorKamar',
                'km.fotoKamar'
            );
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

    private function namaKostHeader(int $idPemilik): string
    {
        $nama = DB::table('kost')
            ->where('idPemilik', $idPemilik)
            ->orderBy('idKost')
            ->value('namaKost');

        return $nama ?: 'Kost.io';
    }

    // PC-14: Menampilkan daftar keluhan pemilik
    public function mintaDaftarKeluhan(Request $request): View
    {
        $pemilik = $this->pemilikAktif($request);
        $cari = trim((string) $request->query('cari', ''));

        $query = $this->queryKeluhan(
            (int) $pemilik->idAkun
        );

        if ($cari !== '') {
            $query->where(function ($q) use ($cari) {
                $q->where('kl.deskripsi', 'like', "%{$cari}%")
                    ->orWhere('p.namaLengkap', 'like', "%{$cari}%")
                    ->orWhere('km.nomorKamar', 'like', "%{$cari}%")
                    ->orWhere('k.namaKost', 'like', "%{$cari}%");
            });
        }

        $laporan = $query
            ->orderByDesc('kl.tanggalPengajuan')
            ->orderByDesc('kl.idKeluhan')
            ->paginate(10)
            ->withQueryString();

        $laporan->getCollection()->transform(function ($item) {
            $detail = $this->uraikanDeskripsi($item->deskripsi);

            $item->judulKeluhan = $detail['judul'];
            $item->lokasiKeluhan = $detail['lokasi'];

            return $item;
        });

        $ringkasan = DB::table('keluhan as kl')
            ->join('kost as k', 'kl.idKost', '=', 'k.idKost')
            ->where('k.idPemilik', $pemilik->idAkun)
            ->selectRaw(
                "SUM(CASE WHEN kl.status = 'Diajukan'
                THEN 1 ELSE 0 END) AS diajukan"
            )
            ->selectRaw(
                "SUM(CASE WHEN kl.status = 'Diproses'
                THEN 1 ELSE 0 END) AS diproses"
            )
            ->first();

        $namaKostHeader = $this->namaKostHeader(
            (int) $pemilik->idAkun
        );

        return view('keluhan.pemilik.daftar', compact(
            'pemilik',
            'cari',
            'laporan',
            'ringkasan',
            'namaKostHeader'
        ));
    }

    // PC-14: Menampilkan detail keluhan
    public function mintaDetailKeluhan(
        Request $request,
        int $idKeluhan
    ): View {
        $pemilik = $this->pemilikAktif($request);

        $laporan = $this->queryKeluhan(
            (int) $pemilik->idAkun
        )
            ->where('kl.idKeluhan', $idKeluhan)
            ->first();

        abort_unless(
            $laporan,
            404,
            'Keluhan tidak ditemukan.'
        );

        $detail = $this->uraikanDeskripsi($laporan->deskripsi);
        $namaKostHeader = $laporan->namaKost;

        $info = json_decode(
            (string) $laporan->informasiTambahan,
            true
        );

        $info = is_array($info)
            && ($info['uc14'] ?? false) === true
            ? $info
            : null;

        return view('keluhan.pemilik.detail', compact(
            'pemilik',
            'laporan',
            'detail',
            'info',
            'namaKostHeader'
        ));
    }

    // PC-14: Meminta informasi tambahan
    public function mintaInformasiTambahan(
        Request $request,
        int $idKeluhan
    ): RedirectResponse {
        $pemilik = $this->pemilikAktif($request);

        $data = $request->validate([
            'pertanyaanTambahan' => [
                'required',
                'string',
                'max:500'
            ],
        ], [
            'pertanyaanTambahan.required' =>
                'Pertanyaan tambahan wajib diisi.',
            'pertanyaanTambahan.max' =>
                'Pertanyaan maksimal 500 karakter.',
        ]);

        $laporan = $this->queryKeluhan(
            (int) $pemilik->idAkun
        )
            ->where('kl.idKeluhan', $idKeluhan)
            ->first();

        abort_unless($laporan, 404);

        // Menggunakan kolom informasiTambahan yang sudah ada.
        $json = json_encode([
            'uc14' => true,
            'pertanyaan' => trim($data['pertanyaanTambahan']),
            'jawaban' => null,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (mb_strlen($json, 'UTF-8') > 2000) {
            return back()->withErrors([
                'pertanyaanTambahan' =>
                    'Permintaan terlalu panjang untuk disimpan.'
            ])->withInput();
        }

        try {
            DB::table('keluhan')
                ->where('idKeluhan', $idKeluhan)
                ->where('idKost', $laporan->idKost)
                ->update([
                    'informasiTambahan' => $json
                ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'pertanyaanTambahan' =>
                    'Permintaan informasi gagal disimpan.'
            ])->withInput();
        }

        return redirect()
            ->route('pemilik.keluhan.detail', $idKeluhan)
            ->with(
                'success',
                'Permintaan informasi berhasil dikirim.'
            );
    }

    // PC-14: Memperbarui status penanganan
    public function perbaruiStatusKeluhan(
        Request $request,
        int $idKeluhan
    ): RedirectResponse {
        $pemilik = $this->pemilikAktif($request);

        $data = $request->validate([
            'status' => [
                'required',
                'in:Diproses,Selesai'
            ],
        ]);

        $laporan = $this->queryKeluhan(
            (int) $pemilik->idAkun
        )
            ->where('kl.idKeluhan', $idKeluhan)
            ->first();

        abort_unless($laporan, 404);

        try {
            DB::table('keluhan')
                ->where('idKeluhan', $idKeluhan)
                ->where('idKost', $laporan->idKost)
                ->update([
                    'status' => $data['status']
                ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'status' =>
                    'Status keluhan gagal diperbarui.'
            ]);
        }

        return redirect()
            ->route('pemilik.keluhan.detail', [
                'idKeluhan' => $idKeluhan,
                'berhasil' => 1,
            ])
            ->with(
                'uc14_status_berhasil',
                $data['status']
            );
    }

    // Menampilkan pratinjau status dari sisi penghuni
    public function pratinjauPenghuni(
        Request $request,
        int $idKeluhan
    ): View {
        $pemilik = $this->pemilikAktif($request);

        $laporan = $this->queryKeluhan(
            (int) $pemilik->idAkun
        )
            ->where('kl.idKeluhan', $idKeluhan)
            ->first();

        abort_unless($laporan, 404);

        $penghuni = DB::table('penghuni as p')
            ->leftJoin('kost as k', 'p.idKost', '=', 'k.idKost')
            ->leftJoin('kamar as km', 'p.idKamar', '=', 'km.idKamar')
            ->where('p.idPenghuni', $laporan->idPenghuni)
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
            ->first();

        abort_unless($penghuni, 404);

        $detail = $this->uraikanDeskripsi($laporan->deskripsi);

        $info = json_decode(
            (string) $laporan->informasiTambahan,
            true
        );

        $info = is_array($info)
            && ($info['uc14'] ?? false) === true
            ? $info
            : null;

        $pratinjau = true;

        return view(
            'keluhan.pemilik.tanggapan-penghuni',
            compact(
                'laporan',
                'penghuni',
                'detail',
                'info',
                'pratinjau'
            )
        );
    }

    // Menampilkan foto bukti keluhan
    public function lihatFoto(
        Request $request,
        int $idKeluhan
    ) {
        $pemilik = $this->pemilikAktif($request);

        $laporan = $this->queryKeluhan(
            (int) $pemilik->idAkun
        )
            ->where('kl.idKeluhan', $idKeluhan)
            ->first();

        abort_unless($laporan && $laporan->foto, 404);

        abort_unless(
            Storage::disk('local')->exists($laporan->foto),
            404
        );

        return Storage::disk('local')
            ->response($laporan->foto);
    }
}
