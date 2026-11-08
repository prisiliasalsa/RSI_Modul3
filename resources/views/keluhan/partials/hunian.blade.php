@php
    $namaKost = $penghuni->namaKost ?? 'Belum ada hunian';

    $lokasiKost = $penghuni->lokasi
        ?? $penghuni->alamat
        ?? 'Informasi kost belum tersedia';

    $fotoKost = $penghuni->fotoKamar
        ?? $penghuni->fotoUtama
        ?? $penghuni->fotoKost
        ?? null;

    $fotoUrl = $fotoKost
        && !preg_match('/^https?:\/\//i', $fotoKost)
        ? asset(ltrim($fotoKost, '/'))
        : null;
@endphp

<aside class="hunian-card">
    <div class="hunian-photo">
        @if ($fotoUrl)
            <img
                src="{{ $fotoUrl }}"
                alt="Foto hunian {{ $namaKost }}"
                loading="lazy"
            >
        @else
            <div class="photo-placeholder">
                @include('keluhan.partials.icon', [
                    'name' => 'home',
                    'size' => 42
                ])
                <span>Foto hunian belum tersedia</span>
            </div>
        @endif
    </div>

    <h2>{{ $namaKost }}</h2>
    <p>{{ $lokasiKost }}</p>

    <span class="tag tag-blue">
        Hunian nyaman
    </span>
</aside>