{{--
    HalamanDataPenghuni (UI-12):
    - tampilDetailPenghuni() + tampilFormUbah() : tanpa aksi
    - tampilKonfirmasi()                        : ?aksi=akhiri
--}}
@extends('layouts.ruang-pemilik', ['menuAktif' => 'penghuni'])

@php
    $judul = $aksi === 'akhiri' ? 'Konfirmasi akhiri sewa' : 'Detail / ubah penghuni';
    $urlDetail = route('pemilik.penghuni.detail', $penghuni->idPenghuni);
@endphp

@section('judul', $judul)
@section('breadcrumb', 'BERANDA / MENGELOLA DATA PENGHUNI')
@section('heading', $judul)
@section('subjudul', 'Data penghuni dan masa sewa tersusun dalam satu tempat.')

@section('content')

<div class="rp-detail-grid">

    <section class="rp-card rp-detail-main">

        @if ($aksi === 'akhiri')

            {{-- Pop-up konfirmasi "Lanjutkan tindakan?" (Alternative Flow 2) --}}
            <div class="rp-info-box">
                <strong>Akhiri sewa {{ $penghuni->namaLengkap }}?</strong>
                <p>
                    Lanjutkan tindakan? Status penghuni menjadi Tidak Aktif dan
                    kamar {{ $penghuni->nomorKamar }} kembali Tersedia.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('pemilik.penghuni.akhiri', $penghuni->idPenghuni) }}"
                class="rp-actions"
            >
                @csrf
                <button type="submit" class="rp-btn rp-btn-primary">Ya, Akhiri Sewa</button>
                <a href="{{ $urlDetail }}" class="rp-btn rp-btn-outline">Batal</a>
            </form>

        @else

            {{-- Formulir ubah data penghuni yang sudah terisi --}}
            <form
                method="POST"
                action="{{ route('pemilik.penghuni.ubah', $penghuni->idPenghuni) }}"
                novalidate
            >
                @csrf
                @method('PUT')

                <fieldset class="rp-form-grid" @disabled(!$aktif)>
                    <legend class="rp-visually-hidden">Data penghuni</legend>

                    <div class="rp-field">
                        <label for="namaLengkap" class="rp-label">Nama lengkap <span aria-hidden="true">*</span></label>
                        <input
                            id="namaLengkap"
                            name="namaLengkap"
                            type="text"
                            maxlength="100"
                            class="rp-input @error('namaLengkap') rp-input-error @enderror"
                            value="{{ old('namaLengkap', $penghuni->namaLengkap) }}"
                            required
                        >
                        @error('namaLengkap') <p class="rp-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="rp-field">
                        <label for="noTelepon" class="rp-label">Nomor telepon <span aria-hidden="true">*</span></label>
                        <input
                            id="noTelepon"
                            name="noTelepon"
                            type="tel"
                            maxlength="20"
                            inputmode="tel"
                            class="rp-input @error('noTelepon') rp-input-error @enderror"
                            value="{{ old('noTelepon', $penghuni->noTelepon) }}"
                            required
                        >
                        @error('noTelepon') <p class="rp-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="rp-field">
                        <label for="kamar" class="rp-label">Kost / kamar</label>
                        <input
                            id="kamar"
                            type="text"
                            class="rp-input"
                            value="{{ $penghuni->namaKostPendek }} - {{ $penghuni->nomorKamar }}"
                            readonly
                        >
                    </div>

                    <div class="rp-field">
                        <label for="tanggalMulaiSewa" class="rp-label">Mulai sewa <span aria-hidden="true">*</span></label>
                        <input
                            id="tanggalMulaiSewa"
                            name="tanggalMulaiSewa"
                            type="date"
                            class="rp-input @error('tanggalMulaiSewa') rp-input-error @enderror"
                            value="{{ old('tanggalMulaiSewa', $penghuni->mulaiInput) }}"
                            required
                        >
                        @error('tanggalMulaiSewa') <p class="rp-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="rp-field">
                        <label for="tanggalAkhirSewa" class="rp-label">Akhir sewa <span aria-hidden="true">*</span></label>
                        <input
                            id="tanggalAkhirSewa"
                            name="tanggalAkhirSewa"
                            type="date"
                            class="rp-input @error('tanggalAkhirSewa') rp-input-error @enderror"
                            value="{{ old('tanggalAkhirSewa', $penghuni->akhirInput) }}"
                            required
                        >
                        @error('tanggalAkhirSewa') <p class="rp-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="rp-field">
                        <label for="statusPenghuni" class="rp-label">Status</label>
                        {{-- Status diubah lewat tombol "Akhiri Sewa" --}}
                        <select id="statusPenghuni" class="rp-input rp-select" disabled>
                            <option>{{ $penghuni->status }}</option>
                        </select>
                    </div>
                </fieldset>

                @unless ($aktif)
                    <p class="rp-help">Sewa penghuni ini sudah diakhiri, data hanya dapat dilihat.</p>
                @endunless

                <div class="rp-actions">
                    @if ($aktif)
                        <button type="submit" class="rp-btn rp-btn-primary">Simpan perubahan</button>
                        <a href="{{ $urlDetail }}?aksi=akhiri" class="rp-btn rp-btn-outline">Akhiri Sewa</a>
                    @endif
                    <a href="{{ route('pemilik.penghuni.daftar') }}" class="rp-btn rp-btn-outline">
                        {{ $aktif ? 'Batal' : 'Kembali' }}
                    </a>
                </div>
            </form>

        @endif

    </section>

    @include('layouts.partials.kartu-kost', [
        'foto' => $penghuni->fotoKamar ?: $penghuni->fotoUtama,
        'namaKost' => $penghuni->namaKost,
        'lokasi' => $penghuni->lokasi,
        'nomorKamar' => $penghuni->nomorKamar,
        'hargaSewa' => null,
    ])

</div>

@endsection
