@extends('layouts.ruang-pemilik')

@section('judul', 'Verifikasi Pengajuan Sewa')
@section('breadcrumb', 'BERANDA / VERIFIKASI PENGAJUAN SEWA')
@section('heading', 'Verifikasi pengajuan sewa')
@section('subjudul', 'Tinjau pengajuan yang masuk untuk kost milikmu.')

@section('content')
    <style>
        .verifikasi-sewa .daftar {
            display: grid;
            gap: 16px;
        }

        .verifikasi-sewa .kartu {
            padding: 24px;
            border: 1px solid #ddd5ca;
            border-radius: 16px;
            background: #fff;
        }

        .verifikasi-sewa .kepala,
        .verifikasi-sewa .aksi {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .verifikasi-sewa .kepala {
            justify-content: space-between;
        }

        .verifikasi-sewa h2 {
            margin: 0;
            color: #022e4a;
            font-size: 20px;
        }

        .verifikasi-sewa .status {
            padding: 6px 12px;
            border-radius: 999px;
            background: #e2d8cb;
            color: #022e4a;
            font-size: 13px;
        }

        .verifikasi-sewa .status.disetujui { background: #e0efe5; color: #22633d; }
        .verifikasi-sewa .status.ditolak { background: #f6e3e8; color: #56061d; }

        .verifikasi-sewa .ringkasan {
            display: grid;
            gap: 6px;
            margin: 16px 0;
            padding: 16px;
            border-radius: 10px;
            background: #f5f0ea;
            color: #5b6871;
        }

        .verifikasi-sewa label {
            display: block;
            margin-bottom: 6px;
            color: #022e4a;
            font-weight: 500;
        }

        .verifikasi-sewa textarea {
            display: block;
            width: 100%;
            max-width: 540px;
            padding: 10px 12px;
            border: 1px solid #ddd5ca;
            border-radius: 8px;
            font: inherit;
            resize: vertical;
        }

        .verifikasi-sewa .aksi { margin-top: 12px; }

        .verifikasi-sewa .tombol {
            display: inline-block;
            padding: 10px 16px;
            border: 1px solid #56061d;
            border-radius: 8px;
            background: white;
            color: #56061d;
            font: inherit;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
        }

        .verifikasi-sewa .tombol-utama {
            background: #56061d;
            color: white;
        }

        .verifikasi-sewa .galat {
            margin-bottom: 16px;
            padding: 12px 16px;
            border-radius: 8px;
            background: #f6e3e8;
            color: #56061d;
        }
    </style>

    <div class="verifikasi-sewa">
        @if (session('error') || $errors->any())
            <div class="galat" role="alert">
                @if (session('error'))
                    <p>{{ session('error') }}</p>
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

        <div class="daftar">
            @forelse ($pengajuan as $item)
                <article class="kartu">
                    <div class="kepala">
                        <h2>{{ $item->namaKost }} · {{ $item->nomorKamar }}</h2>

                        <span @class([
                            'status',
                            'disetujui' => $item->status === 'Disetujui',
                            'ditolak' => $item->status === 'Ditolak',
                        ])>{{ $item->status }}</span>
                    </div>

                    <div class="ringkasan">
                        <span>Pemohon: <strong>{{ $item->namaLengkap }}</strong></span>
                        <span>
                            Mulai {{ \Illuminate\Support\Carbon::parse($item->tanggalMulaiSewa)->locale('id')->isoFormat('D MMMM YYYY') }}
                            · {{ $item->durasiSewa }} bulan
                            · {{ $item->jumlahPenghuni }} penghuni
                        </span>

                        @if ($item->catatan)
                            <span>Catatan: {{ $item->catatan }}</span>
                        @endif

                        @if ($item->alasanPenolakan)
                            <span>Alasan penolakan: {{ $item->alasanPenolakan }}</span>
                        @endif
                    </div>

                    @if (in_array($item->status, ['Menunggu', 'Menunggu Verifikasi'], true))
                        <form method="POST"
                              action="{{ route('pemilik.pengajuan-sewa.putuskan', $item->idPengajuan) }}">
                            @csrf

                            <label for="alasan-{{ $item->idPengajuan }}">Alasan jika ditolak</label>
                            <textarea id="alasan-{{ $item->idPengajuan }}"
                                      name="alasanPenolakan"
                                      maxlength="1000"
                                      rows="2"></textarea>

                            <div class="aksi">
                                <button type="submit" name="keputusan" value="Disetujui"
                                        class="tombol tombol-utama">Setujui</button>
                                <button type="submit" name="keputusan" value="Ditolak"
                                        class="tombol">Tolak</button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <div class="kartu">Belum ada pengajuan sewa untuk kost milikmu.</div>
            @endforelse
        </div>

        <div class="aksi">
            {{ $pengajuan->links() }}
            <a class="tombol" href="{{ route('kost.daftar') }}">Kembali ke Kelola Kost</a>
        </div>
    </div>
@endsection