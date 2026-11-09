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
        .sb { background: var(--navy); color: #fff; padding: 24px 16px; display: flex; flex-direction: column; gap: 8px; }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; padding: 0 8px 6px; }
        .kotak { background: var(--navy2); border-radius: 10px; padding: 12px 14px; font-size: 13px; display: grid; gap: 4px; margin-bottom: 8px; }
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
        .baris-judul { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .lencana { background: var(--steel); color: #fff; font-size: 12px; padding: 5px 12px; border-radius: 999px; }
        .sub { margin: 0 0 22px; color: var(--muted); font-size: 14px; }

        /* ---------- Komponen ---------- */
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 18px; }
        .stat { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 16px 20px; }
        .stat small { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted); }
        .stat strong { display: block; margin-top: 10px; font-size: 22px; }

        .kartu { background: #fff; border: 1px solid var(--line); border-radius: 12px; margin-bottom: 18px; }
        .kartu-atas { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 22px; }
        .kartu-atas strong { font-size: 14px; }
        .cari { display: flex; align-items: center; gap: 8px; background: var(--krem); border-radius: 8px; padding: 8px 12px; width: 240px; }
        .cari input { border: 0; background: transparent; font: inherit; font-size: 13px; color: var(--navy); width: 100%; outline: none; }
        .tabel-bungkus { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; font-weight: 500; font-size: 12px; color: var(--muted); background: var(--krem); padding: 12px 22px; }
        td { padding: 16px 22px; border-top: 1px solid var(--line); }
        tbody tr[data-nama] { cursor: pointer; }
        tbody tr.dipilih { background: #faf7f0; }
        td.nama-kost { font-weight: 600; }
        td a { color: var(--maroon); font-weight: 600; text-decoration: none; }
        td a:hover { text-decoration: underline; }
        .lencana-status { display: inline-block; font-size: 12px; padding: 4px 12px; border-radius: 999px; }
        .lencana-status.publik { background: #e1ebf3; color: #2d5a7b; }
        .lencana-status.draft { background: var(--steel); color: #fff; }
        .kaki { display: flex; justify-content: space-between; align-items: center; padding: 14px 22px; border-top: 1px solid var(--line);
                font-size: 12px; color: var(--muted); }
        .pager { display: flex; gap: 14px; }

        .aksi { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
        .aksi-kolom { display: flex; flex-direction: column; align-items: flex-start; gap: 14px; }
        .btn { display: inline-block; padding: 11px 18px; font: inherit; font-size: 13px; font-weight: 600;
               border-radius: 8px; border: 1px solid var(--maroon); cursor: pointer; text-decoration: none; }
        .btn-utama { background: var(--maroon); color: #fff; }
        .btn-utama:hover { background: #480819; }
        .btn-garis { background: #fff; color: var(--navy); border-color: var(--line); }
        .btn-garis:hover { background: var(--krem); }

        .banner { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px;
                  background: var(--steel); color: #fff; font-size: 14px; }
        .banner-berhasil { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px;
                           background: #e1ebf3; color: #2d5a7b; font-size: 14px; }
        .ringkas { background: var(--sand); border-radius: 10px; padding: 18px 20px; margin-bottom: 18px; display: grid; gap: 6px; }
        .ringkas span { color: var(--muted); font-size: 14px; }

        .kosong { text-align: center; padding: 40px 24px; }
        .ikon { width: 56px; height: 56px; margin: 0 auto 16px; display: grid; place-items: center; background: var(--sand); border-radius: 14px; }
        .kosong h2 { margin: 0 0 8px; font-size: 20px; }
        .kosong p { margin: 0; color: var(--muted); font-size: 13px; }

        /* ---------- Formulir ---------- */
        .dua-kolom { display: grid; grid-template-columns: 1fr 300px; gap: 18px; align-items: start; }
        .form { padding: 24px; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 20px; margin-bottom: 20px; }
        .field label.lbl { display: block; margin-bottom: 8px; font-size: 14px; }
        .field input[type=text], .field input[type=tel], .field select, .field textarea {
            width: 100%; padding: 0 14px; font: inherit; font-size: 14px; color: var(--navy);
            background: #fff; border: 1px solid #d9d4c8; border-radius: 8px; height: 42px;
        }
        .field textarea { height: 96px; padding: 12px 14px; resize: vertical; }
        .field.has-error input, .field.has-error select, .field.has-error textarea, .field.has-error .dd summary,
        .field.has-error .upload { border-color: var(--maroon); }
        .err { margin-top: 6px; color: var(--maroon); font-size: 13px; }
        .dd { position: relative; }
        .dd summary { list-style: none; display: flex; align-items: center; justify-content: space-between; height: 42px; padding: 0 14px;
                      font-size: 14px; background: #fff; border: 1px solid #d9d4c8; border-radius: 8px; cursor: pointer; }
        .dd summary::-webkit-details-marker { display: none; }
        .dd-list { position: absolute; z-index: 5; top: 46px; left: 0; right: 0; background: #fff; border: 1px solid #d9d4c8;
                   border-radius: 8px; padding: 12px; display: grid; gap: 10px; max-height: 230px; overflow: auto; }
        .dd-list label { display: flex; align-items: center; gap: 10px; font-size: 14px; cursor: pointer; }
        .upload { display: grid; gap: 6px; justify-items: start; padding: 16px; border: 1px dashed #cdbfa6; background: #f5efe3;
                  border-radius: 10px; cursor: pointer; min-height: 100px; align-content: center; }
        .upload strong { color: var(--maroon); font-size: 14px; }
        .upload small { color: var(--muted); font-size: 12px; }
        .preview { padding: 14px; }
        .preview img, .preview .ph { width: 100%; aspect-ratio: 4 / 3; border-radius: 12px; object-fit: cover; display: block; }
        .preview .ph { background: linear-gradient(135deg, #3c5f78, #8aa4b7); }
        .preview h3 { margin: 14px 0 4px; font-size: 20px; }
        .preview p { margin: 0 0 10px; font-size: 12px; color: var(--muted); }
        .tag { display: inline-block; background: var(--steel); color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 999px; }

        @media (max-width: 860px) {
            .app { grid-template-columns: 1fr; }
            .sb { flex-direction: row; flex-wrap: wrap; align-items: center; padding: 14px; }
            .sb .kotak, .sb .spasi { display: none; }
            .top, .isi { padding-left: 18px; padding-right: 18px; }
            .stat-grid, .dua-kolom, .grid2 { grid-template-columns: 1fr; }
            .cari { width: 100%; }
            .kartu-atas { flex-wrap: wrap; }
            th, td { padding-left: 14px; padding-right: 14px; }
        }
    </style>
</head>
<body>
<div class="app">

    <!-- Sidebar Ruang Pemilik -->
    <aside class="sb">
        <div class="brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V21H3z"/><path d="M9 21v-7h6v7"/></svg>
            kost.io
        </div>
        <div class="kotak"><small>RUANG PEMILIK</small><strong>Kelola hunianmu</strong></div>

        <a class="menu aktif" href="{{ route('kost.daftar', $qs) }}">Kelola Kost</a>
        <a class="menu" href="{{ url('/kelola-kamar') }}">Kelola Kamar</a>
        <a class="menu" href="{{ url('/pengajuan-sewa') }}">Pengajuan Sewa</a>
        <a class="menu" href="{{ url('/data-penghuni') }}">Data Penghuni</a>
        <a class="menu" href="{{ url('/verifikasi-pembayaran') }}">Verifikasi Pembayaran</a>
        <a class="menu" href="{{ url('/keluhan-pemilik') }}">Keluhan / Kerusakan</a>
        <a class="menu" href="{{ url('/laporan-keuangan') }}">Laporan Keuangan</a>

        <div class="spasi"></div>
        <div class="kotak"><strong>Butuh bantuan?</strong><span>Kelola tempat tinggal dengan lebih tenang.</span></div>
        <a class="menu" href="{{ url('/logout') }}">Keluar</a>
    </aside>

    <div class="area">
        <header class="top">
            <div>Kost.io &nbsp;/&nbsp; Pemilik Kost</div>
            <div class="user">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5f6b76" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
                <div class="avatar">{{ $pengguna['inisial'] }}</div>
                <div>
                    <div class="nama">{{ $pengguna['nama'] }}</div>
                    <div class="peran">{{ $pengguna['peran'] }}</div>
                </div>
            </div>
        </header>

        <main class="isi">
            <div class="jejak">BERANDA / MENGELOLA &amp; PUBLIKASI DATA KOST</div>
            <div class="baris-judul">
                <h1>@yield('heading')</h1>
                <span class="lencana" id="lencana">{{ $lencana }}</span>
            </div>
            <p class="sub">Atur informasi hunian dan tampilkan kepada calon penghuni.</p>

            @yield('konten')
        </main>
    </div>
</div>
</body>
</html>