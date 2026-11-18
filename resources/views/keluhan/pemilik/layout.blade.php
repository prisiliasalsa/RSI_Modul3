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
    @include('layouts.partials.kostio-ui-assets')
</head>

<body class="owner14 kio-ready kio-owner">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])

<div class="owner14-shell">

    {{-- SIDEBAR PEMILIK --}}
    <aside class="owner14-sidebar kio-sidebar">
        @yield('kioResidentSidebar')
    </aside>

    {{-- AREA KONTEN PEMILIK --}}
    <div class="owner14-workarea">

        {{-- TOPBAR --}}
        <header class="owner14-topbar kio-header">
            @yield('kioResidentHeader')
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
                @include('layouts.partials.lencana-kost-pemilik', ['namaLencana' => $namaKostHeader ?? null])

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
