<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Berhasil - Kost.io</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #F5F0EA;
            color: #263746;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 244px;
            flex-shrink: 0;
            background: #022E4A;
            color: white;
            padding: 32px 20px 24px;
        }

        .logo {
            font-size: 27px;
            font-weight: bold;
            margin-bottom: 24px;
        }

        .workspace {
            background: #173E54;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .workspace p:first-child {
            color: #E2D8CB;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .workspace p:last-child {
            font-size: 14px;
        }

        .menu {
            display: block;
            color: #E2D8CB;
            text-decoration: none;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .menu.active {
            background: #56061D;
            color: white;
        }

        .help {
            margin-top: 90px;
            padding: 16px;
            background: #173E54;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.6;
        }

        .help p {
            color: #E2D8CB;
            font-size: 13px;
            margin-top: 5px;
        }

        .logout {
            display: block;
            color: #E2D8CB;
            text-decoration: none;
            padding: 12px;
            margin-top: 20px;
            font-size: 14px;
        }

        .main {
            flex: 1;
        }

        .topbar {
            height: 80px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 15px 40px;
            gap: 15px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #E2D8CB;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #56061D;
        }

        .profile {
            font-size: 14px;
            line-height: 1.5;
            color: #022E4A;
        }

        .profile span {
            color: #5B6871;
            font-size: 13px;
        }

        .content {
            padding: 40px;
        }

        .success-card {
            max-width: 650px;
            margin: 80px auto;
            background: white;
            border: 1px solid #D9D5CE;
            border-radius: 16px;
            padding: 45px 35px;
            text-align: center;
        }

        .success-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #E1F2E5;
            color: #267345;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        h1 {
            color: #022E4A;
            font-size: 25px;
            margin-bottom: 12px;
        }

        .success-card p {
            color: #5B6871;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .button {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 9px;
            background: #56061D;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        @media (max-width: 650px) {
            .sidebar {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .success-card {
                margin: 40px auto;
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <div class="logo">kost.io</div>

        <div class="workspace">
            <p>RUANG PENGHUNI</p>
            <p>Temukan nyamannya</p>
        </div>

        <a href="../controllers/KontrolCariKost.php"
           class="menu active">
            Cari Kost
        </a>

        <a href="#" class="menu">
            Status Pengajuan
        </a>

        <div class="help">
            <strong>Butuh bantuan?</strong>
            <p>Kelola tempat tinggal dengan lebih tenang.</p>
        </div>

        <a href="#" class="logout">Keluar</a>
    </aside>

    <main class="main">

        <header class="topbar">
            <span>♧</span>
            <div class="avatar">RA</div>

            <div class="profile">
                <strong>Rifa Aqilla</strong><br>
                <span>Calon Penghuni</span>
            </div>
        </header>

        <section class="content">

            <div class="success-card">

                <div class="success-icon">✓</div>

                <h1>Pengajuan Berhasil Dikirim!</h1>

                <p>
                    Pengajuan sewa kamar kamu telah berhasil disimpan.
                    Silakan menunggu pemilik kost memeriksa dan
                    memproses pengajuan tersebut.
                </p>

                <a
                    href="../controllers/KontrolCariKost.php"
                    class="button"
                >
                    Kembali ke Cari Kost
                </a>

            </div>

        </section>

    </main>
</div>
</body>
</html>