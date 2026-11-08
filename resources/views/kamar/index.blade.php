@extends('layouts.app')

@section('title', 'Kelola Kamar')

@section('isi')
    <div class="breadcrumb">Beranda / Mengelola Data Kamar</div>
    <h1>Kelola kamar</h1>
    <p class="deskripsi">Kelola detail, harga, dan ketersediaan setiap kamar.</p>

    <div class="kartu-ringkas">
        <div class="ringkas">
            <div>Tersedia di daftar</div>
            <div class="angka">{{ $daftarKamar->where('status_kamar', 'Tersedia')->count() }} kamar</div>
        </div>
        <div class="ringkas">
            <div>Terisi di daftar</div>
            <div class="angka">{{ $daftarKamar->where('status_kamar', 'Terisi')->count() }} kamar</div>
        </div>
        <div class="ringkas">
            <div>Kost</div>
            <div class="angka">{{ $kost->nama_kost }}</div>
        </div>
    </div>

    <div class="kartu">
        <div style="margin-bottom: 14px;">
            <strong>Pilih kost:</strong> {{ $kost->nama_kost }}
        </div>

        <a href="{{ route('kamar.create', $kost->id_kost) }}" class="btn btn-utama">+ Tambah Kamar</a>

        <h3 style="margin-top: 24px;">Daftar kamar</h3>

        <table>
            <thead>
                <tr>
                    <th>Kamar / tipe</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarKamar as $kamar)
                    <tr>
                        <td>{{ $kamar->nomor_kamar }} · {{ $kamar->tipe_kamar }}</td>
                        <td>Rp{{ number_format($kamar->harga_sewa, 0, ',', '.') }}</td>
                        <td>
                            @if ($kamar->status_kamar === 'Terisi')
                                <span class="label label-terisi">Terisi</span>
                            @else
                                <span class="label label-tersedia">Tersedia</span>
                            @endif
                        </td>
                        <td class="aksi">
                            <a href="{{ route('kamar.edit', [$kost->id_kost, $kamar->id_kamar]) }}">Ubah</a>
                            <a href="{{ route('kamar.hapus', [$kost->id_kost, $kamar->id_kamar]) }}">Hapus</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada kamar pada kost ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection