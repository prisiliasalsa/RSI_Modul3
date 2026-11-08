@extends('layouts.app')

@section('title', 'Konfirmasi Hapus Kamar')

@section('isi')
    <div class="breadcrumb">Beranda / Mengelola Data Kamar</div>
    <h1>Konfirmasi hapus kamar</h1>
    <p class="deskripsi">Kelola detail, harga, dan ketersediaan setiap kamar.</p>

    <div class="kartu">
        <div style="background: #e3d9c9; padding: 16px; border-radius: 6px; margin-bottom: 20px;">
            <strong>Hapus kamar {{ $kamar->nomor_kamar }}?</strong><br>
            Data kamar akan dihapus. Lanjutkan tindakan?
        </div>

        <form action="{{ route('kamar.destroy', [$idKost, $kamar->id_kamar]) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-hapus">Ya, Hapus</button>
        </form>
        <a href="{{ route('kamar.index', $idKost) }}" class="btn">Batal</a>
    </div>
@endsection