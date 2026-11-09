<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Kamar;
use App\Models\Kost;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * <<control>> KontrolKelolaKost (CD-09, SD-09, PC-09)
 * Boundary HalamanKelolaKost = resources/views/kost/daftar.blade.php    (UI-09.01)
 *                              resources/views/kost/form.blade.php      (UI-09.02)
 *                              resources/views/kost/publikasi.blade.php (UI-09.03)
 *
 * Belum termasuk: hapus kost (hapusKost, konfirmasiHapus, tekanHapusKost), karena tidak ada
 * sketsa UI-nya dan tidak dibahas di PC-09.
 */
class KontrolKelolaKost extends Controller
{
    // Batas dan pilihan sesuai PC-09, UI-09.02, dan panjang kolom schema.sql
    private const BATAS_FOTO  = 2 * 1024 * 1024; // 2 MB
    private const FORMAT_FOTO = ['jpg', 'jpeg', 'png'];
    private const MIME_FOTO   = ['image/jpeg', 'image/png'];
    private const TIPE_KOST   = ['Putra', 'Putri', 'Campur'];
    private const FASILITAS   = ['Wi-Fi', 'Parkir', 'Dapur', 'Kamar mandi dalam', 'Water Heater', 'AC', 'Laundry', 'CCTV', 'Musala'];
    private const WAJIB       = ['namaKost', 'tipeKost', 'alamat', 'fasilitasUmum', 'deskripsi', 'peraturan'];
    private const BATAS_KOLOM = [
        'namaKost' => 100, 'alamat' => 255, 'fasilitasUmum' => 1000,
        'deskripsi' => 2000, 'peraturan' => 2000, 'kontakPemilik' => 100,
    ];

    // Pesan (PC-09)
    private const PESAN_FORM_BELUM_LENGKAP = 'Form belum lengkap';
    private const PESAN_FOTO               = 'File maksimal 2MB (JPG/PNG)';
    private const PESAN_PERIKSA            = 'Periksa kembali isian yang ditandai.';
    private const PESAN_GAGAL              = 'Proses gagal, coba kembali';
    private const PESAN_TAMBAH_KAMAR       = 'Tambahkan minimal satu kamar sebelum mempublikasikan kost';
    private const PESAN_DRAFT_TERSIMPAN    = 'Data kost berhasil disimpan dengan status Draft';
    private const PESAN_PUBLIKASI_BERHASIL = 'Kost berhasil dipublikasikan';
    private const PESAN_DATA_GAGAL         = 'Data kost belum dapat dimuat. Silakan coba lagi.';

    /** 'isian kosong', 'foto tidak sesuai', 'isian tidak sesuai', atau 'valid' (PC-09: validasi). */
    private string $statusValidasi = 'valid';

    /** @var array<string,string> pesan kesalahan per field */
    private array $kesalahan = [];

    // ---------- Identitas pemilik yang sedang masuk ----------

    /**
     * ID pemilik diambil dari sesi login (session 'idAkun').
     * Selama halaman login belum ada, di lingkungan lokal boleh memakai ?idPemilik=...
     * HAPUS cadangan lewat URL ini setelah login selesai.
     */
    private function idPemilikAktif(Request $request): ?int
    {
        $id = session('idAkun');

        if ($id === null && app()->environment('local')) {
            $id = $request->query('idPemilik');
        }

        return ($id !== null && ctype_digit((string) $id)) ? (int) $id : null;
    }

    /** Parameter URL yang ikut dibawa saat memakai ?idPemilik (hanya untuk pengujian lokal). */
    private function parameterUji(): array
    {
        $id = request()->query('idPemilik');

        if (session('idAkun') === null && app()->environment('local') && $id !== null) {
            return ['idPemilik' => (string) $id];
        }

        return [];
    }

    /** Kost harus ada dan milik pemilik yang sedang masuk, kalau tidak 404. */
    private function kostMilik(int $idKost, int $idPemilik): object
    {
        $kost = Kost::ambilKost($idKost);

        if ($kost === null || (int) $kost->idPemilik !== $idPemilik) {
            abort(404);
        }

        return $kost;
    }

    // ---------- Daftar kost (UI-09.01) ----------

    /** Pemilik memilih menu "Kelola Kost" (GET /kelola-kost). */
    public function pilihKelolaKost(Request $request): View|RedirectResponse
    {
        $id = $this->idPemilikAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        return $this->ambilDaftarKost($id);
    }

