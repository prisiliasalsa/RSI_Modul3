@php
    $daftarFoto = static function ($foto) {
        if (empty($foto)) {
            return [];
        }

        $hasil = json_decode($foto, true);

        return array_filter(
            is_array($hasil)
                ? $hasil
                : array_map('trim', explode(',', $foto))
        );
    };

    $sumberFoto = static function ($foto) {
        if (preg_match('/^https?:\/\//i', $foto)) {
            return $foto;
        }

        return '../' . ltrim($foto, '/');
    };

    $fotoGaleri = $daftarFoto($kamar['fotoKamar'] ?? '');
    $fotoUtama = $fotoGaleri[0] ?? '';
    $fotoKedua = $fotoGaleri[1] ?? '';

    $tersedia = strtolower(trim($kamar['statusKamar'] ?? '')) === 'tersedia';
    $akunLogin = $akunLoginLaravel ?? [];
    $namaAkun = $akunLogin['namaLengkap'] ?? 'Calon Penghuni';
    $jenisAkun = $akunLogin['jenisAkun'] ?? 'Calon Penghuni';

    $inisialAkun = '';
    foreach (explode(' ', trim($namaAkun)) as $kata) {
        if ($kata !== '') {
            $inisialAkun .= strtoupper(substr($kata, 0, 1));
        }
    }
    $inisialAkun = substr($inisialAkun, 0, 2);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kamar {{ $kamar['nomorKamar'] }} - Kost.io</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #F5F0EA;
            color: #263746;
        }

        .layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .sidebar {
            width: 244px;
            flex: none;
            background: #022E4A;
            padding: 32px 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .logo {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .logo span {
            font-size: 27px;
            font-weight: 600;
            color: #fff;
            line-height: 1.45;
        }

        .workspace {
            background: #173E54;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border-radius: 10px;
            color: #E2D8CB;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.45;
        }

        .nav-item:hover {
            background: #173E54;
        }

        .nav-item.active {
            background: #56061D;
            color: #fff;
        }

        .spacer, .grow {
            flex: 1;
        }

        .bantuan {
            background: #173E54;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .small {
            font-size: 13px;
            line-height: 1.45;
        }

        .label {
            font-size: 14px;
            line-height: 1.45;
            font-weight: 500;
        }

        .light { color: #E2D8CB; }
        .white { color: #fff; }
        .muted { color: #5B6871; }
        .navy { color: #022E4A; }

        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 80px;
            background: #fff;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: 0 1px 4px rgba(12, 12, 13, .05);
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #E2D8CB;
            color: #56061D;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
        }

        .akun {
            display: flex;
            flex-direction: column;
            min-width: 140px;
        }

        .content {
            padding: 24px;
        }

        .breadcrumb {
            font-size: 11px;
            color: #8A9298;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }

        .heading {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 10px;
        }

        h1 {
            color: #022E4A;
            font-size: 29px;
        }

        .room-code {
            background: #E9DDD2;
            color: #56061D;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
        }

        .subtitle {
            color: #78838A;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .gallery {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .gallery img, .photo-placeholder {
            width: 100%;
            height: 135px;
            object-fit: cover;
            border-radius: 10px;
            background: #E7E1DA;
        }

        .photo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8A9298;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            padding: 22px 25px;
            border-radius: 12px;
            border: 1px solid #E9E2DB;
        }

        .summary-label {
            color: #899197;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .summary-value {
            color: #022E4A;
            font-size: 23px;
            font-weight: bold;
        }

        .summary-value.price { color: #56061D; }
        .summary-value.available { color: #267345; }
        .summary-value.unavailable { color: #A32E2E; }

        .detail-card {
            background: #E5D9CA;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 15px;
        }

        .detail-item {
            font-size: 12px;
            line-height: 1.7;
            color: #66727A;
        }

        .detail-item strong {
            color: #022E4A;
        }

        .actions {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .button {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 7px;
            padding: 13px 25px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
        }

        .button-primary {
            background: #56061D;
            color: white;
        }

        .button-secondary {
            background: white;
            color: #56061D;
            border: 1px solid #56061D;
        }

        .button-disabled {
            background: #D9D9D9;
            color: #777;
            cursor: not-allowed;
        }

        @media (max-width: 900px) {
            .sidebar { display: none; }
            .content { padding: 30px 24px; }
            .gallery img, .photo-placeholder { height: 240px; }
        }

        @media (max-width: 650px) {
            .content { padding: 24px 16px; }
            .gallery, .summary { grid-template-columns: 1fr; }
            .gallery img, .photo-placeholder { height: 220px; }
            .actions { flex-direction: column-reverse; }
        }
    </style>
</head>

<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">
            <svg width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="#E2D8CB" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-8 9 8v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>
            </svg>
            <span>kost.io</span>
        </div>

        <div class="workspace">
            <span class="small light">RUANG PENGHUNI</span>
            <span class="label white">Temukan nyamannya</span>
        </div>

        <nav class="menu">
            <a href="{{ route('kost.cari') }}" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M21 21l-4.3-4.3"/>
                </svg>
                <span>Cari Kost</span>
            </a>

            <a href="#" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 3v5h5"/>
                </svg>
                <span>Status Pengajuan</span>
            </a>
        </nav>

        <div class="spacer"></div>

        <div class="bantuan">
            <span class="label white">Butuh bantuan?</span>
            <span class="small light">Kelola tempat tinggal dengan lebih tenang.</span>
        </div>

        <a href="#" class="nav-item">
            <svg width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <path d="M16 17l5-5-5-5"/>
                <path d="M21 12H9"/>
            </svg>
            <span>Keluar</span>
        </a>
    </aside>

    <main class="main">
        <header class="topbar">
            <span class="small muted grow">
                Kost.io &nbsp;/&nbsp; {{ $jenisAkun }}
            </span>

            <svg width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="#5B6871" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>

            <div class="avatar">{{ $inisialAkun }}</div>

            <div class="akun">
                <span class="label navy">{{ $namaAkun }}</span>
                <span class="small muted">{{ $jenisAkun }}</span>
            </div>
        </header>

        <section class="content">
            <div class="breadcrumb">BERANDA / MELIHAT DETAIL KAMAR KOST</div>

            <div class="heading">
                <h1>Detail kamar {{ $kamar['nomorKamar'] }}</h1>
                <span class="room-code">{{ $kamar['nomorKamar'] }}</span>
            </div>

            <p class="subtitle">
                Kenali fasilitas, harga, dan ketersediaan sebelum menentukan pilihan.
            </p>

            <div class="gallery">
                @if ($fotoUtama)
                    <img src="{{ $sumberFoto($fotoUtama) }}"
                         alt="Foto kamar {{ $kamar['nomorKamar'] }}">
                @else
                    <div class="photo-placeholder">Foto kamar belum tersedia</div>
                @endif

                @if ($fotoKedua)
                    <img src="{{ $sumberFoto($fotoKedua) }}" alt="Foto fasilitas kamar">
                @else
                    <div class="photo-placeholder">Foto fasilitas belum tersedia</div>
                @endif
            </div>

            <div class="summary">
                <div class="summary-card">
                    <p class="summary-label">Harga Sewa</p>
                    <p class="summary-value price">
                        Rp{{ number_format((float) $kamar['hargaSewa'], 0, ',', '.') }}
                        <span style="font-size: 13px; font-weight: normal;">/ bulan</span>
                    </p>
                </div>

                <div class="summary-card">
                    <p class="summary-label">Ketersediaan Kamar</p>
                    <p class="summary-value {{ $tersedia ? 'available' : 'unavailable' }}">
                        {{ $tersedia ? 'Tersedia' : 'Tidak Tersedia' }}
                    </p>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-item">
                    <strong>
                        {{ $kamar['tipeKamar'] }} · {{ $kamar['ukuranKamar'] }}
                    </strong>
                    <br>
                    {{ $kamar['fasilitasKamar'] }}. Harga belum termasuk listrik.
                </div>
            </div>

            <div class="actions">
                @if ($tersedia)
                    <a href="{{ route('pengajuan.ajukan', ['idKamar' => $kamar['idKamar']]) }}"
                       class="button button-primary">
                        Ajukan Sewa
                    </a>
                @else
                    <button type="button" class="button button-disabled" disabled>
                        Kamar Tidak Tersedia
                    </button>
                @endif

                <a href="{{ route('kost.detail', ['idKost' => $kamar['idKost']]) }}"
                   class="button button-secondary">
                    Kembali ke kost
                </a>
            </div>
        </section>
    </main>
</div>
</body>
</html>