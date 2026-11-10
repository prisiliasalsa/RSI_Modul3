<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KamarController extends Controller
{
    public function bukaDaftarKamar($idKost)
    {
        $kost = $this->untukTampilanKost(Kost::findOrFail($idKost));

        $daftarKamar = Kamar::where('idKost', $idKost)
            ->get()
            ->map(fn (Kamar $kamar) => $this->untukTampilanKamar($kamar));

        return view('kamar.index', compact('kost', 'daftarKamar'));
    }

    public function bukaFormKamar($idKost)
    {
        $kost = $this->untukTampilanKost(Kost::findOrFail($idKost));

        return view('kamar.form', [
            'kost' => $kost,
            'kamar' => null,
        ]);
    }

    public function bukaFormUbah($idKost, $idKamar)
    {
        $kost = $this->untukTampilanKost(Kost::findOrFail($idKost));
        $kamar = $this->untukTampilanKamar(
            $this->cariKamar($idKost, $idKamar)
        );

        return view('kamar.form', compact('kost', 'kamar'));
    }

    public function hapusKamar($idKost, $idKamar)
    {
        Kost::findOrFail($idKost);

        $kamar = $this->cariKamar($idKost, $idKamar);

        if ($kamar->statusKamar === 'Terisi') {
            return redirect()
                ->route('kamar.index', $idKost)
                ->with('error', 'Kamar sedang terisi dan tidak dapat dihapus');
        }

        $kamar = $this->untukTampilanKamar($kamar);

        return view('kamar.konfirmasi-hapus', compact('kamar', 'idKost'));
    }

    public function konfirmasiHapus($idKost, $idKamar)
    {
        Kost::findOrFail($idKost);

        $kamar = $this->cariKamar($idKost, $idKamar);

        if ($kamar->statusKamar === 'Terisi') {
            return redirect()
                ->route('kamar.index', $idKost)
                ->with('error', 'Kamar sedang terisi dan tidak dapat dihapus');
        }

        try {
            $kamar->delete();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('kamar.index', $idKost)
                ->with('error', 'Data kamar gagal dihapus, silakan coba kembali');
        }

        return redirect()
            ->route('kamar.index', $idKost)
            ->with('pesan', 'Data kamar berhasil dihapus');
    }

    public function simpanKamar(Request $request, $idKost, $idKamar = null)
    {
        Kost::findOrFail($idKost);

        $kamar = $idKamar === null
            ? null
            : $this->cariKamar($idKost, $idKamar);

        $pesanValidasi = $this->validasiKamar($request, $idKost, $kamar);

        if ($pesanValidasi !== null) {
            return back()
                ->withInput()
                ->with('error', $pesanValidasi);
        }

        $data = [
            'idKost' => $idKost,
            'nomorKamar' => trim((string) $request->input('nomor_kamar')),
            'tipeKamar' => trim((string) $request->input('tipe_kamar')),
            'ukuranKamar' => trim((string) $request->input('ukuran_kamar')),
            'hargaSewa' => $request->input('harga_sewa'),
            'fasilitasKamar' => $request->input('fasilitas_kamar'),
        ];

        $fotoBaru = null;

        try {
            if ($request->hasFile('foto_kamar')) {
                $fotoBaru = $request->file('foto_kamar')->store('kamar', 'public');

                if ($fotoBaru === false) {
                    throw new \RuntimeException('Foto kamar gagal disimpan.');
                }

                $data['fotoKamar'] = $fotoBaru;
            }

            if ($kamar !== null) {
                $kamar->update($data);
            } else {
                DB::transaction(function () use ($data) {
                    // Skema proyek menggunakan ID kamar yang diisi manual.
                    $data['idKamar'] = (int) DB::table('kamar')
                        ->lockForUpdate()
                        ->max('idKamar') + 1;

                    $data['statusKamar'] = 'Tersedia';

                    Kamar::create($data);
                }, 3);
            }
        } catch (\Throwable $e) {
            report($e);

            if (is_string($fotoBaru)) {
                Storage::disk('public')->delete($fotoBaru);
            }

            return back()
                ->withInput()
                ->with('error', 'Data kamar gagal disimpan, silakan coba kembali');
        }

        return redirect()
            ->route('kamar.index', $idKost)
            ->with('pesan', 'Data kamar berhasil disimpan');
    }

    private function validasiKamar(
        Request $request,
        $idKost,
        ?Kamar $kamar = null
    ): ?string {
        $fieldWajib = [
            'nomor_kamar',
            'tipe_kamar',
            'ukuran_kamar',
            'harga_sewa',
        ];

        foreach ($fieldWajib as $field) {
            if (trim((string) $request->input($field)) === '') {
                return 'Form belum lengkap';
            }
        }

        $harga = $request->input('harga_sewa');

        if (!is_numeric($harga) || (float) $harga < 0) {
            return 'Harga sewa harus berupa angka yang tidak negatif';
        }

        $nomorSudahAda = Kamar::where('idKost', $idKost)
            ->where('nomorKamar', trim((string) $request->input('nomor_kamar')))
            ->when(
                $kamar !== null,
                fn ($query) => $query->where('idKamar', '!=', $kamar->idKamar)
            )
            ->exists();

        if ($nomorSudahAda) {
            return 'Nomor kamar sudah terdaftar';
        }

        if ($kamar !== null && $kamar->statusKamar === 'Terisi') {
            if (
                $kamar->tipeKamar !== trim((string) $request->input('tipe_kamar')) ||
                $kamar->ukuranKamar !== trim((string) $request->input('ukuran_kamar'))
            ) {
                return 'Tipe dan ukuran tidak dapat diubah saat kamar terisi';
            }
        }

        if ($request->hasFile('foto_kamar')) {
            $foto = $request->file('foto_kamar');
            $ekstensi = strtolower($foto->getClientOriginalExtension());
            $tipeMime = $foto->getMimeType();

            if (
                !$foto->isValid() ||
                !in_array($ekstensi, ['jpg', 'jpeg', 'png'], true) ||
                !in_array($tipeMime, ['image/jpeg', 'image/png'], true) ||
                $foto->getSize() > 2 * 1024 * 1024
            ) {
                return 'File maksimal 2MB (JPG/PNG)';
            }
        }

        return null;
    }

    private function cariKamar($idKost, $idKamar): Kamar
    {
        return Kamar::where('idKost', $idKost)
            ->where('idKamar', $idKamar)
            ->firstOrFail();
    }

    private function untukTampilanKost(Kost $kost): Kost
    {
        $kost->setAttribute('id_kost', $kost->idKost);
        $kost->setAttribute('nama_kost', $kost->namaKost);

        return $kost;
    }

    private function untukTampilanKamar(Kamar $kamar): Kamar
    {
        $kolom = [
            'id_kamar' => 'idKamar',
            'id_kost' => 'idKost',
            'nomor_kamar' => 'nomorKamar',
            'tipe_kamar' => 'tipeKamar',
            'ukuran_kamar' => 'ukuranKamar',
            'harga_sewa' => 'hargaSewa',
            'fasilitas_kamar' => 'fasilitasKamar',
            'foto_kamar' => 'fotoKamar',
            'status_kamar' => 'statusKamar',
        ];

        foreach ($kolom as $namaView => $namaDatabase) {
            $kamar->setAttribute($namaView, $kamar->{$namaDatabase});
        }

        return $kamar;
    }
}