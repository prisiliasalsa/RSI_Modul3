{{-- HalamanVerifikasiPembayaran: tampilDaftarPembayaran() / pesanBelumAdaPembayaran() (UI-13) --}}
@extends('layouts.ruang-pemilik', ['menuAktif' => 'verifikasi'])

@section('judul', 'Verifikasi Pembayaran')
@section('breadcrumb', 'BERANDA / MEMVERIFIKASI PEMBAYARAN')
@section('heading', 'Verifikasi pembayaran')
@section('subjudul', 'Cocokkan bukti pembayaran dengan tagihan penghuni.')

@section('content')

@if ($gangguan)

    <section class="rp-card rp-empty" role="alert">
        <h2>Terjadi gangguan data</h2>
        <p>Daftar pembayaran belum dapat dimuat. Silakan coba beberapa saat lagi.</p>
        <a class="rp-btn rp-btn-primary" href="{{ route('pemilik.verifikasi.daftar') }}">Muat ulang</a>
    </section>

@elseif ($jumlahMenunggu === 0)

    {{-- Alternative Flow 1 (2a): belum ada pembayaran --}}
    <section class="rp-card rp-empty" aria-live="polite">
        <div class="rp-empty-icon">
            @include('keluhan.pemilik.icon', ['name' => 'wallet', 'size' => 34])
        </div>
        <h2>Belum ada pembayaran yang perlu diverifikasi</h2>
        <p>Pembayaran yang dikirim penghuni akan muncul di halaman ini.</p>
    </section>

@else

    {{-- Panel ringkasan --}}
    <div class="rp-stats">
        <section class="rp-card rp-stat">
            <div class="rp-stat-label">
                @include('keluhan.pemilik.icon', ['name' => 'wallet', 'size' => 18])
                <span>Perlu diverifikasi</span>
            </div>
            <strong>{{ $jumlahMenunggu }} pembayaran</strong>
        </section>

        <section class="rp-card rp-stat">
            <div class="rp-stat-label">
                @include('keluhan.pemilik.icon', ['name' => 'file', 'size' => 18])
                <span>Nominal diajukan</span>
            </div>
            <strong>{{ $totalNominal }}</strong>
        </section>
    </div>

    {{-- Tabel daftar pembayaran --}}
    <section class="rp-card rp-table-card">

        <div class="rp-table-header">
            <h2>Daftar pembayaran</h2>

            <form method="GET" action="{{ route('pemilik.verifikasi.daftar') }}" class="rp-search" role="search">
                @include('keluhan.pemilik.icon', ['name' => 'search', 'size' => 18])
                <input
                    type="search"
                    name="cari"
                    value="{{ $cari }}"
                    maxlength="120"
                    placeholder="Cari data..."
                    aria-label="Cari pembayaran"
                >
            </form>
        </div>

        <div class="rp-table-scroll">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Penghuni / kamar</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $baris)
                        <tr>
                            <td class="rp-strong">
                                {{ $baris->namaLengkap }} · {{ $baris->nomorKamar }}
                                <small class="rp-cell-sub">Dikirim {{ $baris->tanggalLabel }}</small>
                            </td>
                            <td>{{ $baris->periodeSingkat }}</td>
                            <td>{{ $baris->nominalRupiah }}</td>
                            <td>
                                <span class="rp-status {{ $baris->statusKelas }}">
                                    {{ $baris->statusPembayaran }}
                                </span>
                            </td>
                            <td>
                                <a
                                    class="rp-link"
                                    href="{{ route('pemilik.verifikasi.detail', $baris->idPembayaran) }}"
                                >Lihat detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="rp-no-match">
                                Tidak ada pembayaran yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('layouts.partials.paginasi', ['data' => $daftar])

    </section>

@endif

@endsection
