@extends('keluhan.pemilik.layout')

@section('title', 'Keluhan / Kerusakan')
@section('heading', 'Keluhan / kerusakan')

@section('content')

{{-- KARTU RINGKASAN --}}
<div class="owner14-stats">

    <div class="owner14-stat-card">

        <div class="owner14-stat-label">
            @include('keluhan.pemilik.icon', [
                'name' => 'wallet',
                'size' => 18
            ])
            <span>Keluhan diajukan</span>
        </div>

        <div class="owner14-stat-number">
            {{ (int) ($ringkasan->diajukan ?? 0) }} laporan
        </div>

    </div>

    <div class="owner14-stat-card">

        <div class="owner14-stat-label">
            @include('keluhan.pemilik.icon', [
                'name' => 'file',
                'size' => 18
            ])
            <span>Sedang diproses</span>
        </div>

        <div class="owner14-stat-number">
            {{ (int) ($ringkasan->diproses ?? 0) }} laporan
        </div>

    </div>

</div>

{{-- TABEL LAPORAN --}}
<section class="owner14-table-card">

    <div class="owner14-table-toolbar">

        <h2>Daftar keluhan</h2>

        <form
            method="GET"
            action="{{ route('pemilik.keluhan.daftar') }}"
            class="owner14-search"
        >
            @include('keluhan.pemilik.icon', [
                'name' => 'search',
                'size' => 19
            ])

            <input
                type="search"
                name="cari"
                value="{{ $cari }}"
                placeholder="Cari data..."
                aria-label="Cari data keluhan"
                maxlength="120"
            >
        </form>

    </div>

    <div class="owner14-table-responsive">

        <table class="owner14-table">

            <thead>
                <tr>
                    <th>Laporan</th>
                    <th>Penghuni / kamar</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($laporan as $item)

                    <tr>

                        <td>
                            <a
                                href="{{ route('pemilik.keluhan.detail', $item->idKeluhan) }}"
                                class="owner14-report-link"
                            >
                                KLH-{{ str_pad($item->idKeluhan, 3, '0', STR_PAD_LEFT) }}
                                ·
                                {{ $item->judulKeluhan }}
                            </a>
                        </td>

                        <td>
                            {{ $item->namaLengkap }}
                            ·
                            {{ $item->nomorKamar ?? '-' }}
                        </td>

                        <td>
                            {{ \Illuminate\Support\Carbon::parse(
                                $item->tanggalPengajuan
                            )->locale('id')->translatedFormat('d M Y') }}
                        </td>

                        <td>
                            <span class="owner14-status owner14-status-{{ strtolower($item->status) }}">
                                {{ $item->status }}
                            </span>
                        </td>

                        <td>
                            <a
                                href="{{ route('pemilik.keluhan.detail', $item->idKeluhan) }}"
                                class="owner14-detail-link"
                            >
                                Lihat detail
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="owner14-empty">
                            @if ($cari !== '')
                                Tidak ditemukan laporan yang sesuai pencarian.
                            @else
                                Belum ada laporan keluhan atau kerusakan.
                            @endif
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- FOOTER TABEL --}}
    <div class="owner14-table-footer">

        <span>
            Menampilkan {{ $laporan->count() }} data
        </span>

        <div class="owner14-pagination">

            @if ($laporan->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a
                    href="{{ $laporan->previousPageUrl() }}"
                    aria-label="Halaman sebelumnya"
                >
                    ‹
                </a>
            @endif

            <span class="current">
                {{ $laporan->currentPage() }}
            </span>

            @if ($laporan->hasMorePages())
                <a
                    href="{{ $laporan->nextPageUrl() }}"
                    aria-label="Halaman berikutnya"
                >
                    ›
                </a>
            @else
                <span class="disabled">›</span>
            @endif

        </div>

    </div>

</section>

{{-- TOMBOL DI BAWAH TABEL SEPERTI FIGMA --}}
@if ($laporan->isNotEmpty())

    <a
        href="{{ route('pemilik.keluhan.detail', $laporan->first()->idKeluhan) }}"
        class="owner14-btn-primary owner14-bottom-button"
    >
        Lihat KLH-{{ str_pad($laporan->first()->idKeluhan, 3, '0', STR_PAD_LEFT) }}
    </a>

@endif

@endsection
