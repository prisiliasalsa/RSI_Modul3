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
    @include('layouts.partials.kostio-ui-assets')
</head>

<body class="finance15 kio-ready kio-owner">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])

<div class="finance-shell">

    {{-- SIDEBAR PEMILIK --}}
    <aside class="finance-sidebar kio-sidebar">
        @yield('kioResidentSidebar')
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="finance-workspace-main">

        <header class="finance-topbar kio-header">
            @yield('kioResidentHeader')
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

                @include('layouts.partials.lencana-kost-pemilik', ['namaLencana' => $namaKostHeader])

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
