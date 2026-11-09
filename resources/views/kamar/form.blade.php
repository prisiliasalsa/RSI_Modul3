@extends('layouts.app')

@php
    $ubah = isset($kamar) && $kamar;
    $terkunci = $ubah && $kamar->status_kamar === 'Terisi';
@endphp

@section('title', $ubah ? 'Ubah Kamar' : 'Tambah Kamar')

@section('isi')
    <div class="breadcrumb">Beranda / Mengelola Data Kamar</div>
    <h1>{{ $ubah ? 'Ubah kamar ' . $kamar->nomor_kamar : 'Tambah kamar' }}</h1>
    <p class="deskripsi">Kelola detail, harga, dan ketersediaan setiap kamar.</p>

    <div class="kartu">
        @if ($ubah)
            <form action="{{ route('kamar.update', [$kost->id_kost, $kamar->id_kamar]) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
        @else
            <form action="{{ route('kamar.store', $kost->id_kost) }}" method="POST" enctype="multipart/form-data">
        @endif
            @csrf

            <div class="baris">
                <div class="field">
                    <label for="nomor_kamar">Nomor kamar *</label>
                    <input type="text" id="nomor_kamar" name="nomor_kamar" value="{{ old('nomor_kamar', $kamar->nomor_kamar ?? '') }}" placeholder="Contoh: A03">
                    @error('nomor_kamar')<div class="error-teks">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="tipe_kamar">Tipe *</label>
                    @if ($terkunci)
                        <input type="text" value="{{ $kamar->tipe_kamar }}" readonly>
                        <input type="hidden" name="tipe_kamar" value="{{ $kamar->tipe_kamar }}">
                        <div class="hint">Tipe tidak dapat diubah saat kamar terisi.</div>
                    @else
                        <select id="tipe_kamar" name="tipe_kamar">
                            <option value="">Pilih tipe</option>
                            @foreach (['Standar', 'Premium'] as $tipe)
                                <option value="{{ $tipe }}" @selected(old('tipe_kamar', $kamar->tipe_kamar ?? '') === $tipe)>{{ $tipe }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('tipe_kamar')<div class="error-teks">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="ukuran_kamar">Ukuran *</label>
                    @if ($terkunci)
                        <input type="text" value="{{ $kamar->ukuran_kamar }}" readonly>
                        <input type="hidden" name="ukuran_kamar" value="{{ $kamar->ukuran_kamar }}">
                        <div class="hint">Ukuran tidak dapat diubah saat kamar terisi.</div>
                    @else
                        <input type="text" id="ukuran_kamar" name="ukuran_kamar" value="{{ old('ukuran_kamar', $kamar->ukuran_kamar ?? '') }}" placeholder="Contoh: 3 x 3 meter">
                    @endif
                    @error('ukuran_kamar')<div class="error-teks">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="harga_sewa">Harga / bulan *</label>
                    <input type="number" id="harga_sewa" name="harga_sewa" min="0" value="{{ old('harga_sewa', $kamar->harga_sewa ?? '') }}" placeholder="Contoh: 850000">
                    @error('harga_sewa')<div class="error-teks">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="fasilitas_kamar">Fasilitas</label>
                    <input type="text" id="fasilitas_kamar" name="fasilitas_kamar" value="{{ old('fasilitas_kamar', $kamar->fasilitas_kamar ?? '') }}" placeholder="Contoh: Kasur, lemari, meja">
                </div>

                <div class="field">
                    <label>Status</label>
                    <input type="text" value="{{ $ubah ? $kamar->status_kamar : 'Tersedia' }}" readonly>
                    <div class="hint">Status diatur otomatis oleh sistem.</div>
                </div>
            </div>

            <div class="field">
                <label for="foto_kamar">Foto kamar *</label>
                <input type="file" id="foto_kamar" name="foto_kamar" accept="image/jpeg,image/png">
                <div class="hint">Format JPG atau PNG, maksimal 2 MB</div>
                @if ($ubah && $kamar->foto_kamar)
                    <img src="{{ asset('storage/' . $kamar->foto_kamar) }}" alt="Foto kamar" class="foto-preview">
                @endif
                @error('foto_kamar')<div class="error-teks">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-utama">{{ $ubah ? 'Simpan perubahan' : 'Simpan kamar' }}</button>
                <a href="{{ route('kamar.index', $kost->id_kost) }}" class="btn">Batal</a>
            </div>
        </form>
    </div>
@endsection