@php
    $rupiah = fn ($nilai) => 'Rp' . number_format((float) $nilai, 0, ',', '.');

    $statusBisaBayar = ['Belum Dibayar', 'Belum Lunas', 'Ditolak'];
    $peran = session('jenisAkun', 'Penghuni');
    $nama = session('namaLengkap') ?: $peran;

    $tagihanTerbaru = $tagihan->first();
    $tagihanBayar = $tagihan->firstWhere('idTagihan', request()->integer('bayar'));

    $jumlahBelumLunas = $tagihan
        ->reject(fn ($item) => strtolower(trim((string) $item->status)) === 'lunas')
        ->sum('jumlah');

    $formatPeriode = fn ($periode) => $periode
        ? \Illuminate\Support\Carbon::parse($periode . '-01')->locale('id')->isoFormat('MMMM YYYY')
        : '—';

    $formatTanggal = fn ($tanggal) => $tanggal
        ? \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->isoFormat('DD MMM YYYY')
        : '—';

    $judul = $tagihanBayar ? 'Bayar tagihan ' . $formatPeriode($tagihanBayar->periode) : 'Tagihan sewa';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} | Kost.io</title>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">

    <style>
        :root {
            --navy: #022e4a;
            --biru-panel: #173e54;
            --maroon: #56061d;
            --latar: #f5f0ea;
            --pasir: #e2d8cb;
            --muted: #5b6871;
            --garis: #ddd5ca;
            --biru: #4f7794;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--navy);
            background: var(--latar);
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
            background: var(--biru-panel);
        }

        .sidebar-box small { color: var(--pasir); }
        .sidebar-box strong { font-size: 14px; font-weight: 500; }
        .sidebar nav { display: grid; gap: 8px; }
        .sidebar-spacer { flex: 1; }

        .nav-link {
            padding: 12px;
            border-radius: 10px;
            color: var(--pasir);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-link:hover { background: var(--biru-panel); }
        .nav-link[aria-current="page"] { background: var(--maroon); color: white; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            min-height: 80px;
            padding: 16px 24px;
            background: white;
            color: var(--muted);
            font-size: 13px;
        }

        .profil { text-align: right; }
        .profil strong { display: block; color: var(--navy); font-weight: 500; }
        main { padding: 40px; }
        .jejak { margin: 0 0 24px; color: var(--muted); font-size: 13px; }

        .baris-judul {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        h1 { margin: 0; font-size: 28px; font-weight: 600; }
        h2 { margin: 0; font-size: 22px; font-weight: 600; }
        .sub { margin: 24px 0; color: var(--muted); }

        .tag, .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
        }

        .tag { background: var(--biru); color: white; }
        .status { background: #f6e3e8; color: var(--maroon); }
        .status.lunas { background: #e3edf3; color: #355e78; }
        .status.menunggu { background: var(--pasir); color: var(--navy); }

        .ringkasan {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .kartu, .daftar {
            border: 1px solid var(--garis);
            border-radius: 16px;
            background: white;
        }

        .kartu { min-width: 0; padding: 24px; }
        .kartu small, .daftar-bawah { color: var(--muted); }
        .nilai { display: block; margin-top: 8px; font-size: 26px; font-weight: 600; }
        .daftar { overflow: hidden; }

        .daftar-atas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 20px 24px;
        }

        input, select, button { font: inherit; }

        input[type="search"] {
            width: min(100%, 290px);
            padding: 12px 16px;
            border: 0;
            border-radius: 10px;
            background: var(--latar);
            color: var(--navy);
        }

        .tabel { overflow-x: auto; }
        table { width: 100%; min-width: 580px; border-collapse: collapse; }
        th, td { padding: 16px 24px; text-align: left; }

        th {
            background: var(--latar);
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
        }

        td { border-bottom: 1px solid var(--garis); }
        .daftar-bawah { padding: 16px 24px; font-size: 13px; }
        .tautan { color: var(--maroon); font-weight: 500; text-underline-offset: 3px; }
        summary { color: var(--maroon); cursor: pointer; }

        .bayar-layout {
            display: grid;
            grid-template-columns: minmax(0, 2.3fr) minmax(0, 1fr);
            align-items: start;
            gap: 24px;
        }

        .bayar-ringkas, .tujuan {
            display: grid;
            gap: 5px;
            padding: 16px 20px;
            border-radius: 10px;
            background: var(--pasir);
        }

        .bayar-ringkas span, .tujuan span, .preview p { color: var(--muted); }
        .tujuan { margin-top: 16px; }
        .tujuan[hidden] { display: none; }

        .bayar-form label {
            display: block;
            margin: 18px 0 6px;
            font-size: 14px;
            font-weight: 500;
        }

        .bayar-form select {
            width: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid var(--garis);
            border-radius: 10px;
            background: white;
            color: var(--navy);
        }

        .unggah {
            padding: 16px;
            border: 1px dashed var(--garis);
            border-radius: 10px;
            background: var(--latar);
        }

        .unggah input { display: block; width: 100%; }
        .unggah small { display: block; margin-top: 8px; }

        .unggah input::file-selector-button {
            margin-right: 10px;
            padding: 8px 12px;
            border: 0;
            border-radius: 7px;
            background: var(--pasir);
            color: var(--maroon);
            font: inherit;
            cursor: pointer;
        }

        .aksi { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }

        .tombol {
            display: inline-block;
            padding: 11px 18px;
            border: 1px solid var(--garis);
            border-radius: 10px;
            background: white;
            color: var(--navy);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
        }

        .tombol.utama { border-color: var(--maroon); background: var(--maroon); color: white; }
        .tombol.utama:hover { background: #440517; }
        .preview .nilai { color: var(--maroon); }

        .pesan {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 10px;
            background: #e3edf3;
        }

        .pesan.galat { background: #f6e3e8; color: var(--maroon); }
        .pesan ul { margin: 0; padding-left: 20px; }

        :is(a, button, input, select, summary):focus-visible {
            outline: 2px solid var(--biru);
            outline-offset: 2px;
        }

        @media (max-width: 1050px) {
            .bayar-layout { grid-template-columns: minmax(0, 1fr); }
        }

        @media (max-width: 900px) {
            .app { grid-template-columns: minmax(0, 1fr); }
            .sidebar { position: static; height: auto; gap: 12px; padding: 16px 20px; }
            .sidebar nav { display: flex; flex-wrap: wrap; }
            .sidebar-box, .sidebar-spacer { display: none; }
            main { padding: 24px 20px; }
        }

        @media (max-width: 560px) {
            .ringkasan { grid-template-columns: minmax(0, 1fr); }
            .kartu { padding: 18px; }
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
            <p class="jejak">BERANDA / MELIHAT TAGIHAN &amp; MENGUNGGAH BUKTI BAYAR</p>

            <div class="baris-judul">
                <h1>{{ $judul }}</h1>
                <span class="tag">Kost.io</span>
            </div>

            <p class="sub">
                {{ $tagihanBayar
                    ? 'Pilih metode pembayaran dan unggah bukti bayar.'
                    : 'Lihat nominal, jatuh tempo, dan status tagihan sewamu.' }}
            </p>

            @if (session('pesan'))
                <div class="pesan" role="status">{{ session('pesan') }}</div>
            @endif

            @if ($errors->any())
                <div class="pesan galat" role="alert">
                    <ul>
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($tagihanBayar)
                @php
                    $pilihanMetode = $metodePerTagihan->get($tagihanBayar->idTagihan, collect());
                    $bisaBayar = in_array($tagihanBayar->status, $statusBisaBayar, true);
                @endphp

                <div class="bayar-layout">
                    <section class="kartu">
                        <div class="bayar-ringkas">
                            <strong>{{ $tagihanBayar->namaKost }} · {{ $tagihanBayar->nomorKamar }}</strong>
                            <span>
                                {{ $rupiah($tagihanBayar->jumlah) }}
                                · Jatuh tempo {{ $formatTanggal($tagihanBayar->jatuhTempo) }}
                            </span>
                        </div>

                        @if (!$bisaBayar)
                            <p>Tagihan ini sedang diverifikasi atau sudah lunas.</p>
                        @elseif (!$tagihanBayar->idPenghuni)
                            <p>Tagihan belum terhubung ke data penghuni.</p>
                        @elseif ($pilihanMetode->isEmpty())
                            <p>Pemilik belum menyediakan metode pembayaran.</p>
                        @else
                            <form class="bayar-form"
                                  method="POST"
                                  action="{{ route('tagihan-sewa.bayar', ['idTagihan' => $tagihanBayar->idTagihan]) }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <label for="metode-bayar">Metode pembayaran / bank tujuan *</label>
                                <select id="metode-bayar" name="idMetode" required>
                                    <option value="">Pilih metode pembayaran</option>
                                    @foreach ($pilihanMetode as $metode)
                                        <option value="{{ $metode->idMetode }}"
                                                data-tujuan="{{ collect([$metode->namaBank, $metode->noRekening, $metode->atasNama])->filter()->implode(' · ') }}"
                                                @selected((string) old('idMetode') === (string) $metode->idMetode)>
                                            {{ $metode->jenis }}{{ $metode->namaBank ? ' · ' . $metode->namaBank : '' }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="tujuan" id="tujuan-bayar" aria-live="polite" hidden>
                                    <strong>Tujuan pembayaran</strong>
                                    <span id="rekening-tujuan"></span>
                                    <span>Nominal {{ $rupiah($tagihanBayar->jumlah) }}</span>
                                </div>

                                <label for="bukti-bayar">Bukti pembayaran *</label>
                                <div class="unggah">
                                    <input id="bukti-bayar" name="buktiBayar" type="file"
                                           accept="image/jpeg,image/png,application/pdf"
                                           aria-describedby="aturan-bukti" required>
                                    <small id="aturan-bukti">JPG, PNG, atau PDF · maksimal 2 MB</small>
                                </div>

                                <div class="aksi">
                                    <button type="submit" class="tombol utama">Kirim bukti</button>
                                    <a class="tombol" href="{{ route('tagihan-sewa.index') }}">Batal</a>
                                </div>
                            </form>
                        @endif

                        @if (!$bisaBayar || !$tagihanBayar->idPenghuni || $pilihanMetode->isEmpty())
                            <div class="aksi">
                                <a class="tombol" href="{{ route('tagihan-sewa.index') }}">Kembali ke daftar</a>
                            </div>
                        @endif
                    </section>

                    <aside class="kartu preview" aria-label="Ringkasan kost">
                        <h2>{{ $tagihanBayar->namaKost }}</h2>
                        <p>Kamar {{ $tagihanBayar->nomorKamar }}</p>
                        <small>Nominal tagihan</small>
                        <strong class="nilai">{{ $rupiah($tagihanBayar->jumlah) }}</strong>
                    </aside>
                </div>
            @else
                <div class="ringkasan">
                    <div class="kartu">
                        <small>Belum lunas</small>
                        <strong class="nilai">{{ $rupiah($jumlahBelumLunas) }}</strong>
                    </div>

                    <div class="kartu">
                        <small>Kamar pada tagihan terbaru</small>
                        <strong class="nilai">
                            {{ $tagihanTerbaru ? $tagihanTerbaru->namaKost . ' · ' . $tagihanTerbaru->nomorKamar : '—' }}
                        </strong>
                    </div>
                </div>

                <section class="daftar" aria-label="Daftar tagihan">
                    <div class="daftar-atas">
                        <strong>Daftar data</strong>
                        <input id="cari-tagihan" type="search"
                               placeholder="Cari data..." aria-label="Cari tagihan">
                    </div>

                    <div class="tabel">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Periode</th>
                                    <th scope="col">Nominal</th>
                                    <th scope="col">Jatuh tempo</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tagihan as $item)
                                    <tr data-tagihan>
                                        <td>{{ $formatPeriode($item->periode) }}</td>
                                        <td>{{ $rupiah($item->jumlah) }}</td>
                                        <td>{{ $formatTanggal($item->jatuhTempo) }}</td>
                                        <td>
                                            <span @class([
                                                'status',
                                                'lunas' => $item->status === 'Lunas',
                                                'menunggu' => $item->status === 'Menunggu Verifikasi',
                                            ])>{{ $item->status }}</span>
                                        </td>
                                        <td>
                                            @if (in_array($item->status, $statusBisaBayar, true))
                                                <a class="tautan"
                                                   href="{{ route('tagihan-sewa.index', ['bayar' => $item->idTagihan]) }}">
                                                    Bayar Tagihan
                                                </a>
                                            @else
                                                <details>
                                                    <summary>Lihat detail</summary>
                                                    <p>{{ $item->namaKost }} · {{ $item->nomorKamar }}</p>
                                                </details>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                <tr id="tagihan-kosong" @if ($tagihan->isNotEmpty()) hidden @endif>
                                    <td colspan="5">
                                        {{ $tagihan->isEmpty()
                                            ? 'Belum ada tagihan sewa untuk akun ini.'
                                            : 'Tagihan tidak ditemukan.' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <footer class="daftar-bawah" id="jumlah-tagihan" aria-live="polite">
                        Menampilkan {{ $tagihan->count() }} data
                    </footer>
                </section>

                @if ($peran === 'Calon Penghuni')
                    <div class="aksi">
                        <a class="tombol" href="{{ route('pengajuan.status') }}">
                            Kembali ke Status Pengajuan
                        </a>
                    </div>
                @endif
            @endif
        </main>
    </div>
</div>

<script>
    const pencarian = document.getElementById('cari-tagihan');

    if (pencarian) {
        const barisTagihan = document.querySelectorAll('[data-tagihan]');
        const barisKosong = document.getElementById('tagihan-kosong');
        const jumlahTagihan = document.getElementById('jumlah-tagihan');

        pencarian.addEventListener('input', () => {
            const kataKunci = pencarian.value.trim().toLowerCase();
            let jumlahTampil = 0;

            barisTagihan.forEach((baris) => {
                const cocok = baris.textContent.toLowerCase().includes(kataKunci);
                baris.hidden = !cocok;

                if (cocok) jumlahTampil++;
            });

            barisKosong.hidden = jumlahTampil > 0;
            jumlahTagihan.textContent = `Menampilkan ${jumlahTampil} data`;
        });
    }

    const metodeBayar = document.getElementById('metode-bayar');

    if (metodeBayar) {
        const tujuanBayar = document.getElementById('tujuan-bayar');
        const rekeningTujuan = document.getElementById('rekening-tujuan');

        function tampilkanTujuan() {
            const tujuan = metodeBayar.selectedOptions[0]?.dataset.tujuan ?? '';
            rekeningTujuan.textContent = tujuan;
            tujuanBayar.hidden = !tujuan;
        }

        metodeBayar.addEventListener('change', tampilkanTujuan);
        tampilkanTujuan();
    }
</script>
</body>
</html>