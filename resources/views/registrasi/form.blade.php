@php
    // <<boundary>> FormRegistrasiPengguna
    // UI-01.01 (form), UI-01.02 (periksa kembali data akun), UI-01.03 (akun berhasil dibuat)
    $fields = [
        'jenisAkun'           => ['label' => 'Jenis akun',            'type' => 'select'],
        'nama'                => ['label' => 'Nama lengkap',          'type' => 'text',     'ph' => 'Nama lengkap',   'max' => 100, 'ac' => 'name'],
        'email'               => ['label' => 'Email',                 'type' => 'email',    'ph' => 'nama@email.com', 'max' => 254, 'ac' => 'email'],
        'noTelepon'           => ['label' => 'Nomor telepon',         'type' => 'tel',      'ph' => '08xxxxxxxxxx',   'max' => 20,  'ac' => 'tel'],
        'kataSandi'           => ['label' => 'Kata sandi',            'type' => 'password', 'ph' => 'Kata sandi',      'ac' => 'new-password'],
        'konfirmasiKataSandi' => ['label' => 'Konfirmasi kata sandi', 'type' => 'password', 'ph' => 'Ulangi kata sandi', 'ac' => 'new-password'],
    ];

    // UI-01.02 hanya menampilkan field yang bermasalah.
    // Jika kata sandi bermasalah, konfirmasinya ikut ditampilkan agar bisa diisi ulang.
    $tampilSemua = ($status === 'form') || $kesalahan === [];
    if ($tampilSemua) {
        $tampil = array_keys($fields);
    } else {
        $tampil = array_keys($kesalahan);
        if (isset($kesalahan['kataSandi'])) {
            $tampil[] = 'konfirmasiKataSandi';
        }
        $tampil = array_values(array_intersect(array_keys($fields), $tampil));
    }

    $judul = [
        'form'     => 'Buat akun Kost.io',
        'gagal'    => 'Periksa kembali data akun',
        'berhasil' => 'Akun berhasil dibuat',
    ][$status];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} | Kost.io</title>
    <style>
        :root {
            --navy: #012d4b; --maroon: #5a0a24; --steel: #4f7794; --sand: #e2d9cc;
            --muted: #5f6b76; --line: #cfd6dd;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body { font-family: Inter, "Segoe UI", Arial, sans-serif; color: var(--navy); background: #fff; }

        .page { display: grid; grid-template-columns: 43% 1fr; min-height: 100vh; }

        /* ---------- Panel kiri ---------- */
        .side { background: var(--navy); color: #fff; padding: 48px; display: flex; flex-direction: column; gap: 24px; }
        .brand { display: flex; align-items: center; gap: 14px; font-size: 28px; font-weight: 700; }
        .side h2 { margin: 16px 0 0; font-size: 36px; line-height: 1.45; }
        .side .desc { margin: 0; font-size: 15px; color: #e3e9ee; }
        .foto { width: 100%; aspect-ratio: 4 / 3; border-radius: 20px;
                background: url('{{ asset('images/kamar.jpg') }}') center / cover no-repeat,
                            linear-gradient(135deg, #3c5f78, #8aa4b7); }
        .points { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; font-size: 15px; }
        .points li { display: flex; align-items: center; gap: 14px; }

        /* ---------- Panel kanan ---------- */
        .main { display: flex; align-items: center; padding: 48px; }
        .content { width: 100%; max-width: 600px; margin: 0 auto; }
        .eyebrow { font-size: 13px; letter-spacing: .02em; color: var(--maroon); text-transform: uppercase; }
        h1 { margin: 22px 0 18px; font-size: 30px; }
        .sub { margin: 0 0 22px; color: var(--muted); font-size: 16px; }
        .small { font-size: 14px; color: var(--muted); }
        .small a { color: var(--navy); text-decoration: none; }
        .small a:hover { text-decoration: underline; }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px 20px; margin: 22px 0 24px; }
        .field label { display: block; margin-bottom: 8px; font-size: 16px; }
        .field input, .field select {
            width: 100%; height: 44px; padding: 0 16px; font: inherit; font-size: 16px;
            color: var(--navy); background: #fff; border: 1px solid var(--line); border-radius: 8px;
        }
        .field input::placeholder { color: #7c8791; }
        .field.has-error input, .field.has-error select { border-color: var(--maroon); }
        .err { margin-top: 8px; color: var(--maroon); font-size: 16px; }

        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 22px; }
        .btn { display: inline-block; padding: 12px 20px; font: inherit; font-size: 14px; font-weight: 600;
               border-radius: 8px; border: 1px solid var(--maroon); cursor: pointer; text-decoration: none; }
        .btn-primary { background: var(--maroon); color: #fff; }
        .btn-primary:hover { background: #480819; }
        .btn-outline { background: #fff; color: var(--navy); border-color: var(--line); }
        .btn-outline:hover { background: #f4f6f8; }

        .banner { display: flex; align-items: center; gap: 12px; padding: 16px 20px; margin-bottom: 24px;
                  background: var(--steel); color: #fff; border-radius: 8px; font-size: 16px; }
        .icon-box { width: 64px; height: 64px; margin: 22px 0 10px; display: grid; place-items: center;
                    background: #e8eef4; border-radius: 16px; }
        .notice { padding: 20px; margin-bottom: 24px; background: var(--sand); border-radius: 8px; }
        .notice strong { display: block; margin-bottom: 8px; font-size: 14px; }
        .notice span { color: var(--muted); font-size: 16px; }
        .foot { margin: 0 0 14px; font-size: 14px; color: var(--muted); }

        @media (max-width: 860px) {
            .page { grid-template-columns: 1fr; }
            .side { padding: 24px; }
            .side h2, .side .desc, .foto, .points { display: none; }
            .main { padding: 28px 24px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- Panel kiri (sama di semua layar) -->
    <aside class="side">
        <div class="brand">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V21H3z"/><path d="M9 21v-7h6v7"/></svg>
            kost.io
        </div>
        <h2>Ruang nyaman.<br>Cerita baru.</h2>
        <p class="desc">Dari mencari kamar hingga mengelola hunian, semua terasa lebih mudah.</p>
        <div class="foto" role="img" aria-label="Kamar kost"></div>
        <ul class="points">
            @foreach (['Pilihan kamar sesuai kebutuhan', 'Pengajuan sewa yang jelas', 'Tagihan dan keluhan dalam satu tempat'] as $poin)
                <li>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>
                    {{ $poin }}
                </li>
            @endforeach
        </ul>
    </aside>

    <!-- Panel kanan -->
    <main class="main">
        <div class="content">
            <div class="eyebrow">Mulai dari sini</div>

            @if ($status === 'berhasil')
                <!-- ===== UI-01.03 Akun berhasil dibuat ===== -->
                <div class="icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4f7794" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>
                </div>
                <h1 style="margin-top:14px">Akun berhasil dibuat</h1>
                <p class="sub">Buat langkah pertamamu menuju tempat tinggal yang nyaman.</p>

                <div class="notice">
                    <strong>Registrasi berhasil</strong>
                    <span>Akun dapat digunakan untuk masuk ke Kost.io.</span>
                </div>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('registrasi.login') }}">Masuk sekarang</a>
                </div>

            @else
                <!-- ===== UI-01.01 Buat akun / UI-01.02 Periksa kembali data akun ===== -->
                <h1>{{ $judul }}</h1>
                <p class="sub">Buat langkah pertamamu menuju tempat tinggal yang nyaman.</p>

                @if ($status === 'form')
                    <p class="small">Sudah punya akun? <a href="{{ route('registrasi.login') }}">Masuk</a></p>
                @else
                    <div class="banner" role="alert">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
                        {{ $banner }}
                    </div>
                @endif

                <form method="post" action="{{ route('registrasi.simpan') }}" novalidate>
                    @csrf
                    <div class="grid">
                        @foreach ($tampil as $k)
                            @php
                                $f   = $fields[$k];
                                $err = $kesalahan[$k] ?? '';
                            @endphp
                            <div class="field{{ $err !== '' ? ' has-error' : '' }}">
                                <label for="{{ $k }}">{{ $f['label'] }} *</label>

                                @if ($f['type'] === 'select')
                                    <select id="{{ $k }}" name="{{ $k }}">
                                        @foreach ($jenisAkunPilihan as $opsi)
                                            <option value="{{ $opsi }}" @selected($data[$k] === $opsi)>{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $f['type'] }}" id="{{ $k }}" name="{{ $k }}"
                                           placeholder="{{ $f['ph'] }}"
                                           @isset($f['max']) maxlength="{{ $f['max'] }}" @endisset
                                           autocomplete="{{ $f['ac'] }}"
                                           value="{{ $f['type'] === 'password' ? '' : $data[$k] }}">
                                @endif

                                @if ($err !== '')
                                    <div class="err">{{ $err }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Field yang tidak ditampilkan (UI-01.02) dibawa lewat hidden input --}}
                    @foreach ($fields as $k => $f)
                        @unless (in_array($k, $tampil, true))
                            <input type="hidden" name="{{ $k }}" value="{{ $data[$k] }}">
                        @endunless
                    @endforeach

                    <div class="actions">
                        @if ($status === 'form')
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        @else
                            <button type="submit" class="btn btn-primary">Perbaiki data</button>
                            <a class="btn btn-outline" href="{{ route('registrasi.login') }}">Ke halaman masuk</a>
                        @endif
                    </div>
                </form>

                @if ($status === 'form')
                    <p class="foot">* Wajib diisi. Gunakan email yang dapat diakses.</p>
                @endif
            @endif

            <p class="foot">Dengan melanjutkan, kamu menggunakan akun sesuai peran yang terdaftar.</p>
        </div>
    </main>
</div>
</body>
</html>