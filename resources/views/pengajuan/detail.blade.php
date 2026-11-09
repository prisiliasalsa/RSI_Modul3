{{-- <<boundary>> HalamanStatusPengajuan: UI-06.02 Detail keputusan pengajuan --}}
@extends('pengajuan._kerangka')

@section('judul', $heading)
@section('heading', $heading)
@section('jejak')
    <a href="{{ route('pengajuan.status', $qs) }}">MELIHAT STATUS PENGAJUAN</a>
@endsection

@section('konten')

    <div class="ringkas">
        <strong>{{ $ringkasan }}</strong>
        <span>{{ $rincian }}</span>
    </div>

    <div class="status-banner {{ $gaya }}" role="status">
        @if ($gaya === 'disetujui')
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>
        @endif
        <span>{{ $status }}@if ($keterangan) — {{ $keterangan }}@endif</span>
    </div>

    <div class="aksi">
        @if ($gaya === 'disetujui')
            <a class="btn btn-utama" href="{{ $urlTagihan }}">Lihat Tagihan Sewa</a>
        @elseif ($gaya === 'ditolak')
            <a class="btn btn-utama" href="{{ $urlCariKost }}">Cari kost lain</a>
            <a class="btn btn-garis" href="{{ route('pengajuan.status', $qs) }}">Kembali ke daftar</a>
        @else
            <a class="btn btn-utama" href="{{ route('pengajuan.status', $qs) }}">Kembali ke daftar</a>
        @endif
    </div>

@endsection