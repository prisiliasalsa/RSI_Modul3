<?php
/**
 * FormCariKost.php  (<<boundary>>, UI-03.01)
 * Dipanggil oleh KontrolCariKost::tampilFormCari(); variabel $kontrol tersedia.
 */
$akun   = $_SESSION['akun'];
$k      = $kontrol->getKriteria();
$daftar = $kontrol->getDaftarKost();
$jumlah = count($daftar);
$self   = $_SERVER['SCRIPT_NAME'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cari kost yang cocok · Kost.io</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--navy:#022E4A;--navy2:#173E54;--maroon:#56061D;--sand:#E2D8CB;--bg:#F5F0EA;--border:#D9D5CE;--muted:#5B6871;--tag:#507593}
*{box-sizing:border-box;margin:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--navy)}
.app{display:flex;min-height:100vh}
.sidebar{width:244px;flex:none;background:var(--navy);padding:32px 20px 24px;display:flex;flex-direction:column;gap:24px;position:sticky;top:0;height:100vh}
.logo{display:flex;gap:12px;align-items:center}
.logo span{font-size:27px;font-weight:600;color:#fff;line-height:1.45}
.workspace{background:var(--navy2);border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:4px}
.menu{display:flex;flex-direction:column;gap:8px}
.nav-item{display:flex;gap:12px;align-items:center;padding:12px;border-radius:10px;color:var(--sand);text-decoration:none;font-size:14px;font-weight:500;line-height:1.45}
.nav-item:hover{background:var(--navy2)}
.nav-item.active{background:var(--maroon);color:#fff}
.spacer{flex:1}
.bantuan{background:var(--navy2);border-radius:16px;padding:16px;display:flex;flex-direction:column;gap:8px}
.small{font-size:13px;line-height:1.45}
.label{font-size:14px;line-height:1.45;font-weight:500}
.body{font-size:15px;line-height:1.45}
.title{font-size:28px;line-height:1.45;font-weight:600;color:var(--navy)}
.muted{color:var(--muted)}.light{color:var(--sand)}.white{color:#fff}.navy{color:var(--navy)}.maroon{color:var(--maroon)}.grow{flex:1}
.workspace-area{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{height:80px;background:#fff;padding:24px;display:flex;align-items:center;gap:24px;box-shadow:0 1px 4px rgba(12,12,13,.05)}
.avatar{width:36px;height:36px;border-radius:24px;background:var(--sand);color:var(--maroon);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:500}
.akun{display:flex;flex-direction:column;min-width:140px}
.content{padding:40px;display:flex;flex-direction:column;gap:24px}
.judul{display:flex;gap:24px;align-items:flex-start}
.tag{background:var(--tag);color:#fff;font-size:13px;line-height:1.45;padding:8px 12px;border-radius:999px}
.panel-cari{background:var(--sand);border-radius:16px;padding:24px;display:flex;flex-direction:column;gap:16px}
.form-cari{display:flex;flex-direction:column;gap:16px}
.row{display:flex;gap:20px}
.field{flex:1;display:flex;flex-direction:column;gap:8px}
.field label{font-size:16px;line-height:1.4}
.field input,.field select{width:100%;height:42px;padding:13px 16px;background:#fff;border:1px solid var(--border);border-radius:10px;font:400 16px 'Inter',sans-serif;color:var(--navy)}
.field input:focus,.field select:focus{outline:2px solid var(--tag);outline-offset:1px}
.select-wrap{position:relative}
.select-wrap select{appearance:none;-webkit-appearance:none;padding-right:44px}
.select-wrap::after{content:"";position:absolute;right:18px;top:50%;width:7px;height:7px;border-right:1.8px solid var(--muted);border-bottom:1.8px solid var(--muted);transform:translateY(-70%) rotate(45deg);pointer-events:none}
.tindakan{display:flex;gap:12px}
.btn{display:inline-flex;align-items:center;justify-content:center;height:48px;padding:14px 20px;border-radius:10px;font:500 14px 'Inter',sans-serif;text-decoration:none;cursor:pointer}
.btn-primary{background:var(--maroon);color:#fff;border:0}.btn-primary:hover{background:#6e0a26}
.btn-outline{background:#fff;color:var(--navy);border:1px solid var(--border)}.btn-outline:hover{background:#faf8f5}
.pilihan-kost{display:grid;grid-template-columns:repeat(2,1fr);gap:24px}
.kartu-kost{background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;display:flex;flex-direction:column}
.foto{height:210px;background-size:cover;background-position:center;border-radius:16px}
.foto-kosong{display:flex;align-items:center;justify-content:center;background:var(--sand);color:var(--muted);font-size:13px}
.info{padding:20px;display:flex;flex-direction:column;gap:12px;align-items:flex-start}
.tag-kartu{align-self:flex-start}
.lokasi{display:flex;gap:8px;align-items:center}
.harga{display:flex;gap:8px;align-items:baseline}
.kosong{background:#fff;border:1px dashed var(--border);border-radius:16px;padding:40px;display:flex;flex-direction:column;gap:12px;align-items:flex-start}
@media(max-width:900px){.sidebar{display:none}.row{flex-direction:column}.pilihan-kost{grid-template-columns:1fr}.content{padding:20px}}
</style>
</head>
<body>
<div class="app">

  <aside class="sidebar">
    <div class="logo">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#E2D8CB" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
      <span>kost.io</span>
    </div>
    <div class="workspace">
      <span class="small light">RUANG PENGHUNI</span>
      <span class="label white">Temukan nyamannya</span>
    </div>
    <nav class="menu">
      <a href="<?= e($self) ?>" class="nav-item active">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <span>Cari Kost</span>
      </a>
      <a href="#" class="nav-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
        <span>Status Pengajuan</span>
      </a>
    </nav>
    <div class="spacer"></div>
    <div class="bantuan">
      <span class="label white">Butuh bantuan?</span>
      <span class="small light">Kelola tempat tinggal dengan lebih tenang.</span>
    </div>
    <a href="<?= e($self) ?>?keluar=1" class="nav-item">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      <span>Keluar</span>
    </a>
  </aside>

  <div class="workspace-area">
    <header class="topbar">
      <span class="small muted grow">Kost.io &nbsp;/&nbsp; <?= e($akun['jenisAkun']) ?></span>
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#5B6871" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
      <div class="avatar"><?= e(inisial($akun['namaLengkap'])) ?></div>
      <div class="akun">
        <span class="label navy"><?= e($akun['namaLengkap']) ?></span>
        <span class="small muted"><?= e($akun['jenisAkun']) ?></span>
      </div>
    </header>

    <main class="content">
      <div class="small muted">BERANDA / MENCARI KOST</div>

      <div class="judul">
        <h1 class="title grow">Cari kost yang cocok</h1>
        <span class="tag">Kost.io</span>
      </div>
      <p class="body muted">Temukan tempat tinggal yang sesuai kebutuhan dan anggaranmu.</p>

      <section class="panel-cari">
        <h2 class="title">Temukan ruang untuk ceritamu.</h2>
        <form method="get" action="<?= e($self) ?>" class="form-cari">
          <div class="row">
            <div class="field">
              <label for="q">Lokasi / nama kost</label>
              <input type="text" id="q" name="q" value="<?= e($k['kataKunci']) ?>" placeholder="Contoh: Lowokwaru, Malang">
            </div>
            <div class="field">
              <label for="rentangHarga">Harga per bulan</label>
              <div class="select-wrap">
                <select id="rentangHarga" name="rentangHarga">
                  <?php foreach (KontrolCariKost::OPSI_HARGA as $nilai => $teks): ?>
                    <option value="<?= e($nilai) ?>" <?= $k['rentangHarga'] === (string) $nilai ? 'selected' : '' ?>><?= e($teks) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="field">
              <label for="tipeKost">Tipe kost</label>
              <div class="select-wrap">
                <select id="tipeKost" name="tipeKost">
                  <option value="">Semua tipe kost</option>
                  <?php foreach (KontrolCariKost::OPSI_TIPE as $t): ?>
                    <option value="<?= e($t) ?>" <?= $k['tipeKost'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="field">
              <label for="fasilitas">Fasilitas</label>
              <div class="select-wrap">
                <select id="fasilitas" name="fasilitas">
                  <option value="">Semua fasilitas</option>
                  <?php foreach (KontrolCariKost::OPSI_FASILITAS as $f): ?>
                    <option value="<?= e($f) ?>" <?= $k['fasilitas'] === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="tindakan">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="<?= e($self) ?>" class="btn btn-outline">Reset filter</a>
          </div>
        </form>
      </section>

      <?php if ($jumlah > 0): ?>
        <div class="label navy"><?= $jumlah ?> kost sesuai pencarianmu</div>
        <div class="pilihan-kost">
          <?php foreach ($daftar as $kost): ?>
            <article class="kartu-kost">
              <?php if ($kost->getFotoUtama()): ?>
                <div class="foto" style="background-image:url('<?= e($kost->getFotoUtama()) ?>')"></div>
              <?php else: ?>
                <div class="foto foto-kosong">Foto belum tersedia</div>
              <?php endif; ?>
              <div class="info">
                <span class="tag tag-kartu"><?= e($kost->getTipeKost()) ?></span>
                <h3 class="title"><?= e($kost->getNamaKost()) ?></h3>
                <div class="lokasi small muted">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5B6871" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span><?= e($kost->getLokasi()) ?></span>
                </div>
                <div class="small muted"><?= e(implode(' · ', $kost->getDaftarFasilitas())) ?></div>
                <div class="harga">
                  <span class="title maroon"><?= e(rupiah($kost->getHargaMulai())) ?></span>
                  <span class="small muted">/ bulan</span>
                </div>
                <a href="../controllers/KontrolKost.php?aksi=detailKost&idKost=<?= urlencode($kost->getIdKost()) ?>" class="btn btn-primary">
                    Lihat detail
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="kosong">
          <div class="title"><?= e($kontrol->pesanTidakDitemukan()) ?></div>
          <p class="body muted">Coba ubah kata kunci atau filter pencarianmu.</p>
          <a href="<?= e($kontrol->ubahKriteria()) ?>" class="btn btn-outline">Ubah kriteria</a>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>
</body>
</html>
