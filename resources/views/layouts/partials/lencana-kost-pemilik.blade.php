@php
    $namaLencana = trim((string) ($namaLencana ?? ''));
    if (in_array($namaLencana, ['', 'Kost.io'], true)) {
        $namaLencana = \Illuminate\Support\Facades\DB::table('kost')
            ->where('idPemilik', session('idAkun'))
            ->orderBy('idKost')
            ->value('namaKost') ?: 'Kost.io';
    }
@endphp
<span class="kio-owner-kost-badge" @isset($idLencana) id="{{ $idLencana }}" @endisset
      title="{{ $namaLencana }}">{{ $namaLencana }}</span>
