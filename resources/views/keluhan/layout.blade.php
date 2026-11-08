
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Keluhan') | Kost.io</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @php
        // CSS khusus UC-08
        $cssPath = public_path('assets/kostio/keluhan.css');

        $cssVersion = is_file($cssPath)
            ? filemtime($cssPath)
            : 1;
    @endphp

    <link
        rel="stylesheet"
        href="{{ asset('assets/kostio/keluhan.css') }}?v={{ $cssVersion }}"
    >

    <script
        src="{{ asset('assets/kostio/keluhan.js') }}"
        defer
    ></script>
</head>

<body>

<div class="dashboard">

    {{-- SIDEBAR PENGHUNI --}}
    <aside class="sidebar">

        <div class="brand">
            @include('keluhan.partials.icon', [
                'name' => 'home',
                'size' => 20
            ])

            <strong>kost.io</strong>
        </div>

        <div class="workspace">
            <small>RUANG PENGHUNI</small>
            <span>Temukan nyamannya</span>
        </div>

        <nav class="sidebar-nav" aria-label="Menu penghuni">

            <span class="nav-item muted">
                @include('keluhan.partials.icon', [
                    'name' => 'wallet'
                ])

                <span>Tagihan Sewa</span>
            </span>

            <a
                href="{{ route('keluhan.status') }}"
                class="nav-item active"
            >
                @include('keluhan.partials.icon', [
                    'name' => 'chat'
                ])

                <span>Keluhan Saya</span>
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="sidebar-help">
                <strong>Butuh bantuan?</strong>

                <p>
                    Kelola tempat tinggal dengan lebih tenang.
                </p>
            </div>

            <div class="nav-footer">
                @include('keluhan.partials.icon', [
                    'name' => 'logout'
                ])

                <span>Keluar</span>
            </div>

        </div>

    </aside>

    {{-- AREA UTAMA --}}
    <div class="main-panel">

        {{-- HEADER --}}
        <header class="topbar">

            <div class="topbar-path">
                Kost.io <span>/</span> Penghuni
            </div>

            <div class="topbar-user">

                <span class="bell">
                    @include('keluhan.partials.icon', [
                        'name' => 'bell',
                        'size' => 22
                    ])
                </span>

                @php
                    $nama = trim(
                        (string) (
                            $penghuni->namaLengkap ?? 'Penghuni'
                        )
                    );

                    $kataNama = preg_split('/\s+/', $nama);

                    $inisial = implode('', array_map(
                        fn ($kata) => mb_substr($kata, 0, 1),
                        array_slice($kataNama, 0, 2)
                    ));
                @endphp

                <span class="avatar">
                    {{ strtoupper($inisial ?: 'P') }}
                </span>

                <div class="user-text">
                    <strong>{{ $nama }}</strong>
                    <small>Penghuni</small>
                </div>

            </div>

        </header>

        {{-- KONTEN HALAMAN --}}
        <main class="page-content">

            <div class="breadcrumb">
                BERANDA / MENGAJUKAN KELUHAN / KERUSAKAN
            </div>

            <div class="page-heading">

                <h1>
                    @yield(
                        'heading',
                        'Ajukan keluhan / kerusakan'
                    )
                </h1>

                <span class="tag tag-brand">
                    Kost.io
                </span>

            </div>

            <p class="page-lead">
                @yield(
                    'subtitle',
                    'Sampaikan kendala agar pemilik dapat segera menindaklanjuti.'
                )
            </p>

            <div class="content-grid">

                {{-- FORM / ISI UC-08 --}}
                <section class="main-card">
                    @yield('content')
                </section>

                {{-- INFORMASI KOST --}}
                @include('keluhan.partials.hunian')

            </div>

        </main>

    </div>

</div>

</body>
</html>
