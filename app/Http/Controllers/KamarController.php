<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kost;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    // bukaDaftarKamar(idKost)
    public function bukaDaftarKamar($idKost)
    {
        $kost = Kost::findOrFail($idKost);
        $daftarKamar = Kamar::where('id_kost', $idKost)->get();

        return view('kamar.index', compact('kost', 'daftarKamar'));
    }

    // bukaFormKamar()
    public function bukaFormKamar($idKost)
    {
        $kost = Kost::findOrFail($idKost);

        return view('kamar.form', ['kost' => $kost, 'kamar' => null]);
    }

    // bukaFormUbah(idKamar)
    public function bukaFormUbah($idKost, $idKamar)
    {
        $kost = Kost::findOrFail($idKost);
        $kamar = Kamar::findOrFail($idKamar);

        return view('kamar.form', compact('kost', 'kamar'));
    }

    // hapusKamar(idKamar)
    public function hapusKamar($idKost, $idKamar)
    {
        $kamar = Kamar::findOrFail($idKamar);

        if ($kamar->status_kamar === 'Terisi') {
            return redirect()->route('kamar.index', $idKost)
                ->with('error', 'Kamar sedang terisi dan tidak dapat dihapus');
        }

        return view('kamar.konfirmasi-hapus', compact('kamar', 'idKost'));
    }

    // konfirmasiHapus(idKamar)
    public function konfirmasiHapus($idKost, $idKamar)
    {
        $kamar = Kamar::findOrFail($idKamar);

        try {
            $kamar->delete();
        } catch (\Throwable $e) {
            return redirect()->route('kamar.index', $idKost)
                ->with('error', 'Data kamar gagal dihapus, silakan coba kembali');
        }

        return redirect()->route('kamar.index', $idKost)
            ->with('pesan', 'Data kamar berhasil dihapus');
    }

    // simpanKamar(dataKamar, foto), dipakai untuk tambah dan ubah
    public function simpanKamar(Request $request, $idKost, $idKamar = null)
    {
        $pesanValidasi = $this->validasiKamar($request, $idKost, $idKamar);

        if ($pesanValidasi) {
            return back()->withInput()->with('error', $pesanValidasi);
        }

        $data = $request->only([
            'nomor_kamar', 'tipe_kamar', 'ukuran_kamar', 'harga_sewa', 'fasilitas_kamar',
        ]);
        $data['id_kost'] = $idKost;

        if ($request->hasFile('foto_kamar')) {
            $data['foto_kamar'] = $request->file('foto_kamar')->store('kamar', 'public');
        }

        try {
            if ($idKamar) {
                // Ubah: status kamar tidak diubah pemilik
                Kamar::findOrFail($idKamar)->update($data);
            } else {
                // Tambah: kamar baru selalu Tersedia
                $data['status_kamar'] = 'Tersedia';
                Kamar::create($data);
            }
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Data kamar gagal disimpan, silakan coba kembali');
        }

        return redirect()->route('kamar.index', $idKost)
            ->with('pesan', 'Data kamar berhasil disimpan');
    }

    // validasiKamar(dataKamar, foto): mengembalikan null jika valid, atau pesan error
    private function validasiKamar(Request $request, $idKost, $idKamar = null): ?string
    {
        foreach (['nomor_kamar', 'tipe_kamar', 'ukuran_kamar', 'harga_sewa'] as $wajib) {
            if (trim((string) $request->input($wajib)) === '') {
                return 'Form belum lengkap';
            }
        }

        if (!is_numeric($request->input('harga_sewa')) || $request->input('harga_sewa') < 0) {
            return 'Form belum lengkap';
        }

        $nomorSudahAda = Kamar::where('id_kost', $idKost)
            ->where('nomor_kamar', $request->input('nomor_kamar'))
            ->when($idKamar, fn ($q) => $q->where('id_kamar', '!=', $idKamar))
            ->exists();

        if ($nomorSudahAda) {
            return 'Nomor kamar sudah terdaftar';
        }

        if ($idKamar) {
            $kamarLama = Kamar::find($idKamar);

            if ($kamarLama && $kamarLama->status_kamar === 'Terisi'
                && ($kamarLama->tipe_kamar !== $request->input('tipe_kamar')
                    || $kamarLama->ukuran_kamar !== $request->input('ukuran_kamar'))) {
                return 'Tipe dan ukuran tidak dapat diubah saat kamar terisi';
            }
        }

        if ($request->hasFile('foto_kamar')) {
            $foto = $request->file('foto_kamar');
            $ekstensi = strtolower($foto->getClientOriginalExtension());

            if (!in_array($ekstensi, ['jpg', 'jpeg', 'png']) || $foto->getSize() > 2 * 1024 * 1024) {
                return 'File maksimal 2MB (JPG/PNG)';
            }
        }

        return null;
    }
}