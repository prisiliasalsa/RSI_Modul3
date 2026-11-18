{{--
    Layout Ruang Pemilik untuk UC-12 (Data Penghuni) dan
    UC-13 (Verifikasi Pembayaran).
    Variabel: $menuAktif ('penghuni' | 'verifikasi'), $pemilik,
    $namaKostHeader. Section: judul, breadcrumb, heading, subjudul, content.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('judul') | Kost.io</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @php
        $fileCss = public_path('assets/kostio/ruang-pemilik.css');
        $versiCss = file_exists($fileCss) ? filemtime($fileCss) : 1;

        // Menu sidebar pemilik; link aktif hanya jika route sudah ada
        $menu = [
            ['kunci' => 'kost', 'ikon' => 'home', 'label' => 'Kelola Kost', 'route' => 'kost.daftar'],
            ['kunci' => 'kamar', 'ikon' => 'key', 'label' => 'Kelola Kamar', 'route' => null],
            ['kunci' => 'pengajuan', 'ikon' => 'file', 'label' => 'Pengajuan Sewa', 'route' => 'pemilik.pengajuan-sewa.index'],   
            ['kunci' => 'penghuni', 'ikon' => 'users', 'label' => 'Data Penghuni', 'route' => 'pemilik.penghuni.daftar'],
            ['kunci' => 'verifikasi', 'ikon' => 'wallet', 'label' => 'Verifikasi Pembayaran', 'route' => 'pemilik.verifikasi.daftar'],
            ['kunci' => 'keluhan', 'ikon' => 'chat', 'label' => 'Keluhan / Kerusakan', 'route' => 'pemilik.keluhan.daftar'],
            ['kunci' => 'keuangan', 'ikon' => 'chart', 'label' => 'Laporan Keuangan', 'route' => 'pemilik.keuangan.index'],
        ];

        $namaPemilik = $pemilik->namaLengkap ?: 'Pemilik Kost';
        $inisial = collect(preg_split('/\s+/', trim($namaPemilik)))
            ->filter()
            ->take(2)
            ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->implode('');
    @endphp

    <link
        rel="stylesheet"
        href="{{ asset('assets/kostio/ruang-pemilik.css') }}?v={{ $versiCss }}"
    >
    @include('layouts.partials.kostio-ui-assets')
</head>

<body class="rp-body kio-ready kio-owner">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])

<div class="rp-shell">

    {{-- SIDEBAR PEMILIK KOST --}}
    <aside class="rp-sidebar kio-sidebar">
        @yield('kioResidentSidebar')
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="rp-main-wrap">

        <header class="rp-topbar kio-header">
            @yield('kioResidentHeader')
        </header>

        <main class="rp-main">

            <div class="rp-breadcrumb">@yield('breadcrumb')</div>

            <div class="rp-heading">
                <h1>@yield('heading')</h1>
                @include('layouts.partials.lencana-kost-pemilik', ['namaLencana' => $namaKostHeader])
            </div>

            <p class="rp-subtitle">@yield('subjudul')</p>

            {{-- tampilPesan(): notifikasi hasil proses --}}
            @if (session('pesanBerhasil'))
                <div class="rp-alert rp-alert-success" role="status">
                    @include('keluhan.pemilik.icon', ['name' => 'check', 'size' => 18])
                    {{ session('pesanBerhasil') }}
                </div>
            @endif

            @if (session('pesanGagal'))
                <div class="rp-alert rp-alert-error" role="alert">
                    {{ session('pesanGagal') }}
                </div>
            @endif

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
