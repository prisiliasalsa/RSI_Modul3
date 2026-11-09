@extends('tagihan.layout')
@section('title', 'Tagihan sewa')
@section('heading', 'Tagihan sewa')
@section('content')
{{-- UI-07.01: Daftar tagihan sewa --}}
@if ($gangguan)
    <div class="alert alert-danger" role="alert">
        @include('tagihan.partials.icon', ['name' => 'info'])
        <span>Terjadi gangguan data</span>
    </div>
@elseif ($errors->any())
    <div class="alert alert-warning" role="status">
        @include('tagihan.partials.icon', ['name' => 'info'])
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<div class="summary-grid">
    <div class="card summary">
        <div class="summary-label">
            @include('tagihan.partials.icon', ['name' => 'wallet'])
            <span>Belum lunas</span>
        </div>
        <p class="summary-value">{{ $belumLunas }}</p>
    </div>
    <div class="card summary">
        <div class="summary-label">
            @include('tagihan.partials.icon', ['name' => 'file'])
            <span>Kamar</span>
        </div>
        <p class="summary-value">
            {{ $penghuni->namaKostPendek }} · {{ $penghuni->nomorKamar }}
        </p>
    </div>
</div>

<section class="card table-card">
    <div class="table-head">
        <h2>Daftar data</h2>
        <label class="search">
            @include('tagihan.partials.icon', ['name' => 'search'])
            <input type="search" placeholder="Cari data..."
                   data-cari-tagihan>
        </label>
    </div>

    @if ($daftar->isEmpty())
        <p class="empty">Belum ada tagihan sewa</p>
    @else
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Jatuh tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($daftar as $tagihan)
                    <tr data-baris-tagihan>
                        <td class="periode">{{ $tagihan->periodeLabel }}</td>
                        <td>{{ $tagihan->jumlahRupiah }}</td>
                        <td>{{ $tagihan->jatuhTempoLabel }}</td>
                        <td>
                            <span class="badge {{ $tagihan->statusKelas }}">
                                {{ $tagihan->status }}
                            </span>
                        </td>
                        <td>
                            <a class="link-aksi"
                               href="{{ route('tagihan.bayar',
                                    $tagihan->idTagihan) }}">
                                Lihat detail
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-foot">
            <span>Menampilkan {{ $daftar->count() }} data</span>
            <div class="pager">
                <a href="{{ $daftar->previousPageUrl() ?? '#' }}"
                   aria-label="Sebelumnya">‹</a>
                <span>{{ $daftar->currentPage() }}</span>
                <a href="{{ $daftar->nextPageUrl() ?? '#' }}"
                   aria-label="Berikutnya">›</a>
            </div>
        </div>
    @endif
</section>

@if ($idBerikutnya)
    <a class="btn btn-primary"
       href="{{ route('tagihan.bayar', $idBerikutnya) }}">
        Bayar tagihan
    </a>
@endif
@endsection
