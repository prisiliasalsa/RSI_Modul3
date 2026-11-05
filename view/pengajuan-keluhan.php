<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan Keluhan | Kost.io</title>
    <style>
        body { margin: 0; background: #f5f7fa; color: #182230; font: 16px system-ui; }
        main { max-width: 560px; margin: 32px auto; padding: 24px; background: white; border-radius: 12px; }
        label { display: block; margin-top: 16px; font-weight: 600; }
        textarea { box-sizing: border-box; width: 100%; padding: 10px; font: inherit; }
        textarea, input, small { display: block; margin-top: 8px; }
        button { padding: 10px 18px; border: 0; border-radius: 6px; background: #2459bf; color: white; font: inherit; }
        [role="status"] { padding: 12px; background: #eef4ff; }
    </style>
</head>
<body>
<main>
    <p>Kost.io</p>
    <h1>Pengajuan Keluhan atau Kerusakan</h1>
    <p>Jelaskan keluhan atau kerusakan yang ingin kamu laporkan.</p>

    <?php if ($pesan !== ''): ?>
        <p role="status"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="KeluhanController.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

        <label for="deskripsi">Deskripsi keluhan</label>
        <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="2000"
                  required><?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="foto">Foto bukti kerusakan (opsional)</label>
        <input id="foto" name="foto" type="file" accept="image/jpeg,image/png" aria-describedby="format-foto">
        <small id="format-foto">JPG atau PNG, maksimal 2 MB.</small>

        <p><button type="submit">Kirim Laporan</button></p>
    </form>
</main>
</body>
</html>