    public function ambilDaftarKost(int $idPemilik): View
    {
        try {
            $daftar = Kost::cariKost($idPemilik);
        } catch (Throwable $e) {
            report($e);
            return $this->tampilGagal($idPemilik);
        }

        if ($daftar->isEmpty()) {
            return $this->tampilKosong($idPemilik);
        }

        return $this->tampilDaftarKost($idPemilik, $daftar);
    }

    public function tampilDaftarKost(int $idPemilik, Collection $daftar): View
    {
        $uji = $this->parameterUji();

        $baris = $daftar->map(fn ($k) => [
            'id'           => $k->idKost,
            'nama'         => (string) $k->namaKost,
            'tipe'         => $k->tipeKost ?? '-',
            'jumlahKamar'  => (int) $k->jumlahKamar,
            'status'       => (string) ($k->status ?? '-'),
            'gaya'         => $k->status === Kost::STATUS_DIPUBLIKASIKAN ? 'publik' : 'draft',
            'urlUbah'      => route('kost.ubah', ['idKost' => $k->idKost] + $uji),
            'urlKamar'     => route('kost.kamar', ['idKost' => $k->idKost] + $uji),
            'urlPublikasi' => route('kost.publikasi', ['idKost' => $k->idKost] + $uji),
        ])->all();

        return view('kost.daftar', $this->kerangka($idPemilik, $baris[0]['nama']) + [
            'mode'    => 'daftar',
            'heading' => 'Kelola kost',
            'baris'   => $baris,
            'ringkas' => [
                'kost'  => count($baris),
                'kamar' => array_sum(array_column($baris, 'jumlahKamar')),
                'publik' => count(array_filter($baris, fn ($b) => $b['gaya'] === 'publik')),
            ],
            'berhasil' => session('berhasil'),
        ]);
    }

    /** Pemilik belum punya kost. */
    public function tampilKosong(int $idPemilik): View
    {
        return view('kost.daftar', $this->kerangka($idPemilik) + [
            'mode'     => 'kosong',
            'heading'  => 'Kelola kost',
            'ringkas'  => ['kost' => 0, 'kamar' => 0, 'publik' => 0],
            'berhasil' => session('berhasil'),
        ]);
    }

    /** Gangguan saat mengambil data. */
    public function tampilGagal(int $idPemilik): View
    {
        return view('kost.daftar', $this->kerangka($idPemilik) + [
            'mode'     => 'gagal',
            'heading'  => 'Kelola kost',
            'ringkas'  => ['kost' => 0, 'kamar' => 0, 'publik' => 0],
            'pesan'    => self::PESAN_DATA_GAGAL,
            'berhasil' => null,
        ]);
    }

    // ---------- Form tambah / ubah kost (UI-09.02) ----------

    /** Tombol "+ Tambah Kost" (GET /kelola-kost/tambah): formulir kosong. */
    public function tekanTambahKost(Request $request): View|RedirectResponse
    {
        $id = $this->idPemilikAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        return $this->tampilForm($id, null);
    }

    /** Tombol "Ubah Kost ..." (GET /kelola-kost/{idKost}/ubah): formulir berisi data lama. */
    public function tekanUbahKost(Request $request, int $idKost): View|RedirectResponse
    {
        $id = $this->idPemilikAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        try {
            $kost = $this->kostMilik($idKost, $id);
        } catch (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e; // 404 tetap 404
            }
            report($e);
            return $this->tampilGagal($id);
        }

