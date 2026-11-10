<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Kost.io</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #082f49;
            background: #f8fafc;
        }

        .halaman {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .intro, .panel-login {
            display: grid;
            align-content: center;
            padding: clamp(24px, 6vw, 80px);
        }

        .intro {
            background: #082f49;
            color: white;
        }

        .intro h1 { font-size: clamp(30px, 4vw, 52px); }

        .panel-login { background: white; }

        .form-login {
            width: 100%;
            max-width: 440px;
            margin-inline: auto;
        }

        .field { margin-bottom: 16px; }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font: inherit;
        }

        input, select {
            border: 1px solid #cbd5e1;
            background: white;
        }

        button {
            border: 0;
            background: #680e26;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .pesan-error {
            padding: 12px;
            border-radius: 8px;
            background: #fff1f2;
            color: #9f1239;
            margin-bottom: 16px;
        }

        .pesan-error ul {
            margin: 0;
            padding-left: 20px;
        }

        a { color: #680e26; }

        @media (max-width: 720px) {
            .halaman { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="halaman">
        <aside class="intro">
            <strong>kost.io</strong>
            <h1>Ruang nyaman.<br>Cerita baru.</h1>
            <p>Cari kost, kelola hunian, dan ajukan keluhan dalam satu tempat.</p>
        </aside>

        <section class="panel-login" aria-labelledby="judul-login">
            <div class="form-login">
                <h2 id="judul-login">Masuk ke Kost.io</h2>
                <p>Gunakan email dan kata sandi yang kamu daftarkan.</p>

                @if (session('error') || $errors->any())
                    <div class="pesan-error" role="alert">
                        @if (session('error'))
                            <div>{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <ul>
                                @foreach ($errors->all() as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('login.proses') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="kata_sandi">Kata sandi</label>
                        <input
                            type="password"
                            id="kata_sandi"
                            name="kata_sandi"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="peran">Masuk sebagai</label>
                        <select id="peran" name="peran" required>
                            <option value="" @selected(!old('peran')) disabled>
                                Pilih peran
                            </option>
                            <option value="Calon Penghuni" @selected(old('peran') === 'Calon Penghuni')>
                                Calon Penghuni
                            </option>
                            <option value="Penghuni" @selected(old('peran') === 'Penghuni')>
                                Penghuni
                            </option>
                            <option value="Pemilik Kost" @selected(old('peran') === 'Pemilik Kost')>
                                Pemilik Kost
                            </option>
                        </select>
                    </div>

                    <button type="submit">Masuk</button>
                </form>

                <p>Belum punya akun? <a href="{{ route('registrasi.form') }}">Daftar akun</a></p>
            </div>
        </section>
    </main>
</body>
</html>