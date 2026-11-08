@extends('keluhan.layout')

@section('title', 'Detail Keluhan')
@section('heading', 'Detail keluhan')
@section(
    'subtitle',
    'Status laporan akan diperbarui oleh pemilik kost melalui UC-14.'
)

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
        <dd class="multiline">
            {{ $detail['deskripsi'] }}
        </dd>
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

    @if ($laporan->informasiTambahan)
        <div class="detail-full">
            <dt>Informasi tambahan</dt>
            <dd class="multiline">
                {{ $laporan->informasiTambahan }}
            </dd>
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