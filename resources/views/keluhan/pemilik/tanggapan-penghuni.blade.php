
@extends('keluhan.pemilik.layout-penghuni')

@section('title', 'Status Keluhan Saya')
@section('heading', 'Status keluhan saya')

@section('content')

@php
    $fotoKost = $penghuni->fotoKamar
        ?: ($penghuni->fotoUtama ?: $penghuni->fotoKost);

    $fotoUrl = $fotoKost
        ? (
            preg_match('/^https?:\/\//i', $fotoKost)
                ? $fotoKost
                : asset(ltrim($fotoKost, '/'))
        )
        : null;
@endphp

<div class="owner14-detail-grid">

    <section class="owner14-detail-card uc14-resident-card">

        <div class="uc14-resident-summary">

            <strong>
                KLH-{{ str_pad($laporan->idKeluhan, 3, '0', STR_PAD_LEFT) }}
                ·
                {{ $detail['judul'] }}
            </strong>

            <p>
                Status: {{ $laporan->status }}
            </p>

            <p>
                @if ($laporan->status === 'Selesai')
                    Penanganan sudah selesai.
                @elseif ($laporan->status === 'Diproses')
                    Pemilik sedang mengatur penanganan.
                @else
                    Laporan menunggu ditinjau pemilik kost.
                @endif
            </p>

        </div>

        @if ($info && filled($info['pertanyaan'] ?? null))

            <div class="uc14-resident-summary">

                <strong>
                    Permintaan informasi dari pemilik
                </strong>

                <p>{{ $info['pertanyaan'] }}</p>

            </div>

            @if (filled($info['jawaban'] ?? null))

                <div class="uc14-response-block">

                    <strong>
                        Informasi tambahan
                    </strong>

                    <p>{{ $info['jawaban'] }}</p>

                </div>

            @elseif (!$pratinjau)

                <form
                    method="POST"
                    action="{{ route('keluhan.tanggapan-kirim', $laporan->idKeluhan) }}"
                    class="uc14-response-form"
                >
                    @csrf

                    <label for="jawaban">
                        Informasi tambahan
                    </label>

                    <textarea
                        id="jawaban"
                        name="jawaban"
                        rows="4"
                        maxlength="1000"
                        required
                        placeholder="Tuliskan informasi tambahan untuk pemilik..."
                    >{{ old('jawaban') }}</textarea>

                    @error('jawaban')
                        <small class="owner14-field-error">
                            {{ $message }}
                        </small>
                    @enderror

                    <button
                        type="submit"
                        class="owner14-primary"
                    >
                        Kirim informasi
                    </button>

                </form>

            @else

                <p class="uc14-response-note">
                    Penghuni belum memberikan informasi tambahan.
                </p>

            @endif

        @else

            <div class="uc14-response-note">
                Belum ada permintaan informasi tambahan dari pemilik kost.
            </div>

        @endif

        @if (session('success'))

            <div class="uc14-response-note">
                {{ session('success') }}
            </div>

        @endif

        @if ($pratinjau)

            <p class="uc14-response-note">
                Pratinjau penghuni dari akun pemilik (hanya baca).
            </p>

            <a
                class="owner14-secondary"
                href="{{ route('pemilik.keluhan.detail', $laporan->idKeluhan) }}"
            >
                Kembali ke detail pemilik
            </a>

        @endif

    </section>

    <aside class="owner14-residence-card">

        <div class="owner14-residence-photo">

            @if ($fotoUrl)

                <img
                    src="{{ $fotoUrl }}"
                    alt="Foto kost {{ $penghuni->namaKost }}"
                    loading="lazy"
                >

            @else

                <div class="owner14-photo-placeholder">
                    Foto hunian belum tersedia
                </div>

            @endif

        </div>

        <h2>
            {{ $penghuni->namaKost ?: 'Hunian penghuni' }}
        </h2>

        <p>
            {{ $penghuni->lokasi
                ?: ($penghuni->alamat
                    ?: 'Lokasi belum tersedia')
            }}
        </p>

        <span class="owner14-kost-pill">
            Hunian nyaman
        </span>

    </aside>

</div>

@endsection
