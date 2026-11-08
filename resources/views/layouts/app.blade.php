<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kost.io')</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f2ea; color: #1f2d3d; display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: #04304f; color: #fff; padding: 20px 12px; display: flex; flex-direction: column; }
        .sidebar .logo { font-size: 1.4rem; font-weight: bold; padding: 0 12px 20px; }
        .sidebar a { display: block; color: #e6edf3; text-decoration: none; padding: 10px 12px; border-radius: 6px; margin-bottom: 4px; }
        .sidebar a.aktif { background: #5a0a1e; color: #fff; }
        .sidebar .keluar { margin-top: auto; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .header { background: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; }
        .konten { padding: 24px; }
        .breadcrumb { font-size: 0.8rem; color: #666; text-transform: uppercase; margin-bottom: 8px; }
        h1 { margin: 0 0 6px; color: #04304f; }
        .deskripsi { color: #666; margin-bottom: 20px; }
        .kartu { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .kartu-ringkas { display: flex; gap: 16px; margin-bottom: 20px; }
        .ringkas { flex: 1; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; }
        .ringkas .angka { font-size: 1.6rem; font-weight: bold; color: #04304f; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 8px; border-bottom: 1px solid #eee; }
        th { background: #f3f3f3; font-size: 0.9rem; }
        .label { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; }
        .label-tersedia { background: #e3f6e8; color: #1b7a3a; }
        .label-terisi { background: #fbe9d7; color: #a25a00; }
        .btn { display: inline-block; padding: 10px 16px; border-radius: 6px; border: 1px solid #aaa; background: #fff; color: #333; text-decoration: none; cursor: pointer; font-size: 0.95rem; }
        .btn-utama { background: #5a0a1e; color: #fff; border: none; }
        .btn-hapus { background: #b00020; color: #fff; border: none; }
        .aksi a { margin-right: 10px; color: #5a0a1e; text-decoration: none; font-weight: bold; }
        .aksi form { display: inline; }
        .aksi button { background: none; border: none; color: #b00020; font-weight: bold; cursor: pointer; padding: 0; }
        .baris { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-weight: bold; margin-bottom: 6px; font-size: 0.9rem; }
        .field input, .field select, .field textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; }
        .field input[readonly], .field select[disabled] { background: #eee; color: #777; }
        .hint { font-size: 0.8rem; color: #666; margin-top: 4px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
        .alert-sukses { background: #e3f6e8; color: #1b7a3a; }
        .alert-error { background: #fdecea; color: #b00020; }
        .error-teks { color: #b00020; font-size: 0.85rem; margin-top: 4px; }
        .foto-preview { width: 220px; border-radius: 8px; margin-top: 8px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">kost.io</div>
        <a href="#">Kelola Kost</a>
        <a href="#" class="aktif">Kelola Kamar</a>
        <a href="#">Pengajuan Sewa</a>
        <a href="#">Data Penghuni</a>
        <a href="#">Verifikasi Pembayaran</a>
        <a href="#">Keluhan / Kerusakan</a>
        <a href="#">Laporan Keuangan</a>
        <a href="#" class="keluar">Keluar</a>
    </aside>

    <div class="main">
        <div class="header">
            <div>Kost.io / Pemilik Kost</div>
            <div>Pemilik Kost</div>
        </div>

        <div class="konten">
            @if (session('pesan'))
                <div class="alert alert-sukses">{{ session('pesan') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('isi')
        </div>
    </div>
</body>
</html>