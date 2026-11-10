<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | UC-02: Menampilkan Halaman Login
    |--------------------------------------------------------------------------
    */
    public function pilihMenuLogin()
    {
        return view('auth.login');
    }

    public function tampilkanHalamanLogin()
    {
        return $this->pilihMenuLogin();
    }

    /*
    |--------------------------------------------------------------------------
    | UC-02: Proses Login
    |--------------------------------------------------------------------------
    */
    public function login(Request $request): RedirectResponse
    {
        $email = strtolower(trim(
            (string) $request->input('email', '')
        ));

        $kataSandi = (string) $request->input(
            'kata_sandi', ''
        );

        $peran = trim((string) $request->input(
            'peran', ''
        ));

        // 1. Validasi kelengkapan
        $status = $this->validasiKelengkapan(
            $email,
            $kataSandi
        );

        if ($status !== 'valid') {
            return $this->loginGagal($request, $status);
        }

        // 2. Validasi pilihan peran
        $peranValid = [
            'Calon Penghuni',
            'Penghuni',
            'Pemilik Kost'
        ];

        if (!in_array($peran, $peranValid, true)) {
            return $this->loginGagal(
                $request,
                'Silakan pilih peran login yang valid.'
            );
        }

        // 3. Mengambil data akun dari MySQL
        try {
            $dataAkun = DB::table('akun')
                ->where('email', $email)
                ->first();

        } catch (\Throwable $e) {
            report($e);

            return $this->loginGagal(
                $request,
                'Login tidak dapat dilakukan. Periksa koneksi database.'
            );
        }

        // 4. Memeriksa akun terdaftar
        if (!$dataAkun) {
            return back()
                ->withInput($request->only(
                    'email', 'peran'
                ))
                ->with(
                    'error',
                    'Akun belum terdaftar, silakan registrasi.'
                )
                ->with('tampilkanRegistrasi', true);
        }

        // 5. Memeriksa password
        if (!$this->cocokkanKataSandi(
            $kataSandi,
            $dataAkun
        )) {
            return $this->loginGagal(
                $request,
                'Email atau kata sandi tidak sesuai.'
            );
        }

        // 6. Mengambil jenis akun dari database
        $jenisAkun = trim(
            (string) ($dataAkun->jenisAkun ?? '')
        );

        $idPenghuni = null;

        /*
        |--------------------------------------------------------------------------
        | 7. Memeriksa Peran
        |--------------------------------------------------------------------------
        */

        // Login Pemilik Kost
        if ($peran === 'Pemilik Kost') {
            if ($jenisAkun !== 'Pemilik Kost') {
                return $this->loginGagal(
                    $request,
                    'Akun ini bukan akun Pemilik Kost.'
                );
            }
        }

        // Login Calon Penghuni
        if ($peran === 'Calon Penghuni') {
            if ($jenisAkun !== 'Calon Penghuni') {
                return $this->loginGagal(
                    $request,
                    'Akun ini bukan akun Calon Penghuni.'
                );
            }
        }

        // Login Penghuni
        if ($peran === 'Penghuni') {
            if (!in_array(
                $jenisAkun,
                ['Calon Penghuni', 'Penghuni'],
                true
            )) {
                return $this->loginGagal(
                    $request,
                    'Akun ini bukan akun Penghuni.'
                );
            }

            try {
                // Mengambil data penghuni aktif
                $penghuni = DB::table('penghuni')
                    ->where(
                        'idAkun',
                        $dataAkun->idAkun
                    )
                    ->where('status', 'Aktif')
                    ->orderByDesc('idPenghuni')
                    ->first();

            } catch (\Throwable $e) {
                report($e);

                return $this->loginGagal(
                    $request,
                    'Data penghuni tidak dapat diperiksa.'
                );
            }

            if (!$penghuni) {
                return $this->loginGagal(
                    $request,
                    'Akun belum memiliki hunian aktif.'
                );
            }

            $idPenghuni = (int) $penghuni->idPenghuni;
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Membuat Session Laravel
        |--------------------------------------------------------------------------
        */

        // Menghindari sesi lama tercampur
        $request->session()->forget([
            'idAkun',
            'idPenghuni',
            'jenisAkun'
        ]);

        // Mencegah session fixation
        $request->session()->regenerate();

        // Menyimpan identitas pengguna
        $request->session()->put(
            'idAkun',
            (int) $dataAkun->idAkun
        );

        $request->session()->put(
            'jenisAkun',
            $peran
        );

        // Khusus Penghuni aktif
        if ($idPenghuni !== null) {
            $request->session()->put(
                'idPenghuni',
                $idPenghuni
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Redirect Sesuai Peran
        |--------------------------------------------------------------------------
        */
        return $this->arahkanKeHalamanUtama($peran);
    }

    /*
    |--------------------------------------------------------------------------
    | Memvalidasi Kelengkapan
    |--------------------------------------------------------------------------
    */
    public function validasiKelengkapan(
        string $email,
        string $kataSandi
    ): string {
        if ($email === '' || $kataSandi === '') {
            return 'Email atau kata sandi wajib diisi.';
        }

        if (!filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )) {
            return 'Format email tidak valid.';
        }

        return 'valid';
    }

    /*
    |--------------------------------------------------------------------------
    | Memeriksa Kata Sandi
    |--------------------------------------------------------------------------
    */
    public function cocokkanKataSandi(
        string $kataSandi,
        object $dataAkun
    ): bool {
        $hash = (string) (
            $dataAkun->kataSandi ?? ''
        );

        if ($hash === '') {
            return false;
        }

        return Hash::check($kataSandi, $hash);
    }

    /*
    |--------------------------------------------------------------------------
    | Mengambil Jenis Akun
    |--------------------------------------------------------------------------
    */
    public function getJenisAkun(string $email): string
    {
        return (string) (
            DB::table('akun')
                ->where('email', strtolower(trim($email)))
                ->value('jenisAkun') ?? ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mengarahkan Pengguna Sesuai Peran
    |--------------------------------------------------------------------------
    */
    public function arahkanKeHalamanUtama(
        string $jenisAkun
    ): RedirectResponse {

        // PEMILIK KOST -> UC-09
        if ($jenisAkun === 'Pemilik Kost') {
            return redirect()
                ->route('kost.daftar');
        }

        // PENGHUNI -> UC-08
        if ($jenisAkun === 'Penghuni') {
            return redirect()
                ->route('keluhan.status');
        }

        // CALON PENGHUNI -> UC-03
        if ($jenisAkun === 'Calon Penghuni') {

            if (Route::has('kost.cari')) {
                return redirect()
                    ->route('kost.cari');
            }

            return redirect('/')
                ->with(
                    'info',
                    'Login berhasil. Halaman Cari Kost belum terdaftar.'
                );
        }

        return redirect()
            ->route('login')
            ->with(
                'error',
                'Jenis akun tidak dikenali.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Pesan Login Gagal
    |--------------------------------------------------------------------------
    */
    private function loginGagal(
        Request $request,
        string $pesan
    ): RedirectResponse {
        return back()
            ->withInput($request->only(
                'email',
                'peran'
            ))
            ->with('error', $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | Menu Registrasi
    |--------------------------------------------------------------------------
    */
    public function tampilkanOpsiRegistrasi()
    {
        return redirect()
            ->route('registrasi.form')
            ->with(
                'info',
                'Silakan daftar akun baru.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Menampilkan Pesan
    |--------------------------------------------------------------------------
    */
    public function tampilkanPesan(string $pesan)
    {
        return back()->with('error', $pesan);
    }
}