        return $this->tampilForm($id, $kost);
    }

    /**
     * tampilForm(idKost) pada PC-09: formulir kosong jika $kostLama null,
     * formulir berisi data kost lama jika tidak. Setelah gagal simpan, $data berisi isian terakhir.
     */
    public function tampilForm(
        int $idPemilik,
        ?object $kostLama,
        ?array $data = null,
        array $kesalahan = [],
        ?string $banner = null
    ): View {
        $uji  = $this->parameterUji();
        $data ??= $kostLama !== null ? $this->dataDariKost($kostLama) : $this->dataKosong();

        return view('kost.form', $this->kerangka($idPemilik, $data['namaKost'] !== '' ? $data['namaKost'] : 'Kost.io') + [
            'heading'   => 'Tambah / ubah data kost',
            'aksi'      => $kostLama === null
                ? route('kost.simpan', $uji)
                : route('kost.perbarui', ['idKost' => $kostLama->idKost] + $uji),
            'batal'     => route('kost.daftar', $uji),
            'data'      => $data,
            'kesalahan' => $kesalahan,
            'banner'    => $banner,
            'tipeKost'  => self::TIPE_KOST,
            'fasilitas' => self::FASILITAS,
            'fotoUrl'   => $this->tampilFoto($kostLama?->fotoKost),
        ]);
    }

    /** Alamat gambar kost untuk pratinjau di formulir (CD-09: tampilFoto). */
    public function tampilFoto(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return str_starts_with($path, 'uploads/kost/') ? asset($path) : null;
    }

    // ---------- Simpan sebagai draft (PC-09: kirimData) ----------

    /** Tombol "Simpan sebagai Draft" (POST /kelola-kost atau /kelola-kost/{idKost}/ubah). */
    public function tekanSimpan(Request $request, ?int $idKost = null): View|RedirectResponse
    {
        $id = $this->idPemilikAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        $kostLama = null;

        if ($idKost !== null) {
            try {
                $kostLama = $this->kostMilik($idKost, $id);
            } catch (Throwable $e) {
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    throw $e;
                }
                report($e);
                return $this->tampilGagal($id);
            }
        }

        return $this->kirimData($id, $this->dataDariRequest($request), $request->file('foto'), $kostLama);
    }

    public function kirimData(int $idPemilik, array $data, ?UploadedFile $foto, ?object $kostLama): View|RedirectResponse
    {
        if (!$this->validasi($data, $foto, $kostLama)) {
            $banner = match ($this->statusValidasi) {
                'isian kosong'      => self::PESAN_FORM_BELUM_LENGKAP,
                'foto tidak sesuai' => self::PESAN_FOTO,
                default             => self::PESAN_PERIKSA,
            };

            return $this->tampilForm($idPemilik, $kostLama, $data, $this->kesalahan, $banner);
        }

        // Foto lama dipakai terus kecuali pemilik memilih foto baru
        $fotoLama  = $kostLama->fotoKost ?? null;
        $pathFoto  = $fotoLama;
        $fotoBaru  = null;

        try {
            if ($foto !== null) {
                $fotoBaru = $this->simpanFoto($foto);
                $pathFoto = $fotoBaru;
            }

            $simpan = [
                'idPemilik'     => $idPemilik,
                'namaKost'      => $data['namaKost'],
                'alamat'        => $data['alamat'],
                'lokasi'        => mb_substr($data['alamat'], 0, 100),
                'tipeKost'      => $data['tipeKost'],
                'fasilitasUmum' => implode(', ', $data['fasilitasUmum']),
                'deskripsi'     => $data['deskripsi'],
                'peraturan'     => $data['peraturan'],
                'kontakPemilik' => $data['kontakPemilik'] !== '' ? $data['kontakPemilik'] : null,
                'fotoUtama'     => $pathFoto,
                'fotoKost'      => $pathFoto,
            ];

            $berhasil = $kostLama === null
                ? Kost::simpanDraft($simpan) !== null
                : Kost::ubahData((int) $kostLama->idKost, $simpan);
        } catch (Throwable $e) {
            report($e);
            $berhasil = false;
        }

        if (!$berhasil) {
            $this->hapusFoto($fotoBaru); // jangan tinggalkan berkas yatim
            return $this->tampilForm($idPemilik, $kostLama, $data, [], self::PESAN_GAGAL);
        }

        if ($fotoBaru !== null) {
            $this->hapusFoto($fotoLama); // foto lama sudah tidak dipakai
        }

        return redirect()->route('kost.daftar', $this->parameterUji())->with('berhasil', self::PESAN_DRAFT_TERSIMPAN);
    }

    /**
     * validasi (PC-09): isian wajib kosong lalu foto tidak sesuai. Tambahan di luar PC-09 hanya yang
     * diwajibkan schema.sql: tipe kost harus salah satu pilihan dan panjang isian tidak melebihi kolom.
     */
    public function validasi(array $d, ?UploadedFile $foto, ?object $kostLama = null): bool
    {
        $this->kesalahan      = [];
        $this->statusValidasi = 'valid';

        // 1. Isian wajib (foto wajib untuk kost baru, atau kost lama yang belum punya foto)
        foreach (self::WAJIB as $field) {
            $nilai  = $d[$field];
            $kosong = is_array($nilai) ? count($nilai) === 0 : trim((string) $nilai) === '';

            if ($kosong) {
                $this->kesalahan[$field] = 'Wajib diisi';
            }
        }

        $punyaFoto = $foto !== null || !empty($kostLama?->fotoKost);

        if (!$punyaFoto) {
            $this->kesalahan['foto'] = 'Wajib diisi';
        }

        if ($this->kesalahan !== []) {
            $this->statusValidasi = 'isian kosong';
            return false;
        }

        // 2. Foto: maksimal 2 MB, format JPG/PNG
        if ($foto !== null && !$this->fotoSesuai($foto)) {
            $this->kesalahan['foto'] = self::PESAN_FOTO;
            $this->statusValidasi    = 'foto tidak sesuai';
            return false;
        }

        // 3. Sesuai schema.sql
        if (!in_array($d['tipeKost'], self::TIPE_KOST, true)) {
            $this->kesalahan['tipeKost'] = 'Pilih tipe kost yang tersedia';
        }

        $teks = $d;
        $teks['fasilitasUmum'] = implode(', ', $d['fasilitasUmum']);

        foreach (self::BATAS_KOLOM as $field => $maks) {
            if (mb_strlen((string) $teks[$field]) > $maks) {
                $this->kesalahan[$field] = 'Maksimal ' . $maks . ' karakter';
            }
        }

        if ($this->kesalahan !== []) {
            $this->statusValidasi = 'isian tidak sesuai';
            return false;
        }

        return true;
    }

    private function fotoSesuai(UploadedFile $foto): bool
    {
        if (!$foto->isValid()) {
            return false; // termasuk berkas yang melebihi batas upload PHP
        }

        if ($foto->getSize() > self::BATAS_FOTO) {
            return false;
        }

        $ekstensi = strtolower($foto->getClientOriginalExtension());

        if (!in_array($ekstensi, self::FORMAT_FOTO, true)) {
            return false;
        }

        return in_array($foto->getMimeType(), self::MIME_FOTO, true);
    }

    /** Foto disimpan di public/uploads/kost (tanpa storage:link) dan yang dicatat adalah path relatifnya. */
    private function simpanFoto(UploadedFile $foto): string
    {
        $nama = 'kost-' . date('YmdHis') . '-' . Str::random(8) . '.' . strtolower($foto->getClientOriginalExtension());
        $foto->move(public_path('uploads/kost'), $nama);

        return 'uploads/kost/' . $nama;
    }

    private function hapusFoto(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'uploads/kost/') && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    // ---------- Publikasi kost (UI-09.03) ----------

    /** Tombol "Publikasikan" (GET /kelola-kost/{idKost}/publikasi). */
    public function pilihPublikasi(Request $request, int $idKost): View|RedirectResponse
    {
        $id = $this->idPemilikAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        try {
            $kost   = $this->kostMilik($idKost, $id);
            $jumlah = Kamar::cekKamar($idKost);
        } catch (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return $this->tampilGagal($id);
        }

        $uji   = $this->parameterUji();
        $sudah = $kost->status === Kost::STATUS_DIPUBLIKASIKAN;

        if ($sudah) {
            $judulKartu = $kost->namaKost . ' sudah dipublikasikan';
            $isiKartu   = 'Kost muncul pada hasil pencarian · ' . $jumlah . ' kamar terdaftar.';
        } elseif ($jumlah > 0) {
            $judulKartu = $kost->namaKost . ' siap dipublikasikan';
            $isiKartu   = 'Data lengkap · ' . $jumlah . ' kamar terdaftar. Kost akan muncul pada hasil pencarian.';
        } else {
            $judulKartu = $kost->namaKost . ' belum memiliki kamar';
            $isiKartu   = self::PESAN_TAMBAH_KAMAR . '.';
        }

        return view('kost.publikasi', $this->kerangka($id, (string) $kost->namaKost) + [
            'heading'         => 'Publikasi kost',
            'judulKartu'      => $judulKartu,
            'isiKartu'        => $isiKartu,
            'sudah'           => $sudah,
            'urlPublikasikan' => route('kost.publikasikan', ['idKost' => $idKost] + $uji),
            'urlKamar'        => route('kost.kamar', ['idKost' => $idKost] + $uji),
            'urlDaftar'       => route('kost.daftar', $uji),
            'galat'           => session('galat'),
        ]);
    }

    /** Tombol "Publikasikan Kost" (POST /kelola-kost/{idKost}/publikasi), mengikuti PC-09: publikasiKost. */
    public function publikasiKost(Request $request, int $idKost): RedirectResponse
    {
        $id = $this->idPemilikAktif($request);

        if ($id === null) {
            return redirect('/login');
        }

        $uji = $this->parameterUji();

        try {
            $this->kostMilik($idKost, $id);
            $jumlah = Kamar::cekKamar($idKost);
        } catch (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->route('kost.publikasi', ['idKost' => $idKost] + $uji)->with('galat', self::PESAN_GAGAL);
        }

        // Belum ada kamar: tampilkan pesan, lalu pemilik diarahkan ke "Tambah kamar" (bukaKamar)
        if ($jumlah === 0) {
            return redirect()->route('kost.publikasi', ['idKost' => $idKost] + $uji)->with('galat', self::PESAN_TAMBAH_KAMAR);
        }

        if (!Kost::ubahStatus($idKost, Kost::STATUS_DIPUBLIKASIKAN)) {
            return redirect()->route('kost.publikasi', ['idKost' => $idKost] + $uji)->with('galat', self::PESAN_GAGAL);
        }

        return redirect()->route('kost.daftar', $uji)->with('berhasil', self::PESAN_PUBLIKASI_BERHASIL);
    }

    
    /**
     * UC-09 ke UC-10:
     * Membuka halaman pengelolaan kamar
     * berdasarkan kost milik pemilik.
     */
    public function bukaKamar(
        Request $request,
        int $idKost
    ): RedirectResponse {

        // Mengambil ID pemilik dari sesi login
        $idPemilik = $this->idPemilikAktif($request);

        if ($idPemilik === null) {
            return redirect()->route('login');
        }

        // Memastikan kost benar-benar milik pemilik
        $this->kostMilik($idKost, $idPemilik);

        // Menghubungkan UC-09 ke UC-10
        return redirect()->route('kamar.index', [
            'idKost' => $idKost
        ]);
    }


    // ---------- Pembantu ----------

    private function dataKosong(): array
    {
        return [
            'namaKost' => '', 'tipeKost' => self::TIPE_KOST[0], 'alamat' => '',
            'fasilitasUmum' => [], 'deskripsi' => '', 'peraturan' => '', 'kontakPemilik' => '',
        ];
    }

    private function dataDariKost(object $k): array
    {
        return [
            'namaKost'      => (string) $k->namaKost,
            'tipeKost'      => (string) ($k->tipeKost ?? self::TIPE_KOST[0]),
            'alamat'        => (string) $k->alamat,
            'fasilitasUmum' => array_values(array_filter(array_map('trim', explode(',', (string) $k->fasilitasUmum)))),
            'deskripsi'     => (string) $k->deskripsi,
            'peraturan'     => (string) $k->peraturan,
            'kontakPemilik' => (string) ($k->kontakPemilik ?? ''),
        ];
    }

    private function dataDariRequest(Request $request): array
    {
        $fasilitas = array_map('strval', (array) $request->input('fasilitasUmum', []));

        return [
            'namaKost'      => trim((string) $request->input('namaKost', '')),
            'tipeKost'      => trim((string) $request->input('tipeKost', '')),
            'alamat'        => trim((string) $request->input('alamat', '')),
            'fasilitasUmum' => array_values(array_intersect(self::FASILITAS, $fasilitas)),
            'deskripsi'     => trim((string) $request->input('deskripsi', '')),
            'peraturan'     => trim((string) $request->input('peraturan', '')),
            'kontakPemilik' => trim((string) $request->input('kontakPemilik', '')),
        ];
    }

    /** Data kerangka halaman (nama pengguna di pojok kanan atas dan lencana di judul). */
    private function kerangka(int $idPemilik, string $lencana = 'Kost.io'): array
    {
        $nama = 'Pemilik Kost';

        try {
            $akun = Akun::find($idPemilik);
            if ($akun && trim((string) $akun->namaLengkap) !== '') {
                $nama = $akun->namaLengkap;
            }
        } catch (Throwable $e) {
            report($e);
        }

        $kata    = preg_split('/\s+/', trim($nama));
        $inisial = mb_strtoupper(mb_substr($kata[0], 0, 1) . (isset($kata[1]) ? mb_substr($kata[1], 0, 1) : ''));

        return [
            'pengguna' => ['nama' => $nama, 'inisial' => $inisial, 'peran' => 'Pemilik Kost'],
            'qs'       => $this->parameterUji(),
            'lencana'  => $lencana,
        ];
    }
}