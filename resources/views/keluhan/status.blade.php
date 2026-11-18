@extends('keluhan.layout')

@section('title', 'Keluhan Saya')
@section('heading', 'Keluhan saya')
@section(
    'subtitle',
    'Pantau status laporan yang telah dikirimkan kepada pemilik kost.'
)

@section('content')

<div class="table-heading">
    <h2>Riwayat laporan</h2>

    <a class="btn btn-primary"
       href="{{ route('keluhan.ajukan') }}">
        + Ajukan keluhan
    </a>
</div>

@if ($laporan->isEmpty())

    <div class="empty-state">
        Belum ada laporan keluhan.
        Gunakan tombol “Ajukan keluhan” untuk
        mengirim laporan pertama.
    </div>

@else

    <div class="report-list">
        @foreach ($laporan as $item)

            <a href="{{ route('keluhan.detail', $item->idKeluhan) }}"
               class="report-row">

                <span>
                    <strong>
                        KLH-{{ str_pad($item->idKeluhan, 3, '0', STR_PAD_LEFT) }}
                        · {{ $item->judulKeluhan }}
                    </strong>

                    <small>
                        {{ $item->lokasiKeluhan }}
                        ·
                        {{ \Illuminate\Support\Carbon::parse(
                            $item->tanggalPengajuan
                        )->format('d/m/Y H:i') }}
                    </small>
                </span>

                <span class="tag tag-status">
                    {{ $item->status }}
                </span>

            </a>

        @endforeach
    </div>

@endif

@endsection