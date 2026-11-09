<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // UC-02: Menampilkan halaman login
    public function pilihMenuLogin()
    {
        return view('auth.login');
    }

    public function tampilkanHalamanLogin()
    {
        return $this->pilihMenuLogin();
    }

    // UC-02: Memeriksa akun dan membuat sesi login
    public function login(Request $request)
    {
        $email = strtolower(trim(
            (string) $request->input('email', '')
        ));

        $kataSandi = (string) $request->input(
            'kata_sandi', ''
        );

        $peran = (string) $request->input(
            'peran', ''
        );

        // Validasi kelengkapan
        $status = $this->validasiKelengkapan(
            $email,
            $kataSandi
        );

        if ($status !== 'valid') {
            return back()
                ->withInput($request->only('email', 'peran'))
                ->with('error', $status);
        }

        // Validasi pilihan peran
        if (!in_array(
            $peran,
            ['Calon Penghuni', 'Penghuni', 'Pemilik Kost'],
            true
        )) {
            return back()
                ->withInput($request->only('email', 'peran'))
                ->with('error', 'Silakan pilih peran login yang valid.');
        }

        try {
            // Mengambil akun dari tabel akun
            $dataAkun = DB::table('akun')
                ->where('email', $email)
                ->first();

            if (!$dataAkun) {
                return back()
                    ->withInput($request->only('email', 'peran'))
                    ->with(
                        'error',
                        'Akun belum terdaftar, silakan registrasi'
                    )
                    ->with('tampilkanRegistrasi', true);
            }

            // Memeriksa kata sandi
            if (!$this->cocokkanKataSandi(
                $kataSandi,
                $dataAkun
            )) {
                return back()
                    ->withInput($request->only('email', 'peran'))
                    ->with(
                        'error',
                        'Email atau kata sandi tidak sesuai'
                    );
            }

            $jenisAkun = (string) $dataAkun->jenisAkun;
            $penghuniAktif = null;

            // Pemeriksaan peran pemilik kost
            if (
                $peran === 'Pemilik Kost' &&
                $jenisAkun !== 'Pemilik Kost'
            ) {
                return back()
                    ->withInput($request->only('email', 'peran'))
                    ->with(
                        'error',
                        'Akun ini bukan akun pemilik kost.'
                    );
            }

            // Pemeriksaan peran calon penghuni
            if (
                $peran === 'Calon Penghuni' &&
                $jenisAkun !== 'Calon Penghuni'
            ) {
                return back()
                    ->withInput($request->only('email', 'peran'))
                    ->with(
                        'error',
                        'Akun ini tidak terdaftar sebagai calon penghuni.'
                    );
            }

            // Pemeriksaan peran penghuni
            if ($peran === 'Penghuni') {
                if (!in_array(
                    $jenisAkun,
                    ['Calon Penghuni', 'Penghuni'],
                    true
                )) {
                    return back()
                        ->withInput($request->only('email', 'peran'))
                        ->with(
                            'error',
                            'Akun ini bukan akun penghuni.'
                        );
                }

                // Memastikan akun memiliki hunian aktif
                $penghuniAktif = DB::table('penghuni')
                    ->where('idAkun', $dataAkun->idAkun)
                    ->where('status', 'Aktif')
                    ->orderByDesc('idPenghuni')
                    ->first(['idPenghuni']);

                if (!$penghuniAktif) {
                    return back()
                        ->withInput($request->only('email', 'peran'))
                        ->with(
                            'error',
                            'Akun belum memiliki data penghuni aktif.'
                        );
                }
            }

            // Membersihkan sesi login sebelumnya
            $request->session()->forget([
                'idAkun',
                'idPenghuni',
                'jenisAkun'
            ]);

            // Mengganti ID sesi setelah login
            $request->session()->regenerate();

            // Menyimpan identitas akun
            $request->session()->put(
                'idAkun',
                (int) $dataAkun->idAkun
            );

            $request->session()->put(
                'jenisAkun',
                $peran
            );

            // Menyimpan ID penghuni jika relevan
            if ($penghuniAktif) {
                $request->session()->put(
                    'idPenghuni',
                    (int) $penghuniAktif->idPenghuni
                );
            }

            // Mengarahkan sesuai peran
            return $this->arahkanKeHalamanUtama(
                $peran
            );

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email', 'peran'))
                ->with(
                    'error',
                    'Login tidak dapat dilakukan, silakan coba kembali.'
                );
        }
    }

    // Memvalidasi email dan kata sandi
    public function validasiKelengkapan(
        string $email,
        string $kataSandi
    ): string {
        if ($email === '' || $kataSandi === '') {
            return 'Email atau kata sandi wajib diisi';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid';
        }

        return 'valid';
    }

    // Memeriksa kata sandi yang sudah di-hash
    public function cocokkanKataSandi(
        string $kataSandi,
        object $dataAkun
    ): bool {
        return Hash::check(
            $kataSandi,
            (string) ($dataAkun->kataSandi ?? '')
        );
    }

    // Mengambil jenis akun
    public function getJenisAkun(string $email): string
    {
        return (string) (
            DB::table('akun')
                ->where('email', $email)
                ->value('jenisAkun') ?? ''
        );
    }

    // Mengarahkan pengguna berdasarkan perannya
    public function arahkanKeHalamanUtama(
        string $jenisAkun
    ) {
        if ($jenisAkun === 'Pemilik Kost') {

            // Mengambil kost milik akun yang login
            $idKost = DB::table('kost')
                ->where(
                    'idPemilik',
                    session('idAkun')
                )
                ->orderBy('idKost')
                ->value('idKost');

            if ($idKost) {
                return redirect()->route(
                    'kamar.index',
                    ['idKost' => $idKost]
                );
            }

            return redirect()->route(
                'pemilik.keuangan.index'
            );
        }

        if ($jenisAkun === 'Penghuni') {
            return redirect()->route(
                'keluhan.status'
            );
        }

        // UC-03 masih dalam pengembangan
        return redirect('/')
            ->with(
                'info',
                'Login berhasil. Fitur pencarian kost masih dalam pengembangan.'
            );
    }

    // Mengarahkan pengguna ke registrasi
    public function tampilkanOpsiRegistrasi()
    {
        return redirect()
            ->route('registrasi.form')
            ->with(
                'info',
                'Silakan daftar akun baru'
            );
    }

    // Menampilkan pesan kesalahan
    public function tampilkanPesan(string $pesan)
    {
        return back()->with('error', $pesan);
    }
}
