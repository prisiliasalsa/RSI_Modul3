<?php
function ePengajuan($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function rupiahPengajuan($harga)
{
    return 'Rp' . number_format((float) $harga, 0, ',', '.');
}

function fotoPengajuan($foto)
{
    if (empty($foto)) {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $foto)) {
        return $foto;
    }

    return '../' . ltrim($foto, '/');
}

$fotoKost = $kamar['fotoUtama'] ?? '';

if (empty($fotoKost)) {
    $daftarFoto = json_decode($kamar['fotoKost'] ?? '', true);

    if (is_array($daftarFoto)) {
        $fotoKost = $daftarFoto[0] ?? '';
    } else {
        $fotoKost = $kamar['fotoKost'] ?? '';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ajukan Sewa - Kost.io</title>

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
        }

        /* SIDEBAR */
.sidebar {
    width: 244px;
    flex: none;
    background: var(--navy);
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
    background: var(--navy2);
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
    color: var(--sand);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.45;
}

.nav-item:hover {
    background: var(--navy2);
}

.nav-item.active {
    background: var(--maroon);
    color: #fff;
}

.spacer {
    flex: 1;
}

.bantuan {
    background: var(--navy2);
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

.light {
    color: var(--sand);
}

.white {
    color: #fff;
}

        /* AREA UTAMA */
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

.grow {
    flex: 1;
}

.small {
    font-size: 13px;
    line-height: 1.45;
}

.muted {
    color: #5B6871;
}

.label {
    font-size: 14px;
    line-height: 1.45;
    font-weight: 500;
}

.navy {
    color: #022E4A;
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
            padding: 40px;
        }

        .breadcrumb {
            color: #5B6871;
            font-size: 13px;
            margin-bottom: 24px;
        }

        .heading {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 10px;
        }

        h1 {
            color: #022E4A;
            font-size: 28px;
        }

        .room-code {
            background: #507593;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .subtitle {
            color: #5B6871;
            font-size: 15px;
            margin-bottom: 24px;
        }

        /* FORM DAN RINGKASAN */
        .application-layout {
            display: grid;
            grid-template-columns: minmax(0, 2.2fr) minmax(270px, 1fr);
            gap: 24px;
            align-items: start;
        }

        .form-card,
        .summary-card {
            background: white;
            border: 1px solid #D9D5CE;
            border-radius: 16px;
        }

        .form-card {
            padding: 28px;
        }

        .room-information {
            background: #E2D8CB;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 22px;
        }

        .room-information strong {
            display: block;
            color: #022E4A;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .room-information p {
            color: #5B6871;
            font-size: 15px;
            line-height: 1.5;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 22px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group label {
            display: block;
            color: #022E4A;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #D9D5CE;
            border-radius: 10px;
            background: white;
            color: #022E4A;
            font-size: 14px;
            outline: none;
        }

        .form-group input,
        .form-group select {
            height: 44px;
        }

        .form-group textarea {
            height: 88px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #507593;
        }

        .upload-area {
            background: #F5F0EA;
            border: 1px dashed #D9D5CE;
            border-radius: 10px;
            padding: 16px;
        }

        .upload-area input {
            height: auto;
            padding: 0;
            border: none;
            background: transparent;
        }

        .upload-area p {
            color: #5B6871;
            font-size: 12px;
            margin-top: 8px;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .button {
            display: inline-block;
            border: none;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            text-align: center;
        }

        .button-primary {
            background: #56061D;
            color: white;
        }

        .button-secondary {
            background: white;
            border: 1px solid #D9D5CE;
            color: #022E4A;
        }

        .error-message {
            background: #FCE8E8;
            color: #A32E2E;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        /* RINGKASAN HUNIAN */
        .summary-card {
            padding: 24px;
        }

        .summary-photo {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .summary-placeholder {
            width: 100%;
            height: 170px;
            background: #E7E1DA;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8A9298;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .summary-card h2 {
            color: #022E4A;
            font-size: 23px;
            margin-bottom: 8px;
        }

        .summary-location {
            color: #5B6871;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .summary-tag {
            display: inline-block;
            background: #507593;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin-bottom: 22px;
        }

        .price-label {
            color: #5B6871;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .summary-price {
            color: #56061D;
            font-size: 27px;
            font-weight: bold;
        }

        .summary-price span {
            font-size: 13px;
            font-weight: normal;
        }

        @media (max-width: 1050px) {
            .application-layout {
                grid-template-columns: 1fr;
            }

            .summary-card {
                max-width: none;
            }
        }

        @media (max-width: 750px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 25px 18px;
            }

            .topbar {
                padding: 15px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 20px;
            }

            .heading {
                flex-wrap: wrap;
            }

            h1 {
                font-size: 23px;
            }

            .actions {
                flex-direction: column;
            }
        }

        /* FIX WARNA SIDEBAR */
.sidebar {
    background: #022E4A !important;
    color: #E2D8CB;
}

.sidebar .logo span {
    color: #FFFFFF !important;
}

.sidebar .logo svg {
    stroke: #E2D8CB !important;
}

.sidebar .workspace {
    background: #173E54 !important;
}

.sidebar .workspace .small {
    color: #E2D8CB !important;
}

.sidebar .workspace .label {
    color: #FFFFFF !important;
}

.sidebar .nav-item {
    color: #E2D8CB !important;
    background: transparent;
}

.sidebar .nav-item.active {
    background: #56061D !important;
    color: #FFFFFF !important;
}

.sidebar .nav-item svg {
    stroke: currentColor !important;
}

.sidebar .nav-item:hover {
    background: #173E54;
}

.sidebar .bantuan {
    background: #173E54 !important;
}

.sidebar .bantuan .label {
    color: #FFFFFF !important;
}

.sidebar .bantuan .small {
    color: #E2D8CB !important;
}

.sidebar > .nav-item:last-child {
    color: #E2D8CB !important;
    background: transparent !important;
}
    </style>
</head>

<body>
<div class="layout">

    <!-- SIDEBAR -->
    <!-- SIDEBAR -->
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

        <a href="../controllers/KontrolCariKost.php"
            class="nav-item active">

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
        <span class="small light">
            Kelola tempat tinggal dengan lebih tenang.
        </span>
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

    <!-- AREA UTAMA -->
    <main class="main">

        <header class="topbar">

    <span class="small muted grow">
        Kost.io &nbsp;/&nbsp; Calon Penghuni
    </span>

    <svg width="22" height="22"
        viewBox="0 0 24 24"
        fill="none"
        stroke="#5B6871"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round">

        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.7 21a2 2 0 0 1-3.4 0"/>

    </svg>

    <div class="avatar">
        RA
    </div>

    <div class="akun">
        <span class="label navy">
            Rifa Aqilla
        </span>

        <span class="small muted">
            Calon Penghuni
        </span>
    </div>

</header>

        <section class="content">

            <div class="breadcrumb">
                BERANDA / MENGAJUKAN SEWA
            </div>

            <div class="heading">
                <h1>
                    Ajukan sewa kamar <?= ePengajuan($kamar['nomorKamar']) ?>
                </h1>

                <span class="room-code">
                    <?= ePengajuan($kamar['nomorKamar']) ?>
                </span>
            </div>

            <p class="subtitle">
                Lengkapi informasi untuk mengajukan sewa kamar pilihanmu.
            </p>

            <div class="application-layout">

                <!-- FORMULIR PENGAJUAN -->
                <div class="form-card">

                    <?php if ($pesanError === 'tanggal'): ?>
                        <div class="error-message">
                            Tanggal mulai sewa harus diisi dengan benar
                            dan tidak boleh sebelum hari ini.
                        </div>
                    <?php endif; ?>

                    <div class="room-information">

                        <strong>
                            <?= ePengajuan($kamar['namaKost']) ?>
                            · <?= ePengajuan($kamar['nomorKamar']) ?>
                        </strong>

                        <p>
                            <?= rupiahPengajuan($kamar['hargaSewa']) ?>
                            / bulan
                            · <?= ePengajuan($kamar['statusKamar']) ?>
                        </p>

                    </div>

                    <form
                        action="../controllers/KontrolPengajuanSewa.php?aksi=simpan"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="hidden"
                            name="idKamar"
                            value="<?= ePengajuan($kamar['idKamar']) ?>"
                        >

                        <!-- TANGGAL DAN DURASI -->
                        <div class="form-row">

                            <div class="form-group">
                                <label for="tanggalMulaiSewa">
                                    Tanggal Mulai Sewa *
                                </label>

                                <input
                                    type="date"
                                    id="tanggalMulaiSewa"
                                    name="tanggalMulaiSewa"
                                    min="<?= date('Y-m-d') ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="durasiSewa">
                                    Pilihan Durasi Sewa *
                                </label>

                                <select
                                    id="durasiSewa"
                                    name="durasiSewa"
                                    required
                                >
                                    <option value="">
                                        Pilih durasi sewa
                                    </option>

                                    <option value="1">1 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">12 Bulan</option>
                                </select>
                            </div>

                        </div>

                        <!-- JUMLAH PENGHUNI DAN KTP -->
                        <div class="form-row">

                            <div class="form-group">
                                <label for="jumlahPenghuni">
                                    Jumlah Penghuni *
                                </label>

                                <input
                                    type="number"
                                    id="jumlahPenghuni"
                                    name="jumlahPenghuni"
                                    min="1"
                                    max="10"
                                    placeholder="Masukkan jumlah penghuni"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="berkasKTP">
                                    Unggah Dokumen KTP *
                                </label>

                                <div class="upload-area">

                                    <input
                                        type="file"
                                        id="berkasKTP"
                                        name="berkasKTP"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >

                                    <p>
                                        JPG, PNG, atau PDF · maksimal 2 MB
                                    </p>

                                </div>
                            </div>

                        </div>

                        <!-- CATATAN -->
                        <div class="form-row">

                            <div class="form-group">
                                <label for="catatan">
                                    Catatan
                                </label>

                                <textarea
                                    id="catatan"
                                    name="catatan"
                                    maxlength="1000"
                                    placeholder="Tambahkan catatan"
                                ></textarea>
                            </div>

                        </div>

                        <!-- TOMBOL -->
                        <div class="actions">

                            <button
                                type="submit"
                                class="button button-primary"
                            >
                                Kirim Pengajuan
                            </button>

                            <a
                                href="../controllers/KontrolKost.php?aksi=detailKost&idKost=<?= urlencode($kamar['idKost']) ?>"
                                class="button button-secondary"
                            >
                                Batal
                            </a>

                        </div>

                    </form>

                </div>

                <!-- RINGKASAN HUNIAN -->
                <aside class="summary-card">

                    <?php if (!empty($fotoKost)): ?>

                        <img
                            src="<?= ePengajuan(fotoPengajuan($fotoKost)) ?>"
                            alt="Foto <?= ePengajuan($kamar['namaKost']) ?>"
                            class="summary-photo"
                        >

                    <?php else: ?>

                        <div class="summary-placeholder">
                            Foto kost belum tersedia
                        </div>

                    <?php endif; ?>

                    <h2>
                        <?= ePengajuan($kamar['namaKost']) ?>
                    </h2>

                    <p class="summary-location">
                        <?= ePengajuan($kamar['lokasi']) ?>
                    </p>

                    <span class="summary-tag">
                        Kamar <?= ePengajuan($kamar['nomorKamar']) ?>
                        · <?= ePengajuan($kamar['tipeKamar']) ?>
                    </span>

                    <p class="price-label">
                        Harga sewa / bulan
                    </p>

                    <p class="summary-price">
                        <?= rupiahPengajuan($kamar['hargaSewa']) ?>
                    </p>

                </aside>

            </div>

        </section>
    </main>
</div>
</body>
</html>