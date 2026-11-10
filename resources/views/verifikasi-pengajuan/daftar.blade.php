@extends('layouts.ruang-pemilik')

@section('judul', 'Pengajuan Sewa')
@section('heading', 'Pengajuan Sewa')
@section('subjudul', 'Verifikasi calon penghuni yang mengajukan sewa.')

@section('content')

@if (session('pesan'))
    <div class="alert alert-success">{{ session('pesan') }}</div>
@endif

@error('pengajuan')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

@if ($daftar->isEmpty())
    <p>Belum ada pengajuan sewa.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Nama Kost</th>
                <th>Kamar</th>
                <th>Tanggal Pengajuan</th>
                <th>Durasi</th>
                <th>Jumlah Penghuni</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($daftar as $item)
                <tr>
                    <td>{{ $item->namaKost }}</td>
                    <td>{{ $item->nomorKamar }}</td>
                    <td>{{ $item->tanggalPengajuan }}</td>
                    <td>{{ $item->durasiSewa }} bulan</td>
                    <td>{{ $item->jumlahPenghuni }}</td>
                    <td>
                        <a href="{{ route('verifikasi.detail', $item->idPengajuan) }}">Detail</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection