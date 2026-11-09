<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · Kost.io</title>
    <link rel="stylesheet" href="{{ asset('assets/kostio/tagihan.css') }}">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <a class="brand" href="{{ route('tagihan.daftar') }}">
            @include('tagihan.partials.icon', ['name' => 'home'])
            <span>kost.io</span>
        </a>
        <div class="sidebar-card">
            <small>RUANG PENGHUNI</small>
            <strong>Temukan nyamannya</strong>
        </div>
        <nav class="menu">
            <a class="menu-item active"
               href="{{ route('tagihan.daftar') }}">
                @include('tagihan.partials.icon', ['name' => 'wallet'])
                <span>Tagihan Sewa</span>
            </a>
            <a class="menu-item"
               href="{{ Route::has('keluhan.ajukan')
                    ? route('keluhan.ajukan') : '#' }}">
                @include('tagihan.partials.icon', ['name' => 'chat'])
                <span>Keluhan Saya</span>
            </a>
        </nav>
        <form class="logout" method="POST"
              action="{{ Route::has('logout') ? route('logout') : '#' }}">
            @csrf
            <button type="submit" class="menu-item">
                @include('tagihan.partials.icon', ['name' => 'logout'])
                <span>Keluar</span>
            </button>
        </form>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="crumb">Kost.io <span>/</span> Penghuni</div>
            <div class="topbar-user">
                <button type="button" class="icon-btn"
                        aria-label="Notifikasi">
                    @include('tagihan.partials.icon', ['name' => 'bell'])
                </button>
                <span class="avatar">
                    {{ mb_strtoupper(
                        collect(explode(' ', $penghuni->namaLengkap))
                            ->map(fn ($k) => mb_substr($k, 0, 1))
                            ->take(2)->implode('')
                    ) }}
                </span>
                <div class="user-info">
                    <strong>{{ $penghuni->namaLengkap }}</strong>
                    <span>Penghuni</span>
                </div>
            </div>
        </header>

        <main class="content">
            <p class="breadcrumb">
                BERANDA / MELIHAT TAGIHAN &amp; MENGUNGGAH BUKTI BAYAR
            </p>
            <div class="page-head">
                <h1>@yield('heading')</h1>
                <span class="pill">Kost.io</span>
            </div>
            <p class="subtitle">
                Kelola tagihan dan kirim bukti pembayaran dengan mudah.
            </p>

            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('assets/kostio/tagihan.js') }}" defer></script>
</body>
</html>
