<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Keluhan') | Kost.io</title>

    <link rel="stylesheet"
          href="{{ asset('assets/kostio/keluhan.css') }}">

    <script src="{{ asset('assets/kostio/keluhan.js') }}"
            defer></script>
</head>

<body>
<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            @include('keluhan.partials.icon', [
                'name' => 'home', 'size' => 22
            ])
            <strong>kost.io</strong>
        </div>

        <div class="workspace">
            <small>RUANG PENGHUNI</small>
            <span>Temukan nyamannya</span>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-item muted">
                @include('keluhan.partials.icon', [
                    'name' => 'wallet'
                ])
                Tagihan Sewa
            </span>

            <a href="{{ route('keluhan.status') }}"
               class="nav-item {{ request()->routeIs('keluhan.*') ? 'active' : '' }}">
                @include('keluhan.partials.icon', [
                    'name' => 'chat'
                ])
                Keluhan Saya
            </a>

            <a href="{{ route('keluhan.ajukan') }}"
               class="nav-item nav-subitem {{ request()->routeIs('keluhan.ajukan') ? 'selected' : '' }}">
                Ajukan keluhan
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-help">
                <strong>Butuh bantuan?</strong>
                <p>Kelola tempat tinggal dengan lebih tenang.</p>
            </div>

            <div class="nav-item muted nav-footer">
                <span class="signout-dot"></span>
                Keluar
            </div>
        </div>
    </aside>

    <!-- KONTEN -->
    <div class="main-panel">

        <header class="topbar">
            <span class="topbar-path">
                Kost.io / Penghuni
            </span>

            <div class="topbar-user">
                <span class="bell">
                    @include('keluhan.partials.icon', [
                        'name' => 'bell',
                        'size' => 21
                    ])
                </span>

                @php
                    $nama = trim(
                        (string) ($penghuni->namaLengkap ?? 'Penghuni')
                    );

                    $inisial = implode('', array_map(
                        fn($kata) => mb_substr($kata, 0, 1),
                        array_slice(preg_split('/\s+/', $nama), 0, 2)
                    ));
                @endphp

                <span class="avatar">
                    {{ strtoupper($inisial ?: 'P') }}
                </span>

                <span class="user-text">
                    <strong>{{ $nama }}</strong>
                    <small>Penghuni</small>
                </span>
            </div>
        </header>

        <main class="page-content">
            <div class="breadcrumb">
                BERANDA / MENGAJUKAN KELUHAN / KERUSAKAN
            </div>

            <div class="page-heading">
                <h1>
                    @yield('heading', 'Ajukan keluhan / kerusakan')
                </h1>

                <span class="tag tag-brand">Kost.io</span>
            </div>

            <p class="page-lead">
                @yield(
                    'subtitle',
                    'Sampaikan kendala agar pemilik dapat segera menindaklanjuti.'
                )
            </p>

            <div class="content-grid">
                <section class="main-card">
                    @yield('content')
                </section>

                @include('keluhan.partials.hunian')
            </div>
        </main>
    </div>
</div>
</body>
</html>php artisan optimize:clear