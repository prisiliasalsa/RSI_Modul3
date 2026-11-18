@php
    $akun = $akunLoginLaravel ?? [];
    $nama = $akun['namaLengkap'] ?? 'Calon Penghuni';
    $peran = $akun['jenisAkun'] ?? 'Calon Penghuni';
    $inisial = collect(explode(' ', trim($nama)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');

    $foto = $kost['fotoUtama'] ?? null;
    $lokasi = ($kost['lokasi'] ?? '') ?: ($kost['alamat'] ?? '');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kost['namaKost'] }} | Kost.io</title>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">

    <style>
        :root {
            --navy: #022e4a;
            --panel: #173e54;
            --maroon: #56061d;
            --canvas: #f5f0ea;
            --sand: #e2d8cb;
            --steel: #4f7794;
            --muted: #5b6871;
            --line: #ddd5ca;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--canvas);
            color: var(--navy);
            font: 15px/1.45 Inter, Arial, sans-serif;
        }

        .app {
            display: grid;
            grid-template-columns: 244px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 32px 20px 24px;
            background: var(--navy);
            color: white;
        }

        .logo { font-size: 27px; font-weight: 600; }

        .sidebar-box {
            display: grid;
            gap: 4px;
            padding: 12px;
            border-radius: 10px;
            background: var(--panel);
        }

        .sidebar-box small { color: var(--sand); font-size: 13px; }
        .sidebar-box strong { font-size: 14px; font-weight: 500; }
        .sidebar nav { display: grid; gap: 8px; }
        .sidebar-spacer { flex: 1; }
        .help { padding: 16px; border-radius: 16px; }

        .nav-link {
            display: block;
            padding: 12px;
            border-radius: 10px;
            color: var(--sand);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-link:hover { background: var(--panel); }
        .nav-link.active { background: var(--maroon); color: white; }

        .topbar {
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 24px;
            background: white;
            color: var(--muted);
            font-size: 13px;
        }

        .akun { display: flex; align-items: center; gap: 16px; }
        .akun strong { display: block; color: var(--navy); font-weight: 500; }

        .avatar {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            flex: none;
            border-radius: 50%;
            background: var(--sand);
            color: var(--maroon);
            font-weight: 600;
        }

        main { padding: 40px; }

        .breadcrumb {
            margin: 0 0 24px;
            color: var(--muted);
            font-size: 13px;
        }

        .breadcrumb a { color: inherit; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        .judul {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .subtitle { margin: 24px 0; color: var(--muted); }

        .tag {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--steel);
            color: white;
            font-size: 13px;
        }

        .foto {
            display: block;
            width: 100%;
            height: 252px;
            border-radius: 16px;
            object-fit: cover;
        }

        .foto-kosong {
            display: grid;
            place-items: center;
            background: var(--sand);
            color: var(--muted);
        }

        .ringkasan {
            display: grid;
            gap: 4px;
            margin: 24px 0;
            padding: 20px;
            border-radius: 10px;
            background: var(--sand);
            color: var(--muted);
        }

        .ringkasan strong { color: var(--navy); font-weight: 500; }

        .daftar {
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
        }

        .daftar-atas, .daftar-bawah {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 22px 24px;
        }

        .daftar-atas h2 { margin: 0; font-size: 14px; font-weight: 500; }

        .daftar-atas input {
            width: min(100%, 290px);
            padding: 12px 16px;
            border: 0;
            border-radius: 10px;
            background: var(--canvas);
            color: var(--navy);
            font: inherit;
            font-size: 13px;
        }

        table { width: 100%; min-width: 660px; border-collapse: collapse; }

        th, td { padding: 16px 24px; text-align: left; }

        th {
            background: var(--canvas);
            color: var(--muted);
            font-size: 13px;
            font-weight: 400;
        }

        td { border-bottom: 1px solid var(--line); color: var(--muted); }
        td:first-child { color: var(--navy); font-weight: 500; }

        .status-tersedia { background: #e3edf3; color: #355e78; }
        .tautan-detail { color: var(--maroon); font-weight: 500; }
        .daftar-bawah { color: var(--muted); font-size: 13px; }

        .aksi { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }

        .tombol {
            padding: 13px 20px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: white;
            color: var(--navy);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .tombol-utama { background: var(--maroon); border-color: var(--maroon); color: white; }

        @media (max-width: 900px) {
            .app { grid-template-columns: minmax(0, 1fr); }
            .sidebar { position: static; height: auto; padding: 16px 20px; gap: 12px; }
            .sidebar nav { display: flex; flex-wrap: wrap; }
            .sidebar-box, .sidebar-spacer { display: none; }
            main { padding: 24px 20px; }
            .foto { height: 200px; }
        }
    </style>
    @include('layouts.partials.kostio-ui-assets')
</head>
<body class="kio-ready kio-resident">
    @include('layouts.partials.kostio-ui-shell', ['kioRenderServer' => true])
    <div class="app">
        <aside class="sidebar kio-sidebar">
            @yield('kioResidentSidebar')
        </aside>

        <div>
            <header class="topbar kio-header">
            @yield('kioResidentHeader')
        </header>

            <main class="kio-page">
                <p class="breadcrumb">
                    <a href="{{ route('kost.cari') }}">BERANDA</a>
                    / MELIHAT DETAIL KAMAR KOST
                </p>

                <div class="judul">
                    <h1>{{ $kost['namaKost'] }}</h1>
                    <span class="tag">Kost.io</span>
                </div>

                <p class="subtitle">Kenali fasilitas, harga, dan ketersediaan sebelum menentukan pilihan.</p>

                @if ($foto)
                    <img class="foto" src="{{ $foto }}" alt="Foto {{ $kost['namaKost'] }}">
                @else
                    <div class="foto foto-kosong">Foto kost belum tersedia</div>
                @endif

                <section class="ringkasan" aria-label="Informasi kost">
                    <strong>
                        {{ $lokasi }}
                        @if (!empty($kost['tipeKost']))
                            · Kost {{ $kost['tipeKost'] }}
                        @endif
                    </strong>

                    @if (!empty($kost['fasilitasUmum']))
                        <span>{{ $kost['fasilitasUmum'] }}</span>
                    @endif

                    @if (!empty($kost['peraturan']) || !empty($kost['kontakPemilik']))
                        <span>
                            @if (!empty($kost['peraturan']))
                                Peraturan: {{ $kost['peraturan'] }}
                            @endif
                            @if (!empty($kost['kontakPemilik']))
                                Kontak pemilik: {{ $kost['kontakPemilik'] }}
                            @endif
                        </span>
                    @endif
                </section>

                <section class="daftar" aria-labelledby="judul-daftar">
                    <div class="daftar-atas">
                        <h2 id="judul-daftar">Daftar data</h2>
                        <input id="cari-kamar" type="search" placeholder="Cari data..."
                               aria-label="Cari kamar">
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Kamar</th>
                                <th scope="col">Tipe / ukuran</th>
                                <th scope="col">Harga / bulan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarKamar as $item)
                                <tr data-kamar>
                                    <td>{{ $item['nomorKamar'] }}</td>
                                    <td>
                                        {{ $item['tipeKamar'] }}
                                        @if (!empty($item['ukuranKamar']))
                                            · {{ $item['ukuranKamar'] }}
                                        @endif
                                    </td>
                                    <td>Rp{{ number_format((float) $item['hargaSewa'], 0, ',', '.') }}</td>
                                    <td>
                                        <span @class([
                                            'tag',
                                            'status-tersedia' => $item['statusKamar'] === 'Tersedia',
                                        ])>{{ $item['statusKamar'] }}</span>
                                    </td>
                                    <td>
                                        <a class="tautan-detail"
                                           href="{{ route('kamar.detail', ['idKamar' => $item['idKamar']]) }}">
                                            Lihat detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="kamar-kosong" @if (count($daftarKamar)) hidden @endif>
                                <td colspan="5">Tidak ada kamar yang ditampilkan.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="daftar-bawah">
                        <span id="jumlah-kamar" aria-live="polite">
                            Menampilkan {{ count($daftarKamar) }} data
                        </span>
                    </div>
                </section>

                @if (count($daftarKamar))
                    <div class="aksi">
                        @foreach (array_slice($daftarKamar, 0, 2) as $item)
                            <a @class(['tombol', 'tombol-utama' => $loop->first])
                               href="{{ route('kamar.detail', ['idKamar' => $item['idKamar']]) }}">
                                Lihat kamar {{ $item['nomorKamar'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>

    <script>
        const pencarian = document.getElementById('cari-kamar');
        const barisKamar = document.querySelectorAll('[data-kamar]');
        const jumlahKamar = document.getElementById('jumlah-kamar');
        const kamarKosong = document.getElementById('kamar-kosong');

        pencarian.addEventListener('input', () => {
            const kataKunci = pencarian.value.trim().toLowerCase();
            let jumlahTampil = 0;

            barisKamar.forEach((baris) => {
                const cocok = baris.textContent.toLowerCase().includes(kataKunci);
                baris.hidden = !cocok;

                if (cocok) {
                    jumlahTampil++;
                }
            });

            kamarKosong.hidden = jumlahTampil > 0;
            jumlahKamar.textContent = `Menampilkan ${jumlahTampil} data`;
        });
    </script>
</body>
</html>