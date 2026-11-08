<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // pilihMenuLogin()
    public function pilihMenuLogin()
    {
        return view('auth.login');
    }

    // tampilkanHalamanLogin()
    public function tampilkanHalamanLogin()
    {
        return view('auth.login');
    }

    // login(email, kataSandi)
    public function login(Request $request)
    {
        $email = trim((string) $request->input('email'));
        $kataSandi = (string) $request->input('kata_sandi');

        // validasiKelengkapan(email, kataSandi)
        $status = $this->validasiKelengkapan($email, $kataSandi);
        if ($status !== 'valid') {
            return back()->withInput($request->only('email'))->with('error', $status);
        }

        // Akun.getAkunByEmail(email)
        try {
            $dataAkun = User::where('email', $email)->first();
        } catch (\Throwable $e) {
            return back()->withInput($request->only('email'))
                ->with('error', 'Login tidak dapat dilakukan, silakan coba kembali');
        }

        // Akun belum terdaftar
        if (!$dataAkun) {
            return back()->withInput($request->only('email'))
                ->with('error', 'Akun belum terdaftar, silakan registrasi')
                ->with('tampilkanRegistrasi', true);
        }

        // cocokkanKataSandi(kataSandi, dataAkun)
        if (!$this->cocokkanKataSandi($kataSandi, $dataAkun)) {
            return back()->withInput($request->only('email'))
                ->with('error', 'Email atau kata sandi tidak sesuai');
        }

        // buatSesiLogin()
        Auth::login($dataAkun);
        $request->session()->regenerate();

        // getJenisAkun(email)
        $jenisAkun = $this->getJenisAkun($email);

        // arahkanKeHalamanUtama(jenisAkun)
        return $this->arahkanKeHalamanUtama($jenisAkun);
    }

    // validasiKelengkapan(email, kataSandi): mengembalikan "valid" atau pesan error
    public function validasiKelengkapan(string $email, string $kataSandi): string
    {
        if ($email === '' || $kataSandi === '') {
            return 'Email atau kata sandi wajib diisi';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid';
        }

        return 'valid';
    }

    // cocokkanKataSandi(kataSandi, dataAkun)
    public function cocokkanKataSandi(string $kataSandi, User $dataAkun): bool
    {
        return Hash::check($kataSandi, $dataAkun->password);
    }

    // getJenisAkun(email)
    public function getJenisAkun(string $email): string
    {
        $akun = User::where('email', $email)->first();

        return $akun->jenis_akun ?? 'Calon Penghuni';
    }

    // arahkanKeHalamanUtama(jenisAkun)
    public function arahkanKeHalamanUtama(string $jenisAkun)
    {
        // tampilkanHalamanUtama(jenisAkun)
        return match ($jenisAkun) {
            'Pemilik Kost' => redirect('/kost'),
            'Penghuni' => redirect('/penghuni'),
            default => redirect('/cari-kost'),
        };
    }

    // tampilkanOpsiRegistrasi()
    public function tampilkanOpsiRegistrasi()
    {
        return redirect()->route('register')->with('info', 'Silakan daftar akun baru');
    }

    // tampilkanPesan(pesan)
    public function tampilkanPesan(string $pesan)
    {
        return back()->with('error', $pesan);
    }
}
