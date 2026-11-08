<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * <<control>> RegistrasiController (CD-01, SD-01, PC-01)
 * Boundary (form) = resources/views/registrasi/form.blade.php
 */
class RegistrasiController extends Controller
{
    // Hasil registrasi (PC-01 dan teks banner UI-01.02)
    private const HASIL_BERHASIL     = 'Registrasi berhasil';
    private const HASIL_GAGAL        = 'Registrasi gagal, coba kembali';
    private const HASIL_PERIKSA_DATA = 'Registrasi belum berhasil. Perbaiki isian yang ditandai.';

    /** Batas panjang kolom pada schema.sql: namaLengkap 100, email 254, noTelepon 20. */
    private const BATAS_KOLOM = ['nama' => 100, 'email' => 254, 'noTelepon' => 20];

    /** 'form' = UI-01.01, 'gagal' = UI-01.02, 'berhasil' = UI-01.03 */
    private string $status = 'form';
    private string $hasil  = '';
    private string $banner = '';

    /** @var array<string,string> pesan kesalahan per field */
    private array $errors = [];

    /** @var array<string,string> */
    private array $data = [
        'nama' => '', 'email' => '', 'noTelepon' => '',
        'kataSandi' => '', 'konfirmasiKataSandi' => '', 'jenisAkun' => '',
    ];

    // ---------- Navigasi ----------

    /** Pengguna memilih menu Registrasi (GET /registrasi). */
    public function pilihMenuRegistrasi(): View
    {
        return $this->tampilkanFormRegistrasi();
    }

    public function tampilkanFormRegistrasi(): View
    {
        return $this->formRegistrasi();
    }

    /** Menyiapkan form kosong (UI-01.01). */
    public function formRegistrasi(): View
    {
        $this->status = 'form';
        $this->hasil  = '';
        $this->banner = '';
        $this->errors = [];

        return $this->tampilkanHasilRegistrasi();
    }

    public function arahkanKeHalamanLogin(): RedirectResponse
    {
        return redirect('/login');
    }

    /** UI-01.03, hanya bisa dibuka setelah registrasi berhasil. */
    public function halamanBerhasil(): View|RedirectResponse
    {
        if (!session('registrasi_berhasil')) {
            return redirect()->route('registrasi.form');
        }

        $this->pesanRegistrasiBerhasil();
        return $this->tampilkanHasilRegistrasi();
    }

    /** Menampilkan layar sesuai status (PC-01: tampilkanHasilRegistrasi). */
    public function tampilkanHasilRegistrasi(): View
    {
        return view('registrasi.form', [
            'status'           => $this->status,
            'banner'           => $this->banner,
            'kesalahan'        => $this->errors,
            'data'             => $this->data,
            'jenisAkunPilihan' => Akun::JENIS_AKUN_PILIHAN,
        ]);
    }

    // ---------- Input ----------

    public function isiData(
        string $nama,
        string $email,
        string $noTelepon,
        string $kataSandi,
        string $konfirmasiKataSandi,
        string $jenisAkun
    ): void {
        $this->data = [
            'nama'                => trim($nama),
            'email'               => strtolower(trim($email)),
            'noTelepon'           => trim($noTelepon),
            'kataSandi'           => $kataSandi,
            'konfirmasiKataSandi' => $konfirmasiKataSandi,
            'jenisAkun'           => trim($jenisAkun),
        ];
    }

    /** Dipanggil saat tombol "Daftar" atau "Perbaiki data" ditekan (POST /registrasi). */
    public function klikDaftar(Request $request): View|RedirectResponse
    {
        $berhasil = $this->registrasi(
            (string) $request->input('nama', ''),
            (string) $request->input('email', ''),
            (string) $request->input('noTelepon', ''),
            (string) $request->input('kataSandi', ''),
            (string) $request->input('konfirmasiKataSandi', ''),
            (string) $request->input('jenisAkun', '')
        );

        if ($berhasil) {
            return redirect()->route('registrasi.berhasil')->with('registrasi_berhasil', true);
        }

        return $this->tampilkanHasilRegistrasi();
    }

    // ---------- Proses utama (PC-01) ----------

