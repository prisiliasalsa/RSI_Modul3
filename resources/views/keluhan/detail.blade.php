@extends('keluhan.layout')

@section('title', 'Detail Keluhan')
@section('heading', 'Detail keluhan')
@section('subtitle', 'Pantau status laporan dan tanggapi pertanyaan dari pemilik kost.')

@section('content')

<a class="text-link" href="{{ route('keluhan.status') }}">
    Kembali ke keluhan saya
</a>

<div class="residence-highlight detail-title">
    <strong>
        KLH-{{ str_pad($laporan->idKeluhan, 3, '0', STR_PAD_LEFT) }}
        · {{ $detail['judul'] }}
    </strong>

    <p>Lokasi: {{ $detail['lokasi'] }}</p>
</div>

<dl class="detail-grid">

    <div>
        <dt>Status</dt>
        <dd>
            <span class="tag tag-status">
                {{ $laporan->status }}
            </span>
        </dd>
    </div>

    <div>
        <dt>Tanggal pengajuan</dt>
        <dd>
            {{ \Illuminate\Support\Carbon::parse(
                $laporan->tanggalPengajuan
            )->format('d M Y, H:i') }}
        </dd>
    </div>

    <div class="detail-full">
        <dt>Deskripsi keluhan</dt>
        <dd class="multiline">{{ $detail['deskripsi'] }}</dd>
    </div>

    <div class="detail-full">
        <dt>Foto bukti</dt>
        <dd>
            @if ($laporan->foto)
                <a class="text-link"
                   href="{{ route('keluhan.foto', $laporan->idKeluhan) }}"
                   target="_blank"
                   rel="noopener noreferrer">
                    Lihat foto bukti
                </a>
            @else
                Tidak dilampirkan
            @endif
        </dd>
    </div>

    @php
        $tambahan = $laporan->informasiTambahan;
        $info = json_decode((string) $tambahan, true);
        $permintaan = is_array($info) && ($info['uc14'] ?? false) === true;
        $pertanyaan = $permintaan ? ($info['pertanyaan'] ?? null) : null;
        $jawaban = $permintaan ? ($info['jawaban'] ?? null) : null;
    @endphp

    @if ($permintaan && filled($pertanyaan))
        <div class="detail-full">
            <dt>Pertanyaan dari pemilik</dt>
            <dd class="multiline">{{ $pertanyaan }}</dd>
        </div>

        @if (filled($jawaban))
            <div class="detail-full">
                <dt>Jawaban kamu</dt>
                <dd class="multiline">{{ $jawaban }}</dd>
            </div>
        @else
            <div class="detail-full">
                <dt>Tanggapan</dt>
                <dd>
                    <a class="btn btn-primary"
                       href="{{ route('keluhan.tanggapan', $laporan->idKeluhan) }}">
                        Beri tanggapan
                    </a>
                </dd>
            </div>
        @endif
    @elseif (!$permintaan && filled($tambahan))
        <div class="detail-full">
            <dt>Informasi tambahan</dt>
            <dd class="multiline">{{ $tambahan }}</dd>
        </div>
    @endif

</dl>

<div class="actions">
    <a class="btn btn-secondary"
       href="{{ route('keluhan.ajukan') }}">
        Ajukan laporan lain
    </a>
</div>

@endsection