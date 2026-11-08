@extends('keluhan.pemilik.layout')

@section('title', 'Detail Keluhan')

@section('heading')
    @if (session('uc14_status_berhasil') && request()->boolean('berhasil'))
        Status keluhan diperbarui
    @else
        Detail keluhan KLH-{{ str_pad($laporan->idKeluhan, 3, '0', STR_PAD_LEFT) }}
    @endif
@endsection

@section('content')

@php
    $kode = 'KLH-' . str_pad(
        $laporan->idKeluhan,
        3,
        '0',
        STR_PAD_LEFT
    );

    $berhasil = session('uc14_status_berhasil')
        && request()->boolean('berhasil');

    $foto = $laporan->fotoKamar
        ?: ($laporan->fotoUtama ?: $laporan->fotoKost);

    $fotoUrl = $foto
        ? (
            preg_match('/^https?:\/\//i', $foto)
                ? $foto
                : asset(ltrim($foto, '/'))
        )
        : null;

    $statusTerpilih = old(
        'status',
        $laporan->status === 'Diajukan'
            ? 'Diproses'
            : $laporan->status
    );
@endphp

<div class="owner14-detail-grid">

    {{-- BAGIAN KIRI --}}
    <section class="owner14-detail-card">

        @if ($berhasil)

            {{-- UI-14.04: STATUS KELUHAN DIPERBARUI --}}

            <div class="owner14-success-icon">
                @include('keluhan.pemilik.icon', [
                    'name' => 'check',
                    'size' => 30
                ])
            </div>

            <div class="owner14-success-banner">
                @include('keluhan.pemilik.icon', [
                    'name' => 'bell',
                    'size' => 20
                ])

                <span>
                    Perubahan status berhasil disimpan.
                </span>
            </div>

            <div class="owner14-report-box">

                <strong>
                    {{ $kode }} · {{ $laporan->status }}
                </strong>

                <p>
                    @if ($laporan->status === 'Selesai')
                        Penanganan telah selesai.
                    @else
                        Penanganan sedang diproses.
                    @endif

                    Status terbaru dapat dilihat oleh penghuni.
                </p>

            </div>

            <div class="owner14-button-row">

                <a
                    href="{{ route('pemilik.keluhan.daftar') }}"
                    class="owner14-btn-secondary"
                >
                    Kembali ke daftar
                </a>

                <a
                    href="{{ route('pemilik.keluhan.pratinjau', $laporan->idKeluhan) }}"
                    class="owner14-btn-secondary"
                >
                    Lihat tampilan penghuni
                </a>

            </div>

        @else

            {{-- UI-14.02: DETAIL KELUHAN --}}

            @if (session('success'))
                <div class="owner14-success-banner">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="owner14-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="owner14-report-box">

                <strong>{{ $detail['judul'] }}</strong>

                <p>
                    {{ $laporan->namaLengkap }}
                    ·
                    {{ $laporan->nomorKamar ?? '-' }}
                    ·
                    {{ $detail['lokasi'] }}
                </p>

                <p>
                    {{ $detail['isi'] }}
                </p>

                @if ($laporan->foto)

                    <a
                        class="owner14-detail-link"
                        href="{{ route('pemilik.keluhan.foto', $laporan->idKeluhan) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Lihat foto bukti kerusakan
                    </a>

                @else

                    <p>Foto tidak dilampirkan.</p>

                @endif

            </div>

            {{-- JIKA SUDAH ADA PERTANYAAN PEMILIK --}}
            @if ($info && filled($info['pertanyaan'] ?? null))

                <div class="owner14-existing-info">

                    <strong>Pertanyaan yang dikirim</strong>
                    <p>{{ $info['pertanyaan'] }}</p>

                    <strong>Jawaban penghuni</strong>
                    <p>
                        {{ $info['jawaban']
                            ?? 'Menunggu jawaban penghuni.'
                        }}
                    </p>

                </div>

            @endif

            {{-- FORM PERTANYAAN --}}
            <form
                id="uc14FormInfo"
                method="POST"
                action="{{ route('pemilik.keluhan.minta-info', $laporan->idKeluhan) }}"
            >
                @csrf
            </form>

            {{-- FORM STATUS --}}
            <form
                id="uc14FormStatus"
                method="POST"
                action="{{ route('pemilik.keluhan.status', $laporan->idKeluhan) }}"
            >
                @csrf
                @method('PATCH')
            </form>

            <div class="owner14-detail-fields">

                <div class="owner14-field">

                    <label for="pertanyaanTambahan">
                        Pertanyaan tambahan (opsional)
                    </label>

                    <textarea
                        id="pertanyaanTambahan"
                        name="pertanyaanTambahan"
                        form="uc14FormInfo"
                        rows="3"
                        maxlength="500"
                        placeholder="Kebocoran ada di keran atau pipa?"
                    >{{ old('pertanyaanTambahan', $info['pertanyaan'] ?? '') }}</textarea>

                </div>

                <div class="owner14-field">

                    <label for="status">
                        Status penanganan
                    </label>

                    <select
                        id="status"
                        name="status"
                        form="uc14FormStatus"
                        required
                    >
                        <option
                            value="Diproses"
                            @selected($statusTerpilih === 'Diproses')
                        >
                            Diproses
                        </option>

                        <option
                            value="Selesai"
                            @selected($statusTerpilih === 'Selesai')
                        >
                            Selesai
                        </option>
                    </select>

                </div>

            </div>

            <div class="owner14-button-row">

                <button
                    type="submit"
                    form="uc14FormInfo"
                    class="owner14-btn-primary"
                >
                    Minta informasi
                </button>

                <button
                    type="submit"
                    form="uc14FormStatus"
                    class="owner14-btn-secondary"
                >
                    Simpan status
                </button>

            </div>

        @endif

    </section>

    {{-- BAGIAN KANAN: INFORMASI KOST --}}
    <aside class="owner14-residence-card">

        <div class="owner14-residence-photo">

            @if ($fotoUrl)

                <img
                    src="{{ $fotoUrl }}"
                    alt="Foto {{ $laporan->namaKost }}"
                    loading="lazy"
                >

            @else

                <div class="owner14-photo-empty">
                    Foto hunian belum tersedia
                </div>

            @endif

        </div>

        <h2>{{ $laporan->namaKost }}</h2>

        <p>
            {{ $laporan->lokasiKost
                ?: ($laporan->alamatKost
                    ?: 'Lokasi belum tersedia')
            }}
        </p>

        <span class="owner14-kost-pill">
            Hunian nyaman
        </span>

    </aside>

</div>

@endsection
