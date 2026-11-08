@extends('keluhan.layout')

@section(
    'title',
    $berhasil
        ? 'Laporan berhasil dikirim'
        : ($errors->any()
            ? 'Lengkapi data keluhan'
            : 'Ajukan Keluhan')
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

    {{-- UI-08.03: Laporan berhasil dikirim --}}

    <div class="success-icon">
        @include('keluhan.partials.icon', [
            'name' => 'check',
            'size' => 32
        ])
    </div>

    <div class="alert alert-info" role="status">
        @include('keluhan.partials.icon', [
            'name' => 'bell'
        ])

        <span>
            Laporan berhasil dikirim kepada pemilik kost.
        </span>
    </div>

    <div class="residence-highlight result-box">
        <strong>
            KLH-{{ str_pad($berhasil['idKeluhan'], 3, '0', STR_PAD_LEFT) }}
            · {{ $berhasil['judul'] }}
        </strong>

        <p>Status: {{ $berhasil['status'] }}</p>
        <p>Lokasi: {{ $berhasil['lokasi'] }}</p>

        <p>
            Foto:
            {{ $berhasil['foto']
                ? 'Dilampirkan'
                : 'Tidak dilampirkan' }}.
            Pemilik akan memperbarui status penanganan.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('keluhan.detail', $berhasil['idKeluhan']) }}"
           class="btn btn-primary">
            Lihat status keluhan
        </a>

        <a href="{{ route('keluhan.ajukan') }}"
           class="btn btn-secondary">
            Ajukan laporan lain
        </a>
    </div>

@else

    {{-- UI-08.01: Form pengajuan keluhan --}}

    @if (!$penghuni)
        <div class="alert alert-warning" role="status">
            @include('keluhan.partials.icon', [
                'name' => 'info'
            ])

            <span>
                Silakan masuk sebagai penghuni dengan sewa aktif.
                Formulir akan aktif setelah login penghuni
                terhubung dengan sistem.
            </span>
        </div>
    @else
        <div class="residence-highlight">
            <strong>
                {{ $penghuni->namaKost ?? 'Kost' }}
                · Kamar {{ $penghuni->nomorKamar ?? '—' }}
            </strong>

            <p>Laporan akan dikirim kepada pemilik kost.</p>
        </div>
    @endif

    {{-- UI-08.02: Data tidak lengkap / foto tidak valid --}}

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @include('keluhan.partials.icon', [
                'name' => 'alert'
            ])

            <div>
                <strong>
                    Data keluhan belum lengkap atau tidak sesuai.
                </strong>

                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ route('keluhan.simpan') }}"
          enctype="multipart/form-data"
          novalidate>

        @csrf

        <fieldset @disabled(!$penghuni)>

            <div class="form-grid">

                <!-- Judul keluhan -->
                <div class="field">
                    <label for="judul">
                        Judul / jenis keluhan <span>*</span>
                    </label>

                    <input
                        id="judul"
                        name="judul"
                        type="text"
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

                <!-- Lokasi kerusakan -->
                <div class="field">
                    <label for="lokasi">
                        Lokasi kerusakan <span>*</span>
                    </label>

                    <input
                        id="lokasi"
                        name="lokasi"
                        type="text"
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

            <!-- Deskripsi keluhan -->
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

            <!-- Upload foto bukti -->
            <div class="field">
                <label for="foto">
                    Foto bukti (opsional)
                </label>

                <label
                    class="upload-area {{ $errors->has('foto') ? 'has-error' : '' }}"
                    for="foto"
                >
                    @include('keluhan.partials.icon', [
                        'name' => 'upload'
                    ])

                    <strong id="upload-caption">
                        Pilih foto kerusakan (opsional)
                    </strong>

                    <small>
                        JPG atau PNG · maksimal 2 MB
                    </small>
                </label>

                <input
                    class="visually-hidden"
                    id="foto"
                    name="foto"
                    type="file"
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

            <!-- Tombol submit -->
            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    {{ $errors->any()
                        ? 'Lengkapi laporan'
                        : 'Kirim laporan' }}
                </button>
            </div>

        </fieldset>
    </form>

@endif

@endsection