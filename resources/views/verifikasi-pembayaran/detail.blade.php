{{--
    HalamanVerifikasiPembayaran (UI-13):
    - tampilDetailPembayaran()      : tanpa aksi
    - tampilKonfirmasi()            : ?aksi=terima
    - isiAlasanPenolakan()          : ?aksi=tolak
--}}
@extends('layouts.ruang-pemilik', ['menuAktif' => 'verifikasi'])

@php
    $judul = match ($aksi) {
        'terima' => 'Konfirmasi terima pembayaran',
        'tolak' => 'Alasan penolakan pembayaran',
        default => 'Detail pembayaran',
    };
    $urlDetail = route('pemilik.verifikasi.detail', $pembayaran->idPembayaran);
@endphp

@section('judul', $judul)
@section('breadcrumb', 'BERANDA / MEMVERIFIKASI PEMBAYARAN')
@section('heading', $judul)
@section('subjudul', 'Cocokkan bukti pembayaran dengan tagihan penghuni.')

@section('content')

<div class="rp-detail-grid">

    <section class="rp-card rp-detail-main">

        @if ($aksi === 'terima')

            {{-- Pop-up konfirmasi "Lanjutkan tindakan?" (langkah 9) --}}
            <div class="rp-info-box">
                <strong>Terima pembayaran ini?</strong>
                <p>
                    Lanjutkan tindakan? Pembayaran dan tagihan menjadi Lunas.
                    Transaksi dicatat dalam laporan keuangan.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('pemilik.verifikasi.terima', $pembayaran->idPembayaran) }}"
                class="rp-actions"
            >
                @csrf
                <button type="submit" class="rp-btn rp-btn-primary">Ya, Terima</button>
                <a href="{{ $urlDetail }}" class="rp-btn rp-btn-outline">Batal</a>
            </form>

        @elseif ($aksi === 'tolak')

            {{-- Pop-up input alasan penolakan (Alternative Flow 2) --}}
            <form
                method="POST"
                action="{{ route('pemilik.verifikasi.tolak', $pembayaran->idPembayaran) }}"
                novalidate
            >
                @csrf

                <label for="alasanPenolakan" class="rp-label">
                    Alasan penolakan <span aria-hidden="true">*</span>
                </label>

                <textarea
                    id="alasanPenolakan"
                    name="alasanPenolakan"
                    rows="3"
                    maxlength="1000"
                    class="rp-input @error('alasanPenolakan') rp-input-error @enderror"
                    placeholder="Contoh: Nominal pada bukti belum sesuai tagihan."
                    @error('alasanPenolakan') aria-invalid="true" aria-describedby="galatAlasan" @enderror
                    autofocus
                >{{ old('alasanPenolakan') }}</textarea>

                @error('alasanPenolakan')
                    <p id="galatAlasan" class="rp-error">{{ $message }}</p>
                @enderror

                <p class="rp-help">
                    Alasan wajib diisi. Penghuni menerima notifikasi dan dapat
                    memperbaiki bukti. Tagihan tetap Belum Lunas.
                </p>

                <div class="rp-actions">
                    <button type="submit" class="rp-btn rp-btn-primary">Kirim Penolakan</button>
                    <a href="{{ $urlDetail }}" class="rp-btn rp-btn-outline">Batal</a>
                </div>
            </form>

        @else

            {{-- Ringkasan tagihan (langkah 6) --}}
            <div class="rp-info-box">
                <strong>{{ $pembayaran->namaLengkap }} · Kamar {{ $pembayaran->nomorKamar }}</strong>
                <p>
                    Tagihan {{ $pembayaran->periodeLabel }} · {{ $pembayaran->tagihanRupiah }}<br>
                    Metode: {{ $pembayaran->metodeLabel }} · Dikirim {{ $pembayaran->tanggalLabel }}.
                </p>
            </div>

            {{-- Bukti pembayaran --}}
            <div class="rp-proof">
                <div class="rp-proof-head">
                    <div>
                        <small class="rp-muted">Nominal dibayar</small>
                        <p class="rp-price">{{ $pembayaran->nominalRupiah }}</p>
                    </div>
                    <span class="rp-status {{ $pembayaran->statusKelas }}">
                        {{ $pembayaran->statusPembayaran }}
                    </span>
                </div>

                @unless ($pembayaran->nominalSesuai)
                    <p class="rp-warning">
                        Nominal pembayaran berbeda dengan tagihan ({{ $pembayaran->tagihanRupiah }}).
                    </p>
                @endunless

                @if ($buktiTersedia)
                    @if ($pembayaran->buktiAdalahPdf)
                        <a
                            class="rp-btn rp-btn-outline"
                            href="{{ route('pemilik.verifikasi.bukti', $pembayaran->idPembayaran) }}"
                            target="_blank"
                            rel="noopener"
                        >Buka bukti pembayaran (PDF)</a>
                    @else
                        <a
                            href="{{ route('pemilik.verifikasi.bukti', $pembayaran->idPembayaran) }}"
                            target="_blank"
                            rel="noopener"
                            class="rp-proof-image"
                        >
                            <img
                                src="{{ route('pemilik.verifikasi.bukti', $pembayaran->idPembayaran) }}"
                                alt="Bukti pembayaran {{ $pembayaran->namaLengkap }} periode {{ $pembayaran->periodeLabel }}"
                            >
                        </a>
                    @endif
                @else
                    <p class="rp-muted">Berkas bukti pembayaran tidak ditemukan.</p>
                @endif

                @if ($pembayaran->statusPembayaran === 'Ditolak' && $pembayaran->alasanPenolakan)
                    <p class="rp-help">Alasan penolakan: {{ $pembayaran->alasanPenolakan }}</p>
                @endif
            </div>

            <div class="rp-actions">
                @if ($bisaDiverifikasi)
                    <a href="{{ $urlDetail }}?aksi=terima" class="rp-btn rp-btn-primary">Terima</a>
                    <a href="{{ $urlDetail }}?aksi=tolak" class="rp-btn rp-btn-outline">Tolak</a>
                @endif
                <a href="{{ route('pemilik.verifikasi.daftar') }}" class="rp-btn rp-btn-outline">Kembali</a>
            </div>

        @endif

    </section>

    @include('layouts.partials.kartu-kost', [
        'foto' => $pembayaran->fotoKamar ?: $pembayaran->fotoUtama,
        'namaKost' => $pembayaran->namaKost,
        'lokasi' => $pembayaran->lokasi,
        'nomorKamar' => $pembayaran->nomorKamar,
        'hargaSewa' => $pembayaran->hargaSewaRupiah,
    ])

</div>

@endsection
