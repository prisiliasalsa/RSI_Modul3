
@extends('keuangan.layout')

@section('content')

{{-- PILIH KOST --}}
<form
    action="{{ route('pemilik.keuangan.index') }}"
    method="GET"
    class="finance-filter"
>
    <label for="kost">
        Pilih kost
    </label>

    <select
        id="kost"
        name="kost"
        onchange="this.form.submit()"
        @disabled($daftarKost->isEmpty())
    >
        @forelse ($daftarKost as $kost)

            <option
                value="{{ $kost->idKost }}"
                @selected($idKost === (int) $kost->idKost)
            >
                {{ $kost->namaKost }}
            </option>

        @empty

            <option value="">
                Belum ada kost terdaftar
            </option>

        @endforelse
    </select>
</form>

@if (!$kostAktif || $jumlahTransaksi === 0)

    {{-- UI-15.02: DATA KOSONG --}}

    <section class="finance-empty" aria-live="polite">

        <div class="finance-empty-icon">
            @include('keluhan.pemilik.icon', [
                'name' => 'search',
                'size' => 34
            ])
        </div>

        <h2>Data tidak tersedia</h2>

        <p>
            Belum ada pembayaran yang tercatat untuk
            ditampilkan sebagai laporan keuangan.
        </p>

    </section>

@else

    {{-- UI-15.01: RINGKASAN KEUANGAN --}}

    <div class="finance-stats">

        <section class="finance-stat-card">

            <div class="finance-stat-label">
                @include('keluhan.pemilik.icon', [
                    'name' => 'wallet',
                    'size' => 18
                ])

                <span>Total pendapatan tercatat</span>
            </div>

            <strong>
                Rp{{ number_format(
                    $totalPendapatan,
                    0,
                    ',',
                    '.'
                ) }}
            </strong>

        </section>

        <section class="finance-stat-card">

            <div class="finance-stat-label">
                @include('keluhan.pemilik.icon', [
                    'name' => 'file',
                    'size' => 18
                ])

                <span>Transaksi lunas</span>
            </div>

            <strong>
                {{ $jumlahTransaksi }} transaksi
            </strong>

        </section>

    </div>

    {{-- DAFTAR TRANSAKSI --}}

    <section class="finance-transactions">

        <div class="finance-table-header">

            <h2>Daftar transaksi</h2>

            <form
                method="GET"
                action="{{ route('pemilik.keuangan.index') }}"
                class="finance-search"
            >
                <input
                    type="hidden"
                    name="kost"
                    value="{{ $idKost }}"
                >

                @include('keluhan.pemilik.icon', [
                    'name' => 'search',
                    'size' => 18
                ])

                <input
                    type="search"
                    name="cari"
                    value="{{ $cari }}"
                    maxlength="120"
                    placeholder="Cari data..."
                    aria-label="Cari transaksi"
                >

            </form>

        </div>

        <div class="finance-table-scroll">

            <table class="finance-table">

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Penghuni / kamar</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($transaksi as $row)

                        <tr>

                            <td class="finance-date">
                                {{ \Illuminate\Support\Carbon::parse(
                                    $row->tanggalTransaksi
                                )->locale('id')->translatedFormat('d M Y') }}
                            </td>

                            <td>
                                {{ $row->namaLengkap }}
                                ·
                                {{ $row->nomorKamar ?? '-' }}
                            </td>

                            <td>
                                @if ($row->periode)
                                    {{ \Illuminate\Support\Carbon::createFromFormat(
                                        '!Y-m',
                                        $row->periode
                                    )->locale('id')->translatedFormat('M Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                Rp{{ number_format(
                                    $row->nominal,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="finance-no-match">
                                Tidak ada transaksi yang sesuai dengan pencarian.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}

        <div class="finance-table-footer">

            <span>
                Menampilkan {{ $transaksi->count() }} data
            </span>

            <div class="finance-pages">

                @if ($transaksi->onFirstPage())
                    <span>‹</span>
                @else
                    <a
                        href="{{ $transaksi->previousPageUrl() }}"
                        aria-label="Halaman sebelumnya"
                    >
                        ‹
                    </a>
                @endif

                <span>
                    {{ $transaksi->currentPage() }}
                </span>

                @if ($transaksi->hasMorePages())
                    <a
                        href="{{ $transaksi->nextPageUrl() }}"
                        aria-label="Halaman selanjutnya"
                    >
                        ›
                    </a>
                @else
                    <span>›</span>
                @endif

            </div>

        </div>

    </section>

    <p class="finance-note">
        Ringkasan dihitung dari transaksi pembayaran
        yang diterima/lunas pada kost terpilih.
    </p>

    {{-- GRAFIK PENDAPATAN PER TANGGAL --}}

    <section class="finance-chart">

        <h2>Pendapatan per tanggal transaksi</h2>

        <p>
            Hanya pembayaran yang sudah diterima
        </p>

        @foreach ($grafik as $bar)

            @php
                $persentase = round(
                    ((float) $bar->total / $nilaiMaksimum) * 100,
                    2
                );
            @endphp

            <div class="finance-chart-row">

                <span class="finance-chart-date">
                    {{ \Illuminate\Support\Carbon::parse(
                        $bar->tanggalTransaksi
                    )->locale('id')->translatedFormat('d F Y') }}
                </span>

                <div class="finance-track">

                    <div
                        class="finance-bar"
                        style="width: {{ $persentase }}%"
                    ></div>

                </div>

                <strong>
                    Rp{{ number_format(
                        $bar->total,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

        @endforeach

    </section>

@endif

@endsection
