<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Keluhan / Kerusakan') | Kost.io
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @php
        $cssPath = public_path('assets/kostio/uc14.css');

        $cssVersion = is_file($cssPath)
            ? filemtime($cssPath)
            : 1;
    @endphp

    <link
        rel="stylesheet"
        href="{{ asset('assets/kostio/uc14.css') }}?v={{ $cssVersion }}"
    >
</head>

<body class="owner14">

<div class="owner14-shell">

    {{-- SIDEBAR PEMILIK --}}
    <aside class="owner14-sidebar">

        {{-- LOGO --}}
        <div class="owner14-brand">
            @include('keluhan.pemilik.icon', [
                'name' => 'home',
                'size' => 22
            ])

            <strong>kost.io</strong>
        </div>

        {{-- INFORMASI RUANG PEMILIK --}}
        <div class="owner14-workspace">
            <small>RUANG PEMILIK</small>
            <span>Kelola hunianmu</span>
        </div>

        {{-- NAVIGASI PEMILIK --}}
        <nav class="owner14-nav" aria-label="Menu pemilik">

            {{-- UC-09: KELOLA KOST --}}
            <span class="owner14-nav-item">
                @include('keluhan.pemilik.icon', [
                    'name' => 'home'
                ])

                <span>Kelola Kost</span>
            </span>

            {{-- UC-10: KELOLA KAMAR --}}
            <span class="owner14-nav-item">
                @include('keluhan.pemilik.icon', [
                    'name' => 'key'
                ])

                <span>Kelola Kamar</span>
            </span>

            {{-- PENGAJUAN SEWA --}}
            <span class="owner14-nav-item">
                @include('keluhan.pemilik.icon', [
                    'name' => 'file'
                ])

                <span>Pengajuan Sewa</span>
            </span>

            {{-- DATA PENGHUNI --}}
            <span class="owner14-nav-item">
                @include('keluhan.pemilik.icon', [
                    'name' => 'users'
                ])

                <span>Data Penghuni</span>
            </span>

            {{-- UC-13: VERIFIKASI PEMBAYARAN --}}
            <span class="owner14-nav-item">
                @include('keluhan.pemilik.icon', [
                    'name' => 'wallet'
                ])

                <span>Verifikasi Pembayaran</span>
            </span>

            {{-- UC-14: KELUHAN / KERUSAKAN --}}
            <a
                href="{{ route('pemilik.keluhan.daftar') }}"
                class="owner14-nav-item active"
                aria-current="page"
            >
                @include('keluhan.pemilik.icon', [
                    'name' => 'chat'
                ])

                <span>Keluhan / Kerusakan</span>
            </a>

            {{-- UC-15: LAPORAN KEUANGAN --}}
            @if (\Illuminate\Support\Facades\Route::has('pemilik.keuangan.index'))

                <a
                    href="{{ route('pemilik.keuangan.index') }}"
                    class="owner14-nav-item"
                >
                    @include('keluhan.pemilik.icon', [
                        'name' => 'chart'
                    ])

                    <span>Laporan Keuangan</span>
                </a>

            @else

                <span class="owner14-nav-item">
                    @include('keluhan.pemilik.icon', [
                        'name' => 'chart'
                    ])

                    <span>Laporan Keuangan</span>
                </span>

            @endif

        </nav>

        {{-- JARAK KE BAGIAN BAWAH --}}
        <div class="owner14-sidebar-spacer"></div>

        {{-- KOTAK BANTUAN --}}
        <div class="owner14-help">
            <strong>Butuh bantuan?</strong>

            <p>
                Kelola tempat tinggal dengan lebih tenang.
            </p>
        </div>

        {{-- MENU KELUAR --}}
        {{-- Belum terhubung dengan UC-02 Login --}}
        <div class="owner14-logout">
            @include('keluhan.pemilik.icon', [
                'name' => 'logout'
            ])

            <span>Keluar</span>
        </div>

    </aside>

    {{-- AREA KONTEN PEMILIK --}}
    <div class="owner14-workarea">

        {{-- TOPBAR --}}
        <header class="owner14-topbar">

            <div class="owner14-topbar-path">
                Kost.io <span>/</span> Pemilik Kost
            </div>

            <div class="owner14-account">

                {{-- IKON NOTIFIKASI --}}
                <span class="owner14-bell">
                    @include('keluhan.pemilik.icon', [
                        'name' => 'bell',
                        'size' => 22
                    ])
                </span>

                {{-- AVATAR --}}
                <span class="owner14-avatar">
                    PK
                </span>

                {{-- INFORMASI AKUN --}}
                <div class="owner14-account-text">

                    <strong>
                        {{ $pemilik->namaLengkap ?? 'Pemilik Kost' }}
                    </strong>

                    <small>Pemilik Kost</small>

                </div>

            </div>

        </header>

        {{-- KONTEN UTAMA --}}
        <main class="owner14-content">

            {{-- BREADCRUMB --}}
            <div class="owner14-breadcrumb">
                BERANDA
                <span>/</span>
                MELIHAT &amp; MENANGANI KELUHAN
            </div>

            {{-- JUDUL HALAMAN --}}
            <div class="owner14-page-heading">

                <h1>
                    @yield(
                        'heading',
                        'Keluhan / kerusakan'
                    )
                </h1>

                {{-- NAMA KOST --}}
                <span class="owner14-kost-pill">
                    {{ $namaKostHeader ?? 'Kost.io' }}
                </span>

            </div>

            {{-- DESKRIPSI HALAMAN --}}
            <p class="owner14-description">
                Tinjau laporan dan bantu penghuni
                menyelesaikan kendalanya.
            </p>

            {{-- KONTEN DARI DAFTAR / DETAIL UC-14 --}}
            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
