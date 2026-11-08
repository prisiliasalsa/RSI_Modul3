<?php
// Mengamankan output HTML
function eKost($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Format harga ke Rupiah
function rupiahKost($harga)
{
    return 'Rp' . number_format((float) $harga, 0, ',', '.');
}

// Mengatur sumber foto kost
function sumberFotoKost($foto)
{
    if (empty($foto)) {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $foto)) {
        return $foto;
    }

    return '../' . ltrim($foto, '/');
}

$fotoKost = $kost['fotoUtama'] ?? '';


// DATA AKUN LOGIN
$akunLogin = $_SESSION['akun'] ?? [];

$namaAkun = $akunLogin['namaLengkap'] ?? 'Rifa Aqilla';
$jenisAkun = $akunLogin['jenisAkun'] ?? 'Calon Penghuni';

$kataNama = explode(' ', trim($namaAkun));
$inisialAkun = '';

foreach ($kataNama as $kata) {
    if ($kata !== '') {
        $inisialAkun .= strtoupper(substr($kata, 0, 1));
    }
}

$inisialAkun = substr($inisialAkun, 0, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kost - Kost.io</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', Arial, sans-serif;
        }

        body {
            background: #F5F0EA;
            color: #5B6871;
        }

        /* LAYOUT */
        .layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* SIDEBAR */
        :root {
    --navy: #022E4A;
    --navy2: #173E54;
    --maroon: #56061D;
    --sand: #E2D8CB;
    --bg: #F5F0EA;
    --border: #D9D5CE;
    --muted: #5B6871;
    --tag: #507593;
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

/* LAYOUT UTAMA */
.layout {
    display: flex;
    min-height: 100vh;
}

.main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}
        /* AREA UTAMA */
        .main {
            flex: 1;
            min-width: 0;
        }

        /* TOPBAR - SAMA DENGAN FORM CARI KOST */
.topbar {
    height: 80px;
    min-height: 80px;
    background: #FFFFFF;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 24px;
    box-shadow: 0 1px 4px rgba(12, 12, 13, .05);
}

.topbar .grow {
    flex: 1;
    min-width: 0;
}

.topbar .breadcrumb-top {
    font-size: 13px;
    line-height: 1.45;
    color: #5B6871;
}

.topbar .notification {
    width: 22px;
    height: 22px;
    flex: none;
}

.topbar .avatar {
    width: 36px;
    height: 36px;
    flex: none;
    border-radius: 50%;
    background: #E2D8CB;
    color: #56061D;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 500;
    flex: none;
}

.topbar .akun {
    display: flex;
    flex-direction: column;
    min-width: 140px;
}

.topbar .akun .label {
    font-size: 14px;
    line-height: 1.45;
    font-weight: 500;
    color: #022E4A;
}

