{{-- <<boundary>> HalamanKelolaKost: UI-09.03 Publikasi kost --}}
@extends('kost._kerangka')

@section('judul', $heading)
@section('heading', $heading)

@section('konten')

    @if ($galat)
        <div class="banner" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
            {{ $galat }}
        </div>
    @endif

    <div class="ringkas">
        <strong>{{ $judulKartu }}</strong>
        <span>{{ $isiKartu }}</span>
    </div>

    <div class="aksi-kolom">
        @if ($sudah)
            <a class="btn btn-utama" href="{{ $urlDaftar }}">Kembali ke daftar kost</a>
        @else
            <form method="post" action="{{ $urlPublikasikan }}">
                @csrf
                <button type="submit" class="btn btn-utama">Publikasikan Kost</button>
            </form>
        @endif

        <a class="btn btn-utama" href="{{ $urlKamar }}">Tambah kamar</a>
    </div>

@endsection