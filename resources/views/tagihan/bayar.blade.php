@extends('tagihan.layout')
@section('title', 'Bayar tagihan ' . $tagihan->namaBulan)
@section('heading', 'Bayar tagihan ' . $tagihan->namaBulan
    . ($qris ? ' — QRIS' : ''))
@section('content')
{{-- UI-07.02: Transfer Bank | UI-07.03: QRIS --}}
<div class="pay-grid">
    <form class="card pay-form" method="POST" enctype="multipart/form-data"
          action="{{ route('tagihan.kirim', $tagihan->idTagihan) }}"
          novalidate>
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @include('tagihan.partials.icon', ['name' => 'info'])
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if ($qris)
            <div class="info-box">
                <strong>Metode QRIS dipilih</strong>
                <p>
                    Nominal {{ $tagihan->jumlahRupiah }}. Pindai kode milik
                    pemilik kost melalui aplikasi pembayaran di luar
                    Kost.io.
                </p>
                <a class="link-aksi"
                   href="{{ route('tagihan.bayar', $tagihan->idTagihan) }}">
                    Ganti metode pembayaran
                </a>
            </div>
        @else
            <div class="info-box">
                <strong>
                    {{ $penghuni->namaKostLengkap }}
                    · {{ $penghuni->nomorKamar }}
                </strong>
                <p>
                    {{ $tagihan->jumlahRupiah }} · Jatuh tempo
                    {{ $tagihan->jatuhTempoPanjang }}
                </p>
            </div>
        @endif

        @if (!$bisaBayar)
            <div class="alert alert-info" role="status">
                @include('tagihan.partials.icon', ['name' => 'info'])
                <span>
                    Tagihan ini berstatus {{ $tagihan->status }}.
                    Pembayaran tidak dapat dikirim ulang.
                </span>
            </div>
            <a class="btn btn-secondary"
               href="{{ route('tagihan.daftar') }}">Kembali ke Tagihan</a>
        @else
            @if ($qris)
                <input type="hidden" name="metode" value="QRIS">
                <div class="qris-box">
                    @if ($qrisData && $qrisData->kodeQRIS)
                        <img alt="Kode QRIS {{ $penghuni->namaKostLengkap }}"
                             src="{{ asset($qrisData->kodeQRIS) }}">
                    @else
                        <p class="empty">
                            Kode QRIS belum disediakan pemilik kost.
                        </p>
                    @endif
                    <small>QRIS {{ $penghuni->namaKostLengkap }}</small>
                </div>
            @else
                <div class="field-row">
                    <div class="field @error('metode') has-error @enderror">
                        <label for="metode">
                            Metode pembayaran <span class="req">*</span>
                        </label>
                        <select id="metode" name="metode" data-pilih-metode>
                            <option value="Transfer Bank" selected>
                                Transfer Bank
                            </option>
                            <option value="QRIS">QRIS</option>
                        </select>
                        @error('metode')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field @error('idBank') has-error @enderror">
                        <label for="idBank">
                            Bank tujuan <span class="req">*</span>
                        </label>
                        <select id="idBank" name="idBank" data-pilih-bank>
                            @foreach ($daftarBank as $item)
                                <option value="{{ $item->idMetode }}"
                                    @selected($bank &&
                                        $bank->idMetode == $item->idMetode)>
                                    {{ $item->namaBank }}
                                </option>
                            @endforeach
                        </select>
                        @error('idBank')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($bank)
                    <div class="info-box">
                        <strong>Transfer Bank — tujuan pembayaran</strong>
                        <p>
                            {{ $bank->namaBank }} · {{ $bank->noRekening }}
                            · a.n. {{ $bank->atasNama }}<br>
                            Nominal {{ $tagihan->jumlahRupiah }}. Lakukan
                            pembayaran melalui aplikasi bank di luar
                            Kost.io.
                        </p>
                    </div>
                @else
                    <div class="alert alert-warning" role="status">
                        @include('tagihan.partials.icon',
                            ['name' => 'info'])
                        <span>
                            Pemilik kost belum menyediakan rekening tujuan.
                        </span>
                    </div>
                @endif
            @endif

            <div class="field-row {{ $qris ? 'field-row-stack' : '' }}">
                <div class="field @error('bukti') has-error @enderror">
                    <label for="bukti">
                        Bukti pembayaran <span class="req">*</span>
                    </label>
                    <label class="dropzone" for="bukti" data-dropzone>
                        <input type="file" id="bukti" name="bukti"
                               class="visually-hidden" data-file-input
                               accept=".jpg,.jpeg,.png,.pdf">
                        @include('tagihan.partials.icon',
                            ['name' => 'upload'])
                        <span class="dropzone-title" id="upload-caption">
                            Pilih berkas atau tarik ke sini
                        </span>
                        <span class="dropzone-hint">
                            JPG, PNG, atau PDF · maksimal 2 MB
                        </span>
                    </label>
                    @error('bukti')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="catatan">Catatan</label>
                    <textarea id="catatan" name="catatan" rows="3">{{
                        old('catatan', 'Pembayaran ' . $tagihan->namaBulan)
                    }}</textarea>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Kirim</button>
                <a class="btn btn-secondary"
                   href="{{ route('tagihan.daftar') }}">Batal</a>
            </div>
        @endif
    </form>

    <aside class="card room-card">
        @if ($penghuni->fotoKamar)
            <img class="room-photo"
                 src="{{ asset($penghuni->fotoKamar) }}"
                 alt="Foto kamar {{ $penghuni->nomorKamar }}">
        @else
            <div class="room-photo room-photo-empty"></div>
        @endif
        <h2>{{ $penghuni->namaKostLengkap }}</h2>
        <p class="muted">{{ $penghuni->lokasi }}</p>
        <span class="pill">Hunian nyaman</span>
        <p class="muted">
            Informasi tersimpan pada akun dan dapat dilihat sesuai hak
            akses.
        </p>
        <small class="muted">Harga sewa / bulan</small>
        <p class="price">{{ $penghuni->hargaSewaRupiah }}</p>
    </aside>
</div>
@endsection
