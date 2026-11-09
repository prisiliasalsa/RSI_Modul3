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
            ['kunci' => 'pengajuan', 'ikon' => 'file', 'label' => 'Pengajuan Sewa', 'route' => null],
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
</head>

<body class="rp-body">

<div class="rp-shell">

    {{-- SIDEBAR PEMILIK KOST --}}
    <aside class="rp-sidebar">

        <div class="rp-logo">
            @include('keluhan.pemilik.icon', ['name' => 'home', 'size' => 22])
            <strong>kost.io</strong>
        </div>

        <div class="rp-workspace">
            <small>RUANG PEMILIK</small>
            <span>Kelola hunianmu</span>
        </div>

        <nav class="rp-nav" aria-label="Navigasi pemilik">
            @foreach ($menu as $item)
                @php
                    $aktif = ($menuAktif ?? '') === $item['kunci'];
                    $adaRoute = $item['route']
                        && \Illuminate\Support\Facades\Route::has($item['route']);
                @endphp

                @if ($adaRoute)
                    <a
                        class="rp-nav-link {{ $aktif ? 'selected' : '' }}"
                        href="{{ route($item['route']) }}"
                        @if ($aktif) aria-current="page" @endif
                    >
                        @include('keluhan.pemilik.icon', ['name' => $item['ikon']])
                        <span>{{ $item['label'] }}</span>
                    </a>
                @else
                    <span class="rp-nav-link {{ $aktif ? 'selected' : '' }}">
                        @include('keluhan.pemilik.icon', ['name' => $item['ikon']])
                        <span>{{ $item['label'] }}</span>
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="rp-spacer"></div>

        <div class="rp-logout">
            @include('keluhan.pemilik.icon', ['name' => 'logout'])
            Keluar
        </div>

    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="rp-main-wrap">

        <header class="rp-topbar">
            <div class="rp-crumb-top">
                Kost.io <span>/</span> Pemilik Kost
            </div>

            <div class="rp-account">
                <button type="button" class="rp-icon-button" aria-label="Notifikasi">
                    @include('keluhan.pemilik.icon', ['name' => 'bell', 'size' => 22])
                </button>

                <div class="rp-avatar" aria-hidden="true">{{ $inisial ?: 'PK' }}</div>

                <div class="rp-account-name">
                    <strong>{{ $namaPemilik }}</strong>
                    <small>Pemilik Kost</small>
                </div>
            </div>
        </header>

        <main class="rp-main">

            <div class="rp-breadcrumb">@yield('breadcrumb')</div>

            <div class="rp-heading">
                <h1>@yield('heading')</h1>
                <span class="rp-kost-tag">{{ $namaKostHeader }}</span>
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
