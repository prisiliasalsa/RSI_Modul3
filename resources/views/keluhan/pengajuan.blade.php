
@extends('keluhan.layout')

@section(
    'title',
    $berhasil
        ? 'Laporan berhasil dikirim'
        : ($errors->any()
            ? 'Lengkapi data keluhan'
            : 'Ajukan keluhan / kerusakan')
)

@section(
    'heading',
    $berhasil
        ? 'Laporan berhasil dikirim'
        : ($errors->any()
            ? 'Lengkapi data keluhan'
            : 'Ajukan keluhan / kerusakan')
)

@section('content')

@if ($berhasil)

    {{-- UI-08.03: LAPORAN BERHASIL DIKIRIM --}}

    <div class="success-icon">
        @include('keluhan.partials.icon', [
            'name' => 'check',
            'size' => 32
        ])
    </div>

    <div class="alert alert-info" role="status">

        @include('keluhan.partials.icon', [
            'name' => 'bell',
            'size' => 20
        ])

        <span>
            Laporan berhasil dikirim kepada pemilik kost.
        </span>

    </div>

    <div class="residence-highlight result-box">

        <strong>
            KLH-{{ str_pad(
                $berhasil['idKeluhan'],
                3,
                '0',
                STR_PAD_LEFT
            ) }}
            ·
            {{ $berhasil['judul'] }}
        </strong>

        <p>
            Status: {{ $berhasil['status'] }}
        </p>

        <p>
            Foto:
            {{ $berhasil['foto']
                ? 'dilampirkan'
                : 'tidak dilampirkan'
            }}.
            Pemilik akan memperbarui status penanganan.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('keluhan.detail', $berhasil['idKeluhan']) }}"
            class="btn btn-primary"
        >
            Lihat status keluhan
        </a>

        <a
            href="{{ route('keluhan.ajukan') }}"
            class="btn btn-secondary"
        >
            Ajukan laporan lain
        </a>

    </div>

@else

    {{-- UI-08.01 / UI-08.02: FORM PENGAJUAN --}}

    @if ($errors->any())

        <div class="alert alert-info" role="alert">

            @include('keluhan.partials.icon', [
                'name' => 'bell',
                'size' => 20
            ])

            <span>
                Data keluhan belum lengkap.
            </span>

        </div>

    @elseif ($penghuni)

        <div class="residence-highlight">

            <strong>
                {{ $penghuni->namaKost ?? 'Kost' }}
                ·
                Kamar {{ $penghuni->nomorKamar ?? '-' }}
            </strong>

            <p>
                Laporan akan dikirim kepada pemilik kost.
            </p>

        </div>

    @else

        <div class="alert alert-warning">

            @include('keluhan.partials.icon', [
                'name' => 'info',
                'size' => 20
            ])

            <span>
                Silakan login sebagai penghuni dengan sewa aktif
                untuk mengirim laporan.
            </span>

        </div>

    @endif

    <form
        method="POST"
        action="{{ route('keluhan.simpan') }}"
        enctype="multipart/form-data"
        novalidate
        class="keluhan-form"
    >

        @csrf

        <fieldset @disabled(!$penghuni)>

            {{-- BARIS JUDUL DAN LOKASI --}}
            <div class="form-grid">

                <div class="field">

                    <label for="judul">
                        Judul / jenis keluhan <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        maxlength="120"
                        value="{{ old('judul') }}"
                        placeholder="Contoh: Keran kamar mandi bocor"
                        aria-invalid="{{ $errors->has('judul') ? 'true' : 'false' }}"
                        required
                    >

                    @error('judul')
                        <small class="field-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="field">

                    <label for="lokasi">
                        Lokasi kerusakan <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="lokasi"
                        name="lokasi"
                        maxlength="120"
                        value="{{ old('lokasi') }}"
                        placeholder="Contoh: Kamar mandi lantai 1"
                        aria-invalid="{{ $errors->has('lokasi') ? 'true' : 'false' }}"
                        required
                    >

                    @error('lokasi')
                        <small class="field-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

            </div>

            {{-- DESKRIPSI --}}
            <div class="field">

                <label for="deskripsi">
                    Deskripsi keluhan <span>*</span>
                </label>

                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    maxlength="1500"
                    rows="3"
                    placeholder="Tuliskan informasi kerusakan dengan jelas."
                    aria-invalid="{{ $errors->has('deskripsi') ? 'true' : 'false' }}"
                    required
                >{{ old('deskripsi') }}</textarea>

                @error('deskripsi')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- FOTO BUKTI --}}
            <div class="field">

                <label for="foto">
                    Foto bukti (opsional)
                </label>

                <label
                    for="foto"
                    class="upload-area {{ $errors->has('foto') ? 'has-error' : '' }}"
                >

                    @include('keluhan.partials.icon', [
                        'name' => 'upload',
                        'size' => 20
                    ])

                    <strong id="upload-caption">
                        Pilih foto kerusakan (opsional)
                    </strong>

                    <small>
                        JPG atau PNG · maksimal 2 MB
                    </small>

                </label>

                <input
                    id="foto"
                    name="foto"
                    type="file"
                    class="visually-hidden"
                    accept="image/jpeg,image/png"
                    aria-describedby="upload-caption"
                    data-file-input
                >

                @error('foto')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            {{-- TOMBOL SUBMIT --}}
            <div class="actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    {{ $errors->any()
                        ? 'Lengkapi laporan'
                        : 'Kirim laporan'
                    }}
                </button>

            </div>

        </fieldset>

    </form>

@endif

@endsection
