<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class KeluhanController extends Controller
{
    // UC-08: Menampilkan halaman pengajuan
    public function mintaFormPengajuan(Request $request): View
    {
        return view('keluhan.pengajuan', [
            'penghuni' => $this->penghuniAktif($request),
            'berhasil' => $request->session()->get('keluhan_berhasil'),
        ]);
    }

    // UC-08: Mengirim dan menyimpan keluhan
    public function ajukanKeluhan(Request $request): RedirectResponse
    {
        $penghuni = $this->penghuniAktif($request);

        abort_unless(
            $penghuni,
            403,
            'Silakan masuk sebagai penghuni dengan sewa aktif.'
        );

        $data = $request->validate([
            'judul' => [
                'required', 'string', 'max:120',
                'not_regex:/[\r\n]/'
            ],
            'lokasi' => [
                'required', 'string', 'max:120',
                'not_regex:/[\r\n]/'
            ],
            'deskripsi' => ['required', 'string', 'max:1500'],
            'foto' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'
            ],
        ], [
            'judul.required' => 'Judul atau jenis keluhan wajib diisi.',
            'judul.max' => 'Judul keluhan maksimal 120 karakter.',
            'lokasi.required' => 'Lokasi kerusakan wajib diisi.',
            'lokasi.max' => 'Lokasi kerusakan maksimal 120 karakter.',
            'deskripsi.required' => 'Deskripsi keluhan wajib diisi.',
            'deskripsi.max' => 'Deskripsi maksimal 1.500 karakter.',
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'foto.uploaded' => 'Foto gagal diunggah.',
        ]);

        $pathFoto = null;

        try {
            if ($request->hasFile('foto')) {
                $pathFoto = $request->file('foto')
                    ->store('keluhan', 'local');

                if ($pathFoto === false) {
                    throw new RuntimeException(
                        'Penyimpanan foto gagal.'
                    );
                }
            }

            $idKeluhan = DB::table('keluhan')->insertGetId([
                'idKost' => $penghuni->idKost,
                'idPenghuni' => $penghuni->idPenghuni,
                'deskripsi' => $this->gabungkanDeskripsi($data),
                'foto' => $pathFoto,
                'status' => 'Diajukan',
                'tanggalPengajuan' => now(),
                'informasiTambahan' => null,
            ]);

            if (!$idKeluhan) {
                throw new RuntimeException(
                    'Data keluhan gagal disimpan.'
                );
            }
        } catch (Throwable $error) {
            report($error);

            if (is_string($pathFoto)) {
                try {
                    Storage::disk('local')->delete($pathFoto);
                } catch (Throwable $cleanupError) {
                    report($cleanupError);
                }
            }

            return back()
                ->withInput(
                    $request->only('judul', 'lokasi', 'deskripsi')
                )
                ->withErrors([
                    'pengajuan' =>
                        'Laporan gagal disimpan. Silakan coba kembali.'
                ]);
        }

        return redirect()
            ->route('keluhan.ajukan')
            ->with('keluhan_berhasil', [
                'idKeluhan' => (int) $idKeluhan,
                'judul' => $data['judul'],
                'lokasi' => $data['lokasi'],
                'foto' => is_string($pathFoto),
                'status' => 'Diajukan',
            ]);
    }

    // Menampilkan seluruh laporan milik penghuni yang login
    public function statusKeluhan(Request $request): View
    {
        $penghuni = $this->penghuniAktif($request);

        abort_unless($penghuni, 403);

        $laporan = DB::table('keluhan')
            ->where('idPenghuni', $penghuni->idPenghuni)
            ->orderByDesc('tanggalPengajuan')
            ->get()
            ->map(function ($item) {
                $detail = $this->uraikanDeskripsi(
                    (string) $item->deskripsi
                );

                $item->judulKeluhan = $detail['judul'];
                $item->lokasiKeluhan = $detail['lokasi'];

                return $item;
            });

        return view('keluhan.status', compact(
            'penghuni', 'laporan'
        ));
    }

    // Melihat detail satu laporan
    public function detailKeluhan(
        Request $request,
        int $idKeluhan
    ): View {
        $penghuni = $this->penghuniAktif($request);

        abort_unless($penghuni, 403);

        $laporan = DB::table('keluhan')
            ->where('idKeluhan', $idKeluhan)
            ->where('idPenghuni', $penghuni->idPenghuni)
            ->first();

        abort_unless($laporan, 404);

        $detail = $this->uraikanDeskripsi(
            (string) $laporan->deskripsi
        );

        return view('keluhan.detail', compact(
            'penghuni', 'laporan', 'detail'
        ));
    }

    // Foto hanya dapat dilihat oleh penghuni pemilik laporan
    public function lihatFoto(
        Request $request,
        int $idKeluhan
    ) {
        $penghuni = $this->penghuniAktif($request);

        abort_unless($penghuni, 403);

        $laporan = DB::table('keluhan')
            ->where('idKeluhan', $idKeluhan)
            ->where('idPenghuni', $penghuni->idPenghuni)
            ->first(['foto']);

        abort_unless($laporan && $laporan->foto, 404);

        abort_unless(
            Storage::disk('local')->exists($laporan->foto),
            404
        );

        return Storage::disk('local')
            ->response($laporan->foto);
    }

    // Menggabungkan judul, lokasi, dan deskripsi tanpa
    // menambahkan kolom database baru.
    private function gabungkanDeskripsi(array $data): string
    {
        return "Judul: {$data['judul']}\n"
            . "Lokasi: {$data['lokasi']}\n"
            . "Deskripsi:\n{$data['deskripsi']}";
    }

    // Memecah kembali data untuk halaman status dan detail
    private function uraikanDeskripsi(string $teks): array
    {
        $pola = '/^Judul: ([^\r\n]*)\r?\n'
            . 'Lokasi: ([^\r\n]*)\r?\n'
            . 'Deskripsi:\r?\n(.*)$/s';

        if (preg_match($pola, $teks, $hasil)) {
            return [
                'judul' => $hasil[1],
                'lokasi' => $hasil[2],
                'deskripsi' => $hasil[3],
            ];
        }

        // Kompatibel dengan laporan lama
        return [
            'judul' => 'Keluhan / Kerusakan',
            'lokasi' => 'Tidak dicantumkan',
            'deskripsi' => $teks,
        ];
    }

    // Mengambil penghuni aktif dari sesi login UC-02
    private function penghuniAktif(Request $request): ?object
    {
        $idPenghuni = $request->session()->get('idPenghuni');
        $idAkun = $request->session()->get('idAkun');

        if (!$idPenghuni && !$idAkun) {
            return null;
        }

        return DB::table('penghuni as p')
            ->leftJoin(
                'kost as k',
                'k.idKost',
                '=',
                'p.idKost'
            )
            ->leftJoin(
                'kamar as km',
                'km.idKamar',
                '=',
                'p.idKamar'
            )
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
                fn ($query) => $query->where(
                    'p.idPenghuni', $idPenghuni
                ),
                fn ($query) => $query->where(
                    'p.idAkun', $idAkun
                )
            )
            ->where('p.status', 'Aktif')
            ->first();
    }
}