    public function registrasi(
        string $nama,
        string $email,
        string $noTelepon,
        string $kataSandi,
        string $konfirmasiKataSandi,
        string $jenisAkun
    ): bool {
        $this->isiData($nama, $email, $noTelepon, $kataSandi, $konfirmasiKataSandi, $jenisAkun);

        // 1. Validasi data
        if (!$this->validasiData()) {
            // UI-01.02 menampilkan semua kesalahan sekaligus, termasuk email yang sudah terpakai.
            if (!isset($this->errors['email']) && $this->emailSudahTerdaftar($this->data['email'])) {
                $this->pesanEmailTelahDigunakan();
            }
            $this->kirimHasilRegistrasi(self::HASIL_PERIKSA_DATA);
            return false;
        }

        try {
            // 2. Cek email
            if (Akun::cekEmailTerdaftar($this->data['email'])) {
                $this->pesanEmailTelahDigunakan();
                $this->kirimHasilRegistrasi(self::HASIL_PERIKSA_DATA);
                return false;
            }

            // 3. Enkripsi kata sandi lalu simpan akun
            $kataSandiTerenkripsi = $this->enkripsi($this->data['kataSandi']);
            $berhasil = Akun::simpanAkun(
                $this->data['nama'],
                $this->data['email'],
                $this->data['noTelepon'],
                $kataSandiTerenkripsi,
                $this->data['jenisAkun']
            );
        } catch (Throwable $e) {
            report($e);
            $berhasil = false;
        }

        // 4. Kirim hasil
        $this->kirimHasilRegistrasi($berhasil ? self::HASIL_BERHASIL : self::HASIL_GAGAL);
        return $berhasil;
    }

    /**
     * Validasi sesuai PC-01 (data kosong, format email, kata sandi dan konfirmasi),
     * kesalahan dicatat per field agar bisa ditampilkan seperti UI-01.02.
     * Tambahan di luar PC-01 hanya yang diwajibkan schema.sql: batas panjang kolom
     * dan jenis akun harus salah satu pilihan dropdown.
     */
    public function validasiData(): bool
    {
        $d = $this->data;

        if (in_array('', $d, true)) {
            $this->pesanDataWajibDiisi();
        }
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Format email tidak valid';
        }
        if ($d['kataSandi'] !== '' && $d['konfirmasiKataSandi'] !== ''
            && $d['kataSandi'] !== $d['konfirmasiKataSandi']) {
            $this->errors['konfirmasiKataSandi'] = 'Kata sandi tidak sama';
        }

        foreach (self::BATAS_KOLOM as $field => $maks) {
            if ($d[$field] !== '' && !isset($this->errors[$field]) && mb_strlen($d[$field]) > $maks) {
                $this->errors[$field] = 'Maksimal ' . $maks . ' karakter';
            }
        }
        if ($d['jenisAkun'] !== '' && !in_array($d['jenisAkun'], Akun::JENIS_AKUN_PILIHAN, true)) {
            $this->errors['jenisAkun'] = 'Pilih jenis akun yang tersedia';
        }

        return $this->errors === [];
    }

    private function enkripsi(string $kataSandi): string
    {
        return Hash::make($kataSandi);
    }

    private function emailSudahTerdaftar(string $email): bool
    {
        try {
            return Akun::cekEmailTerdaftar($email);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    // ---------- Pesan ----------

    public function pesanDataWajibDiisi(): void
    {
        foreach ($this->data as $field => $nilai) {
            if ($nilai === '') {
                $this->errors[$field] = 'Data wajib diisi';
            }
        }
    }

    public function pesanEmailTelahDigunakan(): void
    {
        $this->errors['email'] = 'Email sudah digunakan';
    }

    /** Menyiapkan tampilan UI-01.03. */
    public function pesanRegistrasiBerhasil(): void
    {
        $this->status = 'berhasil';
        $this->banner = '';
        $this->errors = [];
    }

    /** Kegagalan penyimpanan (bukan salah isian), ditampilkan di UI-01.02. */
    public function pesanRegistrasiGagal(): void
    {
        $this->status = 'gagal';
        $this->banner = self::HASIL_GAGAL;
    }

    /**
     * kirimHasilRegistrasi(status) pada PC-01: meneruskan hasil ke form.
     * Pengalihan ke login dilakukan tombol "Masuk sekarang" (UI-01.03).
     */
    public function kirimHasilRegistrasi(string $hasil): void
    {
        $this->hasil = $hasil;

        if ($hasil === self::HASIL_BERHASIL) {
            $this->pesanRegistrasiBerhasil();
        } elseif ($hasil === self::HASIL_GAGAL) {
            $this->pesanRegistrasiGagal();
        } else {
            $this->status = 'gagal';
            $this->banner = $hasil;
        }
    }
}