<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Kost.io</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #fff; color: #1f2d3d; }
        .wrapper { display: flex; min-height: 100vh; }
        .kiri { flex: 1; background: #04304f; color: #fff; padding: 40px; display: flex; flex-direction: column; }
        .kiri .logo { font-size: 1.5rem; font-weight: bold; margin-bottom: 30px; }
        .kiri h2 { font-size: 2rem; margin: 0 0 12px; line-height: 1.2; }
        .kiri p { color: #cfd8e3; }
        .kiri ul { list-style: none; padding: 0; margin-top: 30px; }
        .kiri li { padding: 6px 0; color: #e6edf3; }
        .kiri li::before { content: "✓  "; color: #7fd1a3; }
        .kanan { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .form-box { width: 100%; max-width: 420px; }
        .eyebrow { color: #5a0a1e; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 8px; }
        h1 { color: #04304f; margin: 0 0 8px; }
        .deskripsi { color: #666; margin-bottom: 20px; }
        .label-peran { font-size: 0.9rem; font-weight: bold; margin-bottom: 8px; }
        .peran { display: flex; gap: 8px; margin-bottom: 18px; }
        .peran label { flex: 1; border: 1px solid #ccc; border-radius: 6px; padding: 10px; text-align: center; cursor: pointer; font-size: 0.9rem; }
        .peran input { display: none; }
        .peran input:checked + span { background: #5a0a1e; color: #fff; }
        .peran span { display: block; padding: 10px; border-radius: 6px; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-weight: bold; margin-bottom: 6px; font-size: 0.9rem; }
        .field input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; }
        .baris-dua { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .tombol { display: flex; gap: 10px; margin-top: 10px; }
        .btn { padding: 10px 16px; border-radius: 6px; border: 1px solid #aaa; background: #fff; color: #333; text-decoration: none; cursor: pointer; font-size: 0.95rem; }
        .btn-utama { background: #5a0a1e; color: #fff; border: none; }
        .banner { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; background: #5a7691; color: #fff; }
        .judul-gagal { color: #04304f; font-size: 1.4rem; font-weight: bold; margin: 0 0 8px; }
        .info-akses { background: #e3d9c9; padding: 14px; border-radius: 6px; margin-top: 20px; font-size: 0.9rem; }
        .catatan { font-size: 0.8rem; color: #666; margin-top: 16px; }
        .error-teks { color: #b00020; font-size: 0.85rem; margin-top: 4px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="kiri">
        <div class="logo">kost.io</div>
        <h2>Ruang nyaman.<br>Cerita baru.</h2>
        <p>Dari mencari kamar hingga mengelola hunian, semua terasa lebih mudah.</p>
        <ul>
            <li>Pilihan kamar sesuai kebutuhan</li>
            <li>Pengajuan sewa yang jelas</li>
            <li>Tagihan dan keluhan dalam satu tempat</li>
        </ul>
    </div>

    <div class="kanan">
        <div class="form-box">
            <div class="eyebrow">Selamat datang kembali</div>

            @if (session('error') && !session('gagal_login_ulang'))
                <div class="judul-gagal">Login belum berhasil</div>
                <p class="deskripsi">Masuk untuk melanjutkan aktivitas hunianmu.</p>
                <div class="banner">{{ session('error') }}</div>
            @else
                <h1>Masuk ke Kost.io</h1>
                <p class="deskripsi">Temukan kamar dan pantau pengajuan sewamu.</p>
            @endif

            <form action="{{ route('login.proses') }}" method="POST">
                @csrf

                <div class="label-peran">Masuk sebagai</div>
                <div class="peran">
                    <label>
                        <input type="radio" name="peran" value="Calon Penghuni" @checked(old('peran', 'Calon Penghuni') === 'Calon Penghuni')>
                        <span>Calon Penghuni</span>
                    </label>
                    <label>
                        <input type="radio" name="peran" value="Penghuni" @checked(old('peran') === 'Penghuni')>
                        <span>Penghuni</span>
                    </label>
                    <label>
                        <input type="radio" name="peran" value="Pemilik Kost" @checked(old('peran') === 'Pemilik Kost')>
                        <span>Pemilik Kost</span>
                    </label>
                </div>

                <div class="baris-dua">
                    <div class="field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com">
                    </div>
                    <div class="field">
                        <label for="kata_sandi">Kata sandi *</label>
                        <input type="password" id="kata_sandi" name="kata_sandi" placeholder="••••••••">
                    </div>
                </div>

                <div class="tombol">
                    <button type="submit" class="btn btn-utama">Masuk</button>
                    <a href="{{ route('register') }}" class="btn">Buat akun</a>
                </div>
            </form>

            <div class="info-akses">
                <strong>Akses sesuai akun</strong><br>
                Calon penghuni: cari kost dan status pengajuan. Penghuni: tagihan dan keluhan. Pemilik: pengelolaan kost dan laporan.
            </div>

            <div class="catatan">Dengan melanjutkan, kamu menggunakan akun sesuai peran yang terdaftar.</div>
        </div>
    </div>
</div>
</body>
</html>