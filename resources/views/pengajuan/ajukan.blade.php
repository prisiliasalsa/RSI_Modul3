@php
    $harga = 'Rp' . number_format((float) $kamar->hargaSewa, 0, ',', '.');
    $detailKamar = route('kamar.detail', ['idKamar' => $kamar->idKamar]);
    $foto = $kamar->urlFoto ?? null;
    $namaPemohon = $namaPemohon ?? session('namaLengkap');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajukan sewa kamar {{ $kamar->nomorKamar }} | Kost.io</title>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">

    <style>
        :root {
            --navy: #022e4a;
            --sidebar-panel: #173e54;
            --maroon: #56061d;
            --canvas: #f5f0ea;
            --sand: #e2d8cb;
            --muted: #5b6871;
            --line: #ddd5ca;
            --steel: #4f7794;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--navy);
            background: var(--canvas);
            font: 15px/1.45 Inter, Arial, sans-serif;
        }

        .app {
            display: grid;
            grid-template-columns: 244px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: sticky;
            top: 0;
            min-height: 100vh;
            height: fit-content;
            padding: 32px 20px 24px;
            background: var(--navy);
            color: #fff;
        }

        .logo { font-size: 27px; font-weight: 600; }

        .sidebar-box {
            display: grid;
            gap: 4px;
            padding: 12px;
            border-radius: 10px;
            background: var(--sidebar-panel);
        }

        .sidebar-box small { color: var(--sand); font-size: 13px; }
        .sidebar-box strong { font-size: 14px; font-weight: 500; }
        .sidebar nav { display: grid; gap: 8px; }
        .sidebar-spacer { flex: 1; }
        .sidebar .bantuan { padding: 16px; border-radius: 16px; }

        .nav-link {
            padding: 12px;
            border-radius: 10px;
            color: var(--sand);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-link:hover { background: var(--sidebar-panel); }
        .nav-link.aktif { background: var(--maroon); color: #fff; }

        .topbar {
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 24px;
            background: #fff;
            color: var(--muted);
            font-size: 13px;
        }

        main { padding: 40px; }
        .jejak { margin: 0 0 24px; color: var(--muted); font-size: 13px; }

        .judul {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .subjudul { margin: 24px 0; color: var(--muted); }

        .tag {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--steel);
            color: #fff;
            font-size: 13px;
        }

        .kolom {
            display: grid;
            grid-template-columns: minmax(0, 2.3fr) minmax(0, 1fr);
            align-items: start;
            gap: 24px;
        }

        .kartu {
            padding: 26px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
        }

        .ringkasan {
            display: grid;
            gap: 6px;
            margin-bottom: 20px;
            padding: 18px 20px;
            border-radius: 10px;
            background: var(--sand);
        }

        .ringkasan span, .muted { color: var(--muted); }

        .bidang {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
            gap: 18px 20px;
        }

        .field { min-width: 0; }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        input, textarea {
            width: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--navy);
            font: inherit;
        }

        textarea { min-height: 88px; resize: vertical; }

        .unggah {
            min-width: 0;
            padding: 14px;
            border: 1px dashed var(--line);
            border-radius: 10px;
            background: var(--canvas);
        }

        .unggah input {
            min-height: 0;
            padding: 0;
            border: 0;
            background: transparent;
            font-size: 13px;
        }

        .unggah input::file-selector-button {
            margin: 0 8px 6px 0;
            padding: 7px 10px;
            border: 0;
            border-radius: 6px;
            background: var(--sand);
            color: var(--maroon);
            font: inherit;
            cursor: pointer;
        }

        .unggah small { display: block; color: var(--muted); }

        .aksi { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }

        .btn {
            display: inline-block;
            padding: 13px 18px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--navy);
            font: 500 14px/1.45 Inter, Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-utama {
            border-color: var(--maroon);
            background: var(--maroon);
            color: #fff;
        }

        .preview { padding: 22px; }

        .foto {
            display: block;
            width: 100%;
            aspect-ratio: 5 / 3;
            border-radius: 14px;
            object-fit: cover;
        }

        .foto-kosong {
            display: grid;
            place-items: center;
            padding: 12px;
            background: var(--canvas);
            color: var(--muted);
            text-align: center;
        }

        .preview h2 { margin: 16px 0; font-size: 28px; font-weight: 600; }
        .preview p { margin: 12px 0; }
        .preview .harga { margin-bottom: 0; color: var(--maroon); font-size: 28px; font-weight: 600; }

        .galat {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 8px;
            background: #f6e3e8;
            color: var(--maroon);
        }

        .galat ul { margin: 6px 0 0; padding-left: 20px; }

        @media (max-width: 1100px) {
            .kolom { grid-template-columns: minmax(0, 1fr); }
        }

        @media (max-width: 900px) {
            .app { grid-template-columns: minmax(0, 1fr); }
            .sidebar { position: static; min-height: 0; padding: 16px 20px; gap: 12px; }
            .sidebar nav { display: flex; flex-wrap: wrap; }
            .sidebar-box, .sidebar-spacer { display: none; }
            main { padding: 24px 20px; }
        }

        @media (max-width: 580px) {
            .bidang { grid-template-columns: minmax(0, 1fr); }
            .kartu { padding: 18px; }
            h1, .preview h2 { font-size: 24px; }
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
            <p class="jejak">BERANDA / MENGAJUKAN SEWA</p>

            <div class="judul">
                <h1>Ajukan sewa kamar {{ $kamar->nomorKamar }}</h1>
                <span class="tag">Kost.io</span>
            </div>

            <p class="subjudul">Lengkapi informasi untuk mengajukan sewa kamar pilihanmu.</p>

            <div class="kolom">
                <form class="kartu"
                      action="{{ route('pengajuan.simpan', ['idKamar' => $kamar->idKamar]) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="ringkasan">
                        <strong>{{ $kamar->namaKost }} · {{ $kamar->nomorKamar }}</strong>
                        <span>{{ $harga }} / bulan · Tersedia</span>
                    </div>

                    @if ($errors->any())
                        <div class="galat" role="alert">
                            <strong>Lengkapi pengajuan sewa</strong>
                            <ul>
                                @foreach ($errors->all() as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="bidang">
                        <div class="field">
                            <label for="tanggalMulaiSewa">Tanggal mulai sewa *</label>
                            <input type="date" id="tanggalMulaiSewa" name="tanggalMulaiSewa"
                                   value="{{ old('tanggalMulaiSewa') }}"
                                   min="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="field">
                            <label for="durasiSewa">Durasi sewa (bulan) *</label>
                            <input type="number" id="durasiSewa" name="durasiSewa"
                                   value="{{ old('durasiSewa', 1) }}" min="1" required>
                        </div>

                        <div class="field">
                            <label for="jumlahPenghuni">Jumlah penghuni *</label>
                            <input type="number" id="jumlahPenghuni" name="jumlahPenghuni"
                                   value="{{ old('jumlahPenghuni', 1) }}" min="1" required>
                        </div>

                        <div class="field">
                            <label for="berkasKTP">Dokumen KTP *</label>
                            <div class="unggah">
                                <input type="file" id="berkasKTP" name="berkasKTP"
                                       accept="image/jpeg,image/png,application/pdf"
                                       aria-describedby="aturan-ktp" required>
                                <small id="aturan-ktp">JPG, PNG, atau PDF · maksimal 2 MB</small>
                            </div>
                        </div>

                        <div class="field">
                            <label for="catatan">Catatan</label>
                            <textarea id="catatan" name="catatan" maxlength="1000"
                                      placeholder="Tambahkan catatan">{{ old('catatan') }}</textarea>
                        </div>

                        @if ($namaPemohon)
                            <div class="field">
                                <label for="namaPemohon">Nama pemohon</label>
                                <input id="namaPemohon" value="{{ $namaPemohon }}" readonly>
                            </div>
                        @endif
                    </div>

                    <div class="aksi">
                        <button class="btn btn-utama" type="submit">Kirim Pengajuan</button>
                        <a class="btn" href="{{ $detailKamar }}">Batal</a>
                    </div>
                </form>

                <aside class="kartu preview" aria-label="Ringkasan kost">
                    @if ($foto)
                        <img class="foto" src="{{ $foto }}" alt="Foto {{ $kamar->namaKost }}">
                    @else
                        <div class="foto foto-kosong">Foto belum tersedia</div>
                    @endif

                    <h2>{{ $kamar->namaKost }}</h2>
                    @if (!empty($kamar->lokasi))
                        <p class="muted">{{ $kamar->lokasi }}</p>
                    @endif

                    <span class="tag">Hunian nyaman</span>
                    <p class="muted">Harga sewa / bulan</p>
                    <p class="harga">{{ $harga }}</p>
                </aside>
            </div>
        </main>
    </div>
</div>
</body>
</html>