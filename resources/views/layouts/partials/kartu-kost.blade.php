{{--
    Kartu informasi kost di sisi kanan halaman detail.
    Parameter: $foto (path/URL, boleh null), $namaKost, $lokasi,
    $nomorKamar, $hargaSewa (teks Rupiah, boleh null).
--}}
@php
    $fotoUrl = $foto
        ? (preg_match('/^https?:\/\//i', $foto) ? $foto : asset(ltrim($foto, '/')))
        : null;
    $judulKost = preg_match('/^Kost\s/i', (string) $namaKost)
        ? $namaKost
        : 'Kost ' . $namaKost;
@endphp

<aside class="rp-card rp-room-card">

    @if ($fotoUrl)
        <img
            class="rp-room-photo"
            src="{{ $fotoUrl }}"
            alt="Foto kamar {{ $nomorKamar }} {{ $judulKost }}"
        >
    @else
        <div class="rp-room-photo rp-room-photo-empty" aria-hidden="true">
            @include('keluhan.pemilik.icon', ['name' => 'home', 'size' => 40])
        </div>
    @endif

    <h2>{{ $judulKost }}</h2>
    <p class="rp-muted">{{ $lokasi ?: '-' }}</p>
    <span class="rp-pill">Hunian nyaman</span>
    <p class="rp-muted rp-room-info">
        Informasi tersimpan pada akun dan dapat dilihat sesuai hak akses.
    </p>

    @if (!empty($hargaSewa))
        <small class="rp-muted">Harga sewa / bulan</small>
        <p class="rp-price">{{ $hargaSewa }}</p>
    @endif

</aside>
