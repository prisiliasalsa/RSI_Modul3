<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Kost.io</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #fff; color: #04304f; }
        .wrapper { display: flex; min-height: 100vh; padding: 16px; }
        .kiri { flex: 1; background: #04304f; color: #fff; padding: 40px; border-radius: 16px 0 0 16px; display: flex; flex-direction: column; }
        .kiri .logo { font-size: 1.5rem; font-weight: bold; margin-bottom: 28px; }
        .kiri h2 { font-size: 2.2rem; margin: 0 0 12px; line-height: 1.2; }
        .kiri .lead { color: #cfd8e3; margin-bottom: 24px; }
        .kiri img { width: 100%; border-radius: 12px; object-fit: cover; height: 240px; }
        .kiri ul { list-style: none; padding: 0; margin: 24px 0 0; }
        .kiri li { padding: 6px 0; color: #e6edf3; }
        .kiri li::before { content: "✓  "; color: #7fd1a3; }
        .kanan { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .form-box { width: 100%; max-width: 480px; }
        .eyebrow { color: #5a0a1e; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 14px; }
        .ikon-gagal { width: 56px; height: 56px; border-radius: 14px; background: #e6edf3; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 1.4rem; color: #5a7691; }
        h1 { color: #04304f; margin: 0 0 10px; font-size: 1.9rem; }
        .deskripsi { color: #666; margin-bottom: 22px; }
        .label-peran { font-size: 0.9rem; font-weight: bold; margin-bottom: 8px; }
        .peran { display: flex; gap: 10px; margin-bottom: 20px; }
        .peran label { flex: 1; }
        .peran input { display: none; }
        .peran span { display: block; text-align: center; padding: 12px 8px; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; cursor: pointer; }
        .peran input:checked + span { background: #5a0a1e; color: #fff; border-color: #5a0a1e; }
        .baris-dua { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-weight: bold; margin-bottom: 6px; font-size: 0.95rem; }
        .field input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; }
        .input-sandi { position: relative; }
        .input-sandi input { padding-right: 44px; }
        .toggle-sandi { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: #666; display: flex; align-items: center; }
        .toggle-sandi:hover { color: #04304f; }
        .tombol { display: flex; flex-direction: row; align-items: center; gap: 12px; margin: 8px 0 24px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; height: 44px; min-width: 96px; padding: 0 20px; border-radius: 8px; border: 1px solid #aaa; background: #fff; color: #04304f; text-decoration: none; cursor: pointer; font-size: 0.95rem; white-space: nowrap; box-sizing: border-box; }
        .btn-utama { background: #5a0a1e; color: #fff; border: none; }
        .banner { padding: 14px 16px; border-radius: 8px; margin-bottom: 22px; background: #5a7691; color: #fff; font-size: 0.95rem; }
        .info-akses { background: #e3d9c9; padding: 16px; border-radius: 8px; font-size: 0.92rem; color: #555; margin-top: 20px; }
        .info-akses strong { color: #04304f; }
        .catatan { font-size: 0.82rem; color: #666; margin-top: 20px; }
        .error-teks { color: #b00020; font-size: 0.85rem; margin-top: 4px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="kiri">
        <div class="logo">kost.io</div>
        <h2>Ruang nyaman.<br>Cerita baru.</h2>
        <div class="lead">Dari mencari kamar hingga mengelola hunian, semua terasa lebih mudah.</div>
        <img src="{{ asset('assets/img/kamar.jpg') }}" alt="Kamar kost">
        <ul>
            <li>Pilihan kamar sesuai kebutuhan</li>
            <li>Pengajuan sewa yang jelas</li>
            <li>Tagihan dan keluhan dalam satu tempat</li>
        </ul>
    </div>

    <div class="kanan">
        <div class="form-box">
            <div class="eyebrow">Selamat datang kembali</div>

            @if (session('error'))
                {{-- UI-02.02: Login belum berhasil --}}
                <div class="ikon-gagal">✓</div>
                <h1>Login belum berhasil</h1>
                <p class="deskripsi">Masuk untuk melanjutkan aktivitas hunianmu.</p>
                <div class="banner">{{ session('error') }}</div>
            @else
                {{-- UI-02.01: Masuk ke Kost.io --}}
                <h1>Masuk ke Kost.io</h1>
                <p class="deskripsi">Temukan kamar dan pantau pengajuan sewamu.</p>

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
            @endif

            <form action="{{ route('login.proses') }}" method="POST">
                @csrf

                @if (session('error'))
                    <input type="hidden" name="peran" value="{{ old('peran', 'Calon Penghuni') }}">
                @endif

                <div class="baris-dua">
                    <div class="field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com">
                    </div>

                    <div class="field">
                        <label for="kata_sandi">Kata sandi *</label>
                        <div class="input-sandi">
                            <input type="password" id="kata_sandi" name="kata_sandi" placeholder="••••••••">
                            <button type="button" class="toggle-sandi" id="toggleSandi" aria-label="Tampilkan kata sandi">
                                <svg id="ikonMata" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                @if (session('error'))
                    <div class="tombol">
                        <button type="submit" class="btn btn-utama">Coba masuk lagi</button>
                        <a href="#" class="btn">Daftar akun</a>
                    </div>
                @else
                    <div class="tombol">
                        <button type="submit" class="btn btn-utama">Masuk</button>
                        <a href="#" class="btn">Buat akun</a>
                    </div>
                @endif
            </form>

            @if (!session('error'))
                <div class="info-akses">
                    <strong>Akses sesuai akun</strong><br>
                    Calon penghuni: cari kost dan status pengajuan. Penghuni: tagihan dan keluhan. Pemilik: pengelolaan kost dan laporan.
                </div>
            @endif

            <div class="catatan">Dengan melanjutkan, kamu menggunakan akun sesuai peran yang terdaftar.</div>
        </div>
    </div>
</div>

<script>
    const inputSandi = document.getElementById('kata_sandi');
    const tombolToggle = document.getElementById('toggleSandi');
    const ikonMata = document.getElementById('ikonMata');

    const ikonTerbuka = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    const ikonTertutup = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><line x1="1" y1="1" x2="23" y2="23"></line>';

    tombolToggle.addEventListener('click', function () {
        const sedangTersembunyi = inputSandi.type === 'password';
        inputSandi.type = sedangTersembunyi ? 'text' : 'password';
        ikonMata.innerHTML = sedangTersembunyi ? ikonTertutup : ikonTerbuka;
        tombolToggle.setAttribute('aria-label', sedangTersembunyi ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
</script>
</body>
</html>