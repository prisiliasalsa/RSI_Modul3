<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') | Kost.io</title>
    <style>
        :root {
            --navy: #012d4b; --navy2: #15405c; --maroon: #5a0a24; --steel: #4f7794;
            --sand: #e2d9cc; --krem: #f3eee3; --muted: #5f6b76; --line: #e3dfd5;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body { font-family: Inter, "Segoe UI", Arial, sans-serif; color: var(--navy); background: var(--krem); }
        a { color: inherit; }

        .app { display: grid; grid-template-columns: 248px 1fr; min-height: 100vh; }

        /* ---------- Sidebar ---------- */
        .sb { background: var(--navy); color: #fff; padding: 24px 16px; display: flex; flex-direction: column; gap: 14px; }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; padding: 0 8px 6px; }
        .kotak { background: var(--navy2); border-radius: 10px; padding: 12px 14px; font-size: 13px; display: grid; gap: 4px; }
        .kotak small { font-size: 11px; letter-spacing: .04em; color: #b9c8d4; }
        .kotak span { color: #cdd8e0; }
        .menu { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 8px;
                font-size: 14px; color: #e3e9ee; text-decoration: none; }
        .menu:hover { background: var(--navy2); }
        .menu.aktif { background: var(--maroon); color: #fff; font-weight: 600; }
        .spasi { flex: 1; }

        /* ---------- Area utama ---------- */
        .top { background: #fff; display: flex; justify-content: space-between; align-items: center;
               padding: 14px 32px; border-bottom: 1px solid var(--line); font-size: 13px; color: var(--muted); }
        .user { display: flex; align-items: center; gap: 14px; }
        .avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--sand); color: var(--maroon);
                  display: grid; place-items: center; font-size: 13px; font-weight: 700; }
        .user .nama { font-size: 13px; font-weight: 600; color: var(--navy); line-height: 1.2; }
        .user .peran { font-size: 12px; color: var(--muted); }
        .isi { padding: 28px 32px 48px; max-width: 1100px; }
        .jejak { font-size: 12px; letter-spacing: .03em; color: var(--muted); margin-bottom: 14px; }
        .jejak a { text-decoration: none; }
        .jejak a:hover { text-decoration: underline; }
        .baris-judul { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .lencana { background: var(--steel); color: #fff; font-size: 12px; padding: 5px 12px; border-radius: 999px; }
        .sub { margin: 0 0 22px; color: var(--muted); font-size: 14px; }

        /* ---------- Komponen ---------- */
        .kartu { background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 18px; }
        .kartu-atas { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 22px; }
        .kartu-atas strong { font-size: 14px; }
        .cari { display: flex; align-items: center; gap: 8px; background: var(--krem); border-radius: 8px; padding: 8px 12px; width: 240px; }
        .cari input { border: 0; background: transparent; font: inherit; font-size: 13px; color: var(--navy); width: 100%; outline: none; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; font-weight: 500; font-size: 12px; color: var(--muted); background: var(--krem); padding: 12px 22px; }
        td { padding: 16px 22px; border-top: 1px solid var(--line); }
        tbody tr[data-url] { cursor: pointer; }
        tbody tr.dipilih { background: #faf7f0; }
        td.kostkamar { font-weight: 600; }
        td a { color: var(--maroon); font-weight: 600; text-decoration: none; }
        td a:hover { text-decoration: underline; }
        .lencana-status { display: inline-block; font-size: 12px; padding: 4px 12px; border-radius: 999px; background: var(--sand); color: var(--navy); }
        .lencana-status.ditolak { background: #f6e3e8; color: var(--maroon); }
        .lencana-status.disetujui { background: #e1ebf3; color: #2d5a7b; }
        .kaki { display: flex; justify-content: space-between; align-items: center; padding: 14px 22px; border-top: 1px solid var(--line);
                font-size: 12px; color: var(--muted); }
        .pager { display: flex; gap: 14px; }

        .aksi { display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 11px 18px; font: inherit; font-size: 13px; font-weight: 600;
               border-radius: 8px; border: 1px solid var(--maroon); cursor: pointer; text-decoration: none; }
        .btn-utama { background: var(--maroon); color: #fff; }
        .btn-utama:hover { background: #480819; }
        .btn-garis { background: #fff; color: var(--navy); border-color: var(--line); }
        .btn-garis:hover { background: var(--krem); }

        .kosong { text-align: center; padding: 40px 24px; }
        .ikon { width: 56px; height: 56px; margin: 0 auto 16px; display: grid; place-items: center; background: var(--sand); border-radius: 14px; }
        .kosong h2 { margin: 0 0 8px; font-size: 20px; }
        .kosong p { margin: 0; color: var(--muted); font-size: 13px; }

        .ringkas { background: var(--sand); border-radius: 10px; padding: 18px 20px; margin-bottom: 14px; display: grid; gap: 6px; }
        .ringkas span { color: var(--muted); font-size: 14px; }
        .status-banner { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px;
                         font-size: 14px; background: #e8eef4; color: #3d6481; }
        .status-banner.ditolak { background: #f6e3e8; color: var(--maroon); }
        .status-banner.menunggu { background: var(--sand); color: var(--navy); }
        .status-banner.netral { background: #eceff2; color: var(--muted); }

        @media (max-width: 860px) {
            .app { grid-template-columns: 1fr; }
            .sb { flex-direction: row; flex-wrap: wrap; align-items: center; padding: 14px; }
            .sb .kotak, .sb .spasi { display: none; }
            .top, .isi { padding-left: 18px; padding-right: 18px; }
            .cari { width: 100%; }
            .kartu-atas { flex-wrap: wrap; }
            table { font-size: 13px; }
            th, td { padding-left: 14px; padding-right: 14px; }
        }
    </style>
    @include('layouts.partials.kostio-ui-assets')
</head>
<body class="kio-ready kio-resident">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])
<div class="app">

    <!-- Sidebar Ruang Penghuni -->
    <aside class="sb kio-sidebar">
            @yield('kioResidentSidebar')
        </aside>

    <div class="area">
        <header class="top kio-header">
            @yield('kioResidentHeader')
        </header>

        <main class="isi">
            <div class="jejak">BERANDA @hasSection('jejak') / @yield('jejak') @endif</div>
            <div class="baris-judul">
                <h1>@yield('heading')</h1>
                <span class="lencana">Kost.io</span>
            </div>
            <p class="sub">@yield('subjudul', 'Pantau perkembangan dan keputusan pengajuan sewamu.')</p>

            @yield('konten')
        </main>
    </div>
</div>
</body>
</html>