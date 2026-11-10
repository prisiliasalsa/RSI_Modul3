@extends('layouts.ruang-pemilik')

@section('judul', 'Detail Pengajuan Sewa')
@section('heading', 'Detail Pengajuan Sewa')
@section('subjudul', 'Periksa data calon penghuni sebelum menyetujui atau menolak.')

@section('content')

@if (session('pesan'))
    <div class="alert alert-success">{{ session('pesan') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="detail-pengajuan">
    <p><strong>Kost:</strong> {{ $detail->namaKost }}</p>
    <p><strong>Kamar:</strong> {{ $detail->nomorKamar }}</p>
    <p><strong>Harga sewa:</strong> Rp {{ number_format($detail->hargaSewa, 0, ',', '.') }}</p>
    <p><strong>Tanggal pengajuan:</strong> {{ $detail->tanggalPengajuan }}</p>
    <p><strong>Tanggal mulai sewa:</strong> {{ $detail->tanggalMulaiSewa }}</p>
    <p><strong>Durasi sewa:</strong> {{ $detail->durasiSewa }} bulan</p>
    <p><strong>Jumlah penghuni:</strong> {{ $detail->jumlahPenghuni }}</p>
    <p><strong>Catatan:</strong> {{ $detail->catatan ?? '-' }}</p>
    <p><strong>Berkas KTP:</strong> {{ $detail->berkasKTP ?? 'Belum diunggah' }}</p>
    <p><strong>Status:</strong> {{ $detail->status }}</p>

    @if ($detail->status === 'Ditolak')
        <p><strong>Alasan penolakan:</strong> {{ $detail->alasanPenolakan }}</p>
    @endif
</div>

@if ($detail->status === 'Menunggu')
    <div class="aksi-pengajuan">
        <form method="POST" action="{{ route('verifikasi.setujui', $detail->idPengajuan) }}">
            @csrf
            <button type="submit">Setujui</button>
        </form>

        <form method="POST" action="{{ route('verifikasi.tolak', $detail->idPengajuan) }}">
            @csrf
            <label for="alasan">Alasan penolakan</label>
            <textarea id="alasan" name="alasan" rows="3" required></textarea>
            @error('alasan')
                <small>{{ $message }}</small>
            @enderror
            <button type="submit">Tolak</button>
        </form>
    </div>
@endif

<p><a href="{{ route('verifikasi.daftar') }}">Kembali ke daftar</a></p>

@endsection