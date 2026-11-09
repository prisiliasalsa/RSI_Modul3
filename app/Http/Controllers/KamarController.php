<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KamarController extends Controller
{
    // UC-10: Menampilkan daftar kamar
    public function bukaDaftarKamar($idKost)
    {
        $kost = $this->untukTampilanKost(
            Kost::findOrFail($idKost)
        );

        $daftarKamar = Kamar::where('idKost', $idKost)
            ->get()
            ->map(fn (Kamar $kamar) =>
                $this->untukTampilanKamar($kamar)
            );

        return view(
            'kamar.index',
            compact('kost', 'daftarKamar')
        );
    }

    // UC-10: Membuka formulir tambah kamar
    public function bukaFormKamar($idKost)
    {
        $kost = $this->untukTampilanKost(
            Kost::findOrFail($idKost)
        );

        return view('kamar.form', [
            'kost' => $kost,
            'kamar' => null
        ]);
    }

    // UC-10: Membuka formulir ubah kamar
    public function bukaFormUbah($idKost, $idKamar)
    {
        $kost = $this->untukTampilanKost(
            Kost::findOrFail($idKost)
        );

        $kamar = $this->untukTampilanKamar(
            $this->cariKamar($idKost, $idKamar)
        );

        return view(
            'kamar.form',
            compact('kost', 'kamar')
        );
    }

    // UC-10: Membuka konfirmasi penghapusan
    public function hapusKamar($idKost, $idKamar)
    {
        Kost::findOrFail($idKost);

        $kamar = $this->cariKamar(
            $idKost,
            $idKamar
        );

        if ($kamar->statusKamar === 'Terisi') {
            return redirect()
                ->route('kamar.index', $idKost)
                ->with(
                    'error',
                    'Kamar sedang terisi dan tidak dapat dihapus'
                );
        }

        $kamar = $this->untukTampilanKamar($kamar);

        return view(
            'kamar.konfirmasi-hapus',
            compact('kamar', 'idKost')
        );
    }

    // UC-10: Menghapus kamar
    public function konfirmasiHapus($idKost, $idKamar)
    {
        Kost::findOrFail($idKost);

        $kamar = $this->cariKamar(
            $idKost,
            $idKamar
        );

        // Periksa kembali sebelum menghapus
        if ($kamar->statusKamar === 'Terisi') {
            return redirect()
                ->route('kamar.index', $idKost)
                ->with(
                    'error',
                    'Kamar sedang terisi dan tidak dapat dihapus'
                );
        }

        try {
            $kamar->delete();
        } catch (\Throwable $e) {
            return redirect()
                ->route('kamar.index', $idKost)
                ->with(
                    'error',
                    'Data kamar gagal dihapus, silakan coba kembali'
                );
        }

        return redirect()
            ->route('kamar.index', $idKost)
            ->with(
                'pesan',
                'Data kamar berhasil dihapus'
            );
    }

    // UC-10: Menambah atau mengubah kamar
    public function simpanKamar(
        Request $request,
        $idKost,
        $idKamar = null
    ) {
        Kost::findOrFail($idKost);

        $kamar = $idKamar
            ? $this->cariKamar($idKost, $idKamar)
            : null;

        $pesanValidasi = $this->validasiKamar(
            $request,
            $idKost,
            $kamar
        );

        if ($pesanValidasi !== null) {
            return back()
                ->withInput()
                ->with('error', $pesanValidasi);
        }

        // Sesuaikan nama input dengan kolom database
        $data = [
            'idKost' => $idKost,
            'nomorKamar' => trim(
                (string) $request->input('nomor_kamar')
            ),
            'tipeKamar' => trim(
                (string) $request->input('tipe_kamar')
            ),
            'ukuranKamar' => trim(
                (string) $request->input('ukuran_kamar')
            ),
            'hargaSewa' => $request->input('harga_sewa'),
            'fasilitasKamar' => $request->input(
                'fasilitas_kamar'
            ),
        ];

        $fotoBaru = null;

        try {
            // Upload foto kamar jika tersedia
            if ($request->hasFile('foto_kamar')) {
                $fotoBaru = $request
                    ->file('foto_kamar')
                    ->store('kamar', 'public');

                $data['fotoKamar'] = $fotoBaru;
            }

            if ($kamar !== null) {
                // Memperbarui kamar lama
                $kamar->update($data);
            } else {
                // Kamar baru berstatus Tersedia
                $data['statusKamar'] = 'Tersedia';

                // Membutuhkan idKamar AUTO_INCREMENT
                Kamar::create($data);
            }

        } catch (\Throwable $e) {
            // Bersihkan foto jika penyimpanan gagal
            if ($fotoBaru !== null) {
                Storage::disk('public')
                    ->delete($fotoBaru);
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Data kamar gagal disimpan, silakan coba kembali'
                );
        }

        return redirect()
            ->route('kamar.index', $idKost)
            ->with(
                'pesan',
                'Data kamar berhasil disimpan'
            );
    }

    // UC-10: Validasi data kamar
    private function validasiKamar(
        Request $request,
        $idKost,
        ?Kamar $kamar = null
    ): ?string {

        // Validasi field wajib
        $fieldWajib = [
            'nomor_kamar',
            'tipe_kamar',
            'ukuran_kamar',
            'harga_sewa'
        ];

        foreach ($fieldWajib as $field) {
            if (trim(
                (string) $request->input($field)
            ) === '') {
                return 'Form belum lengkap';
            }
        }

        // Validasi harga sewa
        $harga = $request->input('harga_sewa');

        if (
            !is_numeric($harga) ||
            (float) $harga < 0
        ) {
            return 'Harga sewa harus berupa angka yang tidak negatif';
        }

        // Periksa nomor kamar duplikat
        $nomorSudahAda = Kamar::where(
            'idKost',
            $idKost
        )
            ->where(
                'nomorKamar',
                $request->input('nomor_kamar')
            )
            ->when(
                $kamar !== null,
                fn ($query) => $query->where(
                    'idKamar',
                    '!=',
                    $kamar->idKamar
                )
            )
            ->exists();

        if ($nomorSudahAda) {
            return 'Nomor kamar sudah terdaftar';
        }

        // Batasi perubahan kamar yang sedang terisi
        if (
            $kamar !== null &&
            $kamar->statusKamar === 'Terisi'
        ) {
            if (
                $kamar->tipeKamar !==
                    $request->input('tipe_kamar') ||
                $kamar->ukuranKamar !==
                    $request->input('ukuran_kamar')
            ) {
                return 'Tipe dan ukuran tidak dapat diubah saat kamar terisi';
            }
        }

        // Validasi foto kamar
        if ($request->hasFile('foto_kamar')) {
            $foto = $request->file('foto_kamar');

            $ekstensi = strtolower(
                $foto->getClientOriginalExtension()
            );

            $tipeMime = $foto->getMimeType();

            if (
                !$foto->isValid() ||
                !in_array(
                    $ekstensi,
                    ['jpg', 'jpeg', 'png'],
                    true
                ) ||
                !in_array(
                    $tipeMime,
                    ['image/jpeg', 'image/png'],
                    true
                ) ||
                $foto->getSize() > 2 * 1024 * 1024
            ) {
                return 'File maksimal 2MB (JPG/PNG)';
            }
        }

        return null;
    }

    // Mencari kamar berdasarkan ID kamar dan kost
    private function cariKamar(
        $idKost,
        $idKamar
    ): Kamar {
        return Kamar::where('idKost', $idKost)
            ->where('idKamar', $idKamar)
            ->firstOrFail();
    }

    // Menyesuaikan nama atribut Kost untuk Blade lama
    private function untukTampilanKost(
        Kost $kost
    ): Kost {
        $kost->setAttribute(
            'id_kost',
            $kost->idKost
        );

        $kost->setAttribute(
            'nama_kost',
            $kost->namaKost
        );

        return $kost;
    }

    // Menyesuaikan nama atribut Kamar untuk Blade lama
    private function untukTampilanKamar(
        Kamar $kamar
    ): Kamar {
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
            $kamar->setAttribute(
                $namaView,
                $kamar->{$namaDatabase}
            );
        }

        return $kamar;
    }
}
