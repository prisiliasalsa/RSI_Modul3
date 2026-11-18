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

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 48px;
        }

        .password-field .password-toggle {
            position: absolute;
            inset: 0 2px 0 auto;
            display: grid;
            place-items: center;
            width: 44px;
            padding: 0;
            color: #536572;
            background: transparent;
        }

        .password-field .password-toggle[hidden],
        .password-toggle[aria-pressed="false"] .eye-slash {
            display: none;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #680e26;
            outline-offset: -3px;
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
    @include('layouts.partials.kostio-ui-assets')
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
                        <div class="password-field">
                            <input
                                type="password"
                                id="kata_sandi"
                                name="kata_sandi"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="password-toggle" id="password-toggle"
                                    aria-controls="kata_sandi" aria-label="Tampilkan kata sandi"
                                    aria-pressed="false" hidden>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                    <path class="eye-slash" d="m3 3 18 18"/>
                                </svg>
                            </button>
                        </div>
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
    @include('layouts.partials.kostio-ui-shell')
    <script>
        (() => {
            const password = document.getElementById('kata_sandi');
            const toggle = document.getElementById('password-toggle');

            toggle.addEventListener('click', () => {
                const visible = password.type === 'password';
                password.type = visible ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(visible));
            });

            toggle.hidden = false;
        })();
    </script>
</body>
</html>