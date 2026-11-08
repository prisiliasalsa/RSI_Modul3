<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>UC-15 — Laporan Keuangan | Kost.io</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @php
        $fileCss = public_path('assets/kostio/keuangan.css');
        $versiCss = file_exists($fileCss)
            ? filemtime($fileCss)
            : 1;
    @endphp

    <link
        rel="stylesheet"
        href="{{ asset('assets/kostio/keuangan.css') }}?v={{ $versiCss }}"
    >
</head>

<body class="finance15">

<div class="finance-shell">

    {{-- SIDEBAR PEMILIK --}}
    <aside class="finance-sidebar">

        <div class="finance-logo">
            @include('keluhan.pemilik.icon', [
                'name' => 'home',
                'size' => 22
            ])

            <strong>kost.io</strong>
        </div>

        <div class="finance-workspace">
            <small>RUANG PEMILIK</small>
            <span>Kelola hunianmu</span>
        </div>

        <nav class="finance-nav" aria-label="Navigasi pemilik">

            <span class="finance-nav-link">
                @include('keluhan.pemilik.icon', ['name' => 'home'])
                Kelola Kost
            </span>

            <span class="finance-nav-link">
                @include('keluhan.pemilik.icon', ['name' => 'key'])
                Kelola Kamar
            </span>

            <span class="finance-nav-link">
                @include('keluhan.pemilik.icon', ['name' => 'file'])
                Pengajuan Sewa
            </span>

            <span class="finance-nav-link">
                @include('keluhan.pemilik.icon', ['name' => 'users'])
                Data Penghuni
            </span>

            <span class="finance-nav-link">
                @include('keluhan.pemilik.icon', ['name' => 'wallet'])
                Verifikasi Pembayaran
            </span>

            
            {{-- UC-14: Keluhan / Kerusakan --}}
            @if (\Illuminate\Support\Facades\Route::has('pemilik.keluhan.daftar'))

                <a
                    class="finance-nav-link"
                    href="{{ route('pemilik.keluhan.daftar') }}"
                >
                    @include('keluhan.pemilik.icon', [
                        'name' => 'chat'
                    ])

                    Keluhan / Kerusakan
                </a>

            @else

                <span class="finance-nav-link">
                    @include('keluhan.pemilik.icon', [
                        'name' => 'chat'
                    ])

                    Keluhan / Kerusakan
                </span>

            @endif


            <a
                class="finance-nav-link selected"
                href="{{ route('pemilik.keuangan.index') }}"
                aria-current="page"
            >
                @include('keluhan.pemilik.icon', ['name' => 'chart'])
                Laporan Keuangan
            </a>

        </nav>

        <div class="finance-spacer"></div>

        <div class="finance-help">
            <strong>Butuh bantuan?</strong>
            <p>Kelola tempat tinggal dengan lebih tenang.</p>
        </div>

        <div class="finance-logout">
            @include('keluhan.pemilik.icon', ['name' => 'logout'])
            Keluar
        </div>

    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="finance-workspace-main">

        <header class="finance-topbar">

            <div class="finance-crumb-top">
                Kost.io <span>/</span> Pemilik Kost
            </div>

            <div class="finance-account">

                @include('keluhan.pemilik.icon', [
                    'name' => 'bell',
                    'size' => 22
                ])

                <div class="finance-avatar">
                    PK
                </div>

                <div class="finance-account-name">
                    <strong>
                        {{ $pemilik->namaLengkap ?: 'Pemilik Kost' }}
                    </strong>
                    <small>Pemilik Kost</small>
                </div>

            </div>

        </header>

        <main class="finance-main">

            <div class="finance-breadcrumb">
                BERANDA / MELIHAT LAPORAN KEUANGAN KOST
            </div>

            <div class="finance-heading">

                <h1>
                    {{ $kostAktif && $jumlahTransaksi === 0
                        ? 'Laporan keuangan — data kosong'
                        : 'Laporan keuangan'
                    }}
                </h1>

                <span class="finance-kost-tag">
                    {{ $namaKostHeader }}
                </span>

            </div>

            <p class="finance-subtitle">
                Lihat pendapatan dan transaksi pembayaran kost yang tercatat.
            </p>

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
