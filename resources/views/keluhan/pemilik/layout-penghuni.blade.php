
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title', 'Status Keluhan Saya') | Kost.io
    </title>

    <link rel="stylesheet"
          href="{{ asset('assets/kostio/uc14.css') }}">
</head>

<body class="owner14">

<div class="owner14-shell">

    <aside class="owner14-sidebar">

        <div class="owner14-brand">
            @include('keluhan.pemilik.icon', [
                'name' => 'home',
                'size' => 22
            ])
            <strong>kost.io</strong>
        </div>

        <div class="owner14-workspace">
            <small>RUANG PENGHUNI</small>
            <span>Temukan nyamannya</span>
        </div>

        <nav class="owner14-nav">

            <span class="owner14-nav-item owner14-nav-pending">
                @include('keluhan.pemilik.icon', [
                    'name' => 'wallet'
                ])
                <span>Tagihan Sewa</span>
            </span>

            @if ($pratinjau)

                <span class="owner14-nav-item is-active">
                    @include('keluhan.pemilik.icon', [
                        'name' => 'chat'
                    ])
                    <span>Keluhan Saya</span>
                </span>

            @else

                <a
                    href="{{ route('keluhan.status') }}"
                    class="owner14-nav-item is-active"
                >
                    @include('keluhan.pemilik.icon', [
                        'name' => 'chat'
                    ])
                    <span>Keluhan Saya</span>
                </a>

            @endif

        </nav>

        <div class="owner14-sidebar-spacer"></div>

        <div class="owner14-help">
            <strong>Butuh bantuan?</strong>
            <p>
                Kelola tempat tinggal dengan lebih tenang.
            </p>
        </div>

        <div class="owner14-nav-item owner14-logout">
            @include('keluhan.pemilik.icon', [
                'name' => 'logout'
            ])
            <span>Keluar</span>
        </div>

    </aside>

    <div class="owner14-workarea">

        <header class="owner14-topbar">

            <div class="owner14-path">
                Kost.io <span>/</span> Penghuni
            </div>

            <div class="owner14-account">

                <span class="owner14-bell">
                    @include('keluhan.pemilik.icon', [
                        'name' => 'bell',
                        'size' => 22
                    ])
                </span>

                @php
                    $namaPenghuni = trim(
                        (string) ($penghuni->namaLengkap ?? 'Penghuni')
                    );

                    $inisial = implode('', array_map(
                        fn ($kata) => mb_substr($kata, 0, 1),
                        array_slice(
                            preg_split('/\s+/', $namaPenghuni),
                            0,
                            2
                        )
                    ));
                @endphp

                <span class="owner14-avatar">
                    {{ strtoupper($inisial ?: 'P') }}
                </span>

                <span class="owner14-user">
                    <strong>{{ $namaPenghuni }}</strong>
                    <small>Penghuni</small>
                </span>

            </div>

        </header>

        <main class="owner14-content">

            <div class="owner14-breadcrumb">
                BERANDA / MELIHAT &amp; MENANGANI KELUHAN
            </div>

            <div class="owner14-heading">

                <h1>
                    @yield('heading', 'Status keluhan saya')
                </h1>

                <span class="owner14-kost-pill">
                    Kost.io
                </span>

            </div>

            <p class="owner14-subtitle">
                Tinjau laporan dan bantu penghuni menyelesaikan kendalanya.
            </p>

            @yield('content')

        </main>

    </div>

</div>

</body>
</html>