.topbar .akun .small {
    font-size: 13px;
    line-height: 1.45;
    color: #5B6871;
}

        /* KONTEN */
        .content {
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .breadcrumb {
            font-size: 13px;
            color: #5B6871;
        }

        .heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        h1 {
            font-size: 28px;
            line-height: 1.4;
            font-weight: 600;
            color: #022E4A;
        }

        .badge {
            background: #507593;
            color: white;
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 13px;
            white-space: nowrap;
        }

        .subtitle {
            color: #5B6871;
            font-size: 15px;
            margin-top: -10px;
        }

        /* FOTO KOST */
        .kost-photo,
        .no-photo {
            width: 100%;
            height: 252px;
            object-fit: cover;
            border-radius: 16px;
            background: #E2D8CB;
        }

        .no-photo {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5B6871;
        }

        /* INFORMASI KOST */
        .kost-info {
            width: 100%;
            min-height: 112px;
            background: #E2D8CB;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 8px;
            font-size: 15px;
            line-height: 1.5;
        }

        .kost-info .location {
            color: #022E4A;
            font-size: 14px;
            font-weight: 500;
        }

        /* TABEL KAMAR */
        .table-panel {
            width: 100%;
            min-height: 350px;
            background: white;
            border: 1px solid #D9D5CE;
            border-radius: 16px;
            overflow: hidden;
        }

        .table-heading {
            min-height: 91px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px;
            gap: 20px;
        }

        .table-heading h2 {
            color: #022E4A;
            font-size: 14px;
            font-weight: 500;
        }

        .search-box {
            width: 292px;
            height: 43px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #F5F0EA;
            border-radius: 10px;
            padding: 12px;
        }

        .search-box span {
            color: #5B6871;
            font-size: 18px;
        }

        .search-box input {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-size: 13px;
            color: #022E4A;
        }

        .search-box input::placeholder {
            color: #5B6871;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background: #F5F0EA;
            color: #5B6871;
            font-size: 13px;
            font-weight: 400;
            text-align: left;
            padding: 15px 12px;
        }

        th:first-child,
        td:first-child {
            padding-left: 24px;
            width: 23%;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 21%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 19%;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 21%;
        }

        th:last-child,
        td:last-child {
            width: 16%;
            padding-right: 24px;
        }

        td {
            padding: 18px 12px;
            color: #5B6871;
            font-size: 15px;
            border-bottom: 1px solid #D9D5CE;
            height: 71px;
        }

        td:first-child {
            color: #022E4A;
            font-weight: 500;
            font-size: 14px;
        }

        .status {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 30px;
            background: #E7EEF3;
            color: #507593;
            font-size: 13px;
            white-space: nowrap;
        }

        .status.full {
            background: #507593;
            color: white;
        }

        .detail-link {
            color: #56061D;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }

        .detail-link:hover {
            text-decoration: underline;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            font-size: 13px;
            color: #5B6871;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* TOMBOL */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            height: 48px;
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #D9D5CE;
        }

        .button-primary {
            min-width: 148px;
            background: #56061D;
            border-color: #56061D;
            color: white;
        }

        .button-secondary {
            min-width: 150px;
            background: white;
            color: #022E4A;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #5B6871;
            font-size: 14px;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
    .sidebar {
        display: none;
    }

    .content {
        padding: 20px;
    }
}

@media (max-width: 650px) {
    .topbar {
        height: 68px;
        padding: 16px;
        gap: 12px;
    }

    .profile {
        min-width: auto;
    }

    .content {
        padding: 24px 16px;
        gap: 18px;
    }

    h1 {
        font-size: 23px;
    }

    .kost-photo,
    .no-photo {
        height: 190px;
    }

    .table-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .search-box {
        width: 100%;
    }
}

            /* PANEL DAFTAR DATA */
.table-panel {
    width: 100%;
    min-height: 350px;
    background: white;
    border: 1px solid #D9D5CE;
    border-radius: 16px;
    overflow: hidden;
}

.table-heading {
    height: 91px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
}

.table-heading h2 {
    font-size: 14px;
    font-weight: 500;
    color: #022E4A;
}

.search-box {
    width: 292px;
    height: 43px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #F5F0EA;
    border-radius: 10px;
    padding: 12px;
}

.search-box input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 13px;
}

.table-scroll {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th {
    background: #F5F0EA;
    color: #5B6871;
    font-size: 13px;
    font-weight: 400;
    text-align: left;
    padding: 15px 12px;
}

th:first-child,
td:first-child {
    padding-left: 24px;
    width: 23%;
}

th:nth-child(2),
td:nth-child(2) {
    width: 21%;
}

th:nth-child(3),
td:nth-child(3) {
    width: 19%;
}

th:nth-child(4),
td:nth-child(4) {
    width: 21%;
}

th:last-child,
td:last-child {
    width: 16%;
    padding-right: 24px;
}

td {
    padding: 18px 12px;
    height: 71px;
    border-bottom: 1px solid #D9D5CE;
    color: #5B6871;
    font-size: 15px;
}

td:first-child {
    color: #022E4A;
    font-weight: 500;
}

.status {
    display: inline-block;
    padding: 8px 12px;
    border-radius: 30px;
    background: #E7EEF3;
    color: #507593;
    font-size: 13px;
}

.status.full {
    background: #507593;
    color: white;
}

.detail-link {
    color: #56061D;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    color: #5B6871;
    font-size: 13px;
}

.pagination {
    display: flex;
    gap: 16px;
    align-items: center;
}
        }
    </style>
</head>

<body>

<div class="layout">

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

    <!-- KONTEN UTAMA -->
    <main class="main">

        <!-- TOPBAR -->
<header class="topbar">

    <span class="small muted grow">
        Kost.io &nbsp;/&nbsp; <?= eKost($jenisAkun) ?>
    </span>

    <!-- Ikon Notifikasi -->
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

    <!-- Foto Profil / Inisial -->
    <div class="avatar">
        <?= eKost($inisialAkun) ?>
    </div>

    <!-- Nama Pengguna -->
    <div class="akun">
        <span class="label navy">
            <?= eKost($namaAkun) ?>
        </span>

        <span class="small muted">
            <?= eKost($jenisAkun) ?>
        </span>
    </div>

</header>
        <section class="content">

            <!-- BREADCRUMB -->
            <div class="breadcrumb">
                BERANDA &nbsp; / &nbsp; MELIHAT DETAIL KAMAR KOST
            </div>

            <!-- JUDUL -->
            <div class="heading">

                <h1>
                    <?= eKost($kost['namaKost'] ?? 'Detail Kost') ?>
                </h1>

                <span class="badge">
                    Kost.io
                </span>

            </div>

            <p class="subtitle">
                Kenali fasilitas, harga, dan ketersediaan sebelum menentukan pilihan.
            </p>

            <!-- FOTO KOST -->
            <?php if (!empty($fotoKost)): ?>

                <img
                    src="<?= eKost(sumberFotoKost($fotoKost)) ?>"
                    alt="Foto <?= eKost($kost['namaKost'] ?? 'Kost') ?>"
                    class="kost-photo"
                >

            <?php else: ?>

                <div class="no-photo">
                    Foto kost belum tersedia
                </div>

            <?php endif; ?>

            <!-- INFORMASI KOST -->
            <!-- INFORMASI KOST -->
<div class="kost-info">

    <p class="location">
        <?= eKost($kost['lokasi'] ?? 'Lowokwaru, Malang') ?>
        · Kost <?= eKost($kost['tipeKost'] ?? 'Putra') ?>
    </p>

    <p class="facility">
        <?= eKost($kost['fasilitasUmum'] ?? 'Wi-Fi · parkir motor · dapur bersama') ?>
    </p>

    <p class="rule">
        Peraturan: <?= eKost($kost['peraturan'] ?? 'tamu sampai 21.00') ?>.
        Kontak pemilik:
        <?= eKost($kost['kontakPemilik'] ?? '08xxxxxxxxxx') ?>.
    </p>

</div>

            
<!-- DAFTAR KAMAR -->
<div class="table-panel">

    <div class="table-heading">

        <h2>Daftar data</h2>

        <label class="search-box">
            <span>⌕</span>

            <input
                type="search"
                id="cariKamar"
                placeholder="Cari data..."
            >
        </label>

    </div>

    <?php if (!empty($daftarKamar)): ?>

        <div class="table-scroll">

            <table>

                <thead>
                    <tr>
                        <th>Kamar</th>
                        <th>Tipe / ukuran</th>
                        <th>Harga / bulan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="daftarKamar">

    <?php foreach ($daftarKamar as $room): ?>

        <?php
            $tersedia = strtolower(trim($room['statusKamar'] ?? '')) === 'tersedia';
        ?>

        <tr>
            <td>
                <?= eKost($room['nomorKamar'] ?? '') ?>
            </td>

            <td>
                <?= eKost($room['tipeKamar'] ?? 'Standar') ?>
                ·
                <?= eKost($room['ukuranKamar'] ?? '3 × 3 m') ?>
            </td>

            <td>
                <?= rupiahKost($room['hargaSewa'] ?? 850000) ?>
            </td>

            <td>
                <span class="status <?= $tersedia ? '' : 'full' ?>">
                    <?= eKost($room['statusKamar'] ?? '') ?>
                </span>
            </td>

            <td>
                <a
                    href="../controllers/KontrolKost.php?aksi=detailKamar&idKamar=<?= urlencode($room['idKamar']) ?>"
                    class="detail-link"
                >
                    Lihat detail
                </a>
            </td>
        </tr>

    <?php endforeach; ?>

</tbody>

            </table>

        </div>

        <div class="table-footer">

            <span id="jumlahData">
                Menampilkan <?= count($daftarKamar) ?> data
            </span>

            <div class="pagination">
                <span>‹</span>
                <span>1</span>
                <span>›</span>
            </div>

        </div>

    <?php else: ?>

        <div class="empty">
            Belum ada kamar yang terdaftar pada kost ini.
        </div>

    <?php endif; ?>

</div>



            <!-- TOMBOL LIHAT KAMAR -->
            <?php if (!empty($daftarKamar)): ?>

                <div class="quick-actions">

                    <?php foreach (array_slice($daftarKamar, 0, 2) as $room): ?>

                        <?php
                            $tersedia =
                                strtolower(trim($room['statusKamar'] ?? ''))
                                === 'tersedia';
                        ?>

                        <a
                            class="button <?= $tersedia ? 'button-primary' : 'button-secondary' ?>"
                            href="../controllers/KontrolKost.php?aksi=detailKamar&idKamar=<?= urlencode($room['idKamar']) ?>"
                        >
                            Lihat kamar <?= eKost($room['nomorKamar'] ?? '') ?>
                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

<script>
    
// Fitur pencarian kamar
const inputCari = document.getElementById('cariKamar');
const tabelKamar = document.getElementById('daftarKamar');
const jumlahData = document.getElementById('jumlahData');

if (inputCari && tabelKamar) {

    inputCari.addEventListener('input', function () {

        const keyword = this.value.toLowerCase().trim();
        const baris = tabelKamar.querySelectorAll('tr');

        let jumlah = 0;

        baris.forEach(function (row) {

            const cocok = row.textContent
                .toLowerCase()
                .includes(keyword);

            row.style.display = cocok ? '' : 'none';

            if (cocok) {
                jumlah++;
            }

        });

        jumlahData.textContent =
            'Menampilkan ' + jumlah + ' data';

    });

}
</script>

</body>
</html>
