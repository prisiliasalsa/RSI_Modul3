@extends('tagihan.layout')
@section('title', 'Bukti pembayaran terkirim')
@section('heading', 'Bukti pembayaran terkirim')
@section('content')
{{-- UI-07.04: Bukti pembayaran terkirim --}}
<div class="success-icon">
    @include('tagihan.partials.icon', ['name' => 'check', 'size' => 32])
</div>

<div class="alert alert-info" role="status">
    @include('tagihan.partials.icon', ['name' => 'bell'])
    <span>{{ $pembayaran->statusPembayaran }}</span>
</div>

<div class="info-box">
    <strong>Bukti berhasil dikirim</strong>
    <p>
        Pemilik kost akan memeriksa kesesuaian nominal dan bukti pembayaran.
    </p>
</div>

<a class="btn btn-secondary" href="{{ route('tagihan.daftar') }}">
    Kembali ke Tagihan
</a>
@endsection
