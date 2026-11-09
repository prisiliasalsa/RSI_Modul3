{{-- HalamanDataPenghuni: tampilDaftarPenghuni() / pesanBelumAdaData() (UI-12) --}}
@extends('layouts.ruang-pemilik', ['menuAktif' => 'penghuni'])

@section('judul', 'Data Penghuni')
@section('breadcrumb', 'BERANDA / MENGELOLA DATA PENGHUNI')
@section('heading', 'Data penghuni')
@section('subjudul', 'Data penghuni dan masa sewa tersusun dalam satu tempat.')

@section('content')

@if ($gangguan)

    <section class="rp-card rp-empty" role="alert">
        <h2>Terjadi gangguan data</h2>
        <p>Data penghuni belum dapat dimuat. Silakan coba beberapa saat lagi.</p>
        <a class="rp-btn rp-btn-primary" href="{{ route('pemilik.penghuni.daftar') }}">Muat ulang</a>
    </section>

@elseif (!$adaPenghuni)

    {{-- Alternative Flow 1 (3a): belum ada data penghuni --}}
    <section class="rp-card rp-empty" aria-live="polite">
        <div class="rp-empty-icon">
            @include('keluhan.pemilik.icon', ['name' => 'users', 'size' => 34])
        </div>
        <h2>Belum ada data penghuni</h2>
        <p>Penghuni akan muncul setelah pengajuan sewa disetujui.</p>
    </section>

@else

    {{-- Panel ringkasan --}}
    <div class="rp-stats">
        <section class="rp-card rp-stat">
            <div class="rp-stat-label">
                @include('keluhan.pemilik.icon', ['name' => 'wallet', 'size' => 18])
                <span>Penghuni ditampilkan</span>
            </div>
            <strong>{{ $daftar->total() }} orang</strong>
        </section>

        <section class="rp-card rp-stat">
            <div class="rp-stat-label">
                @include('keluhan.pemilik.icon', ['name' => 'file', 'size' => 18])
                <span>Status sewa</span>
            </div>
            <strong>{{ $status }}</strong>
        </section>
    </div>

    {{-- Tabel daftar penghuni --}}
    <section class="rp-card rp-table-card">

        <div class="rp-table-header">
            <h2>Daftar penghuni</h2>

            <form method="GET" action="{{ route('pemilik.penghuni.daftar') }}" class="rp-filter" role="search">
                <label class="rp-visually-hidden" for="status">Status sewa</label>
                <select id="status" name="status" class="rp-select" onchange="this.form.submit()">
                    @foreach ($pilihanStatus as $pilihan)
                        <option value="{{ $pilihan }}" @selected($status === $pilihan)>{{ $pilihan }}</option>
                    @endforeach
                </select>

                <div class="rp-search">
                    @include('keluhan.pemilik.icon', ['name' => 'search', 'size' => 18])
                    <input
                        type="search"
                        name="cari"
                        value="{{ $cari }}"
                        maxlength="120"
                        placeholder="Cari data..."
                        aria-label="Cari penghuni"
                    >
                </div>
            </form>
        </div>

        <div class="rp-table-scroll">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Penghuni</th>
                        <th>Kost / kamar</th>
                        <th>Periode sewa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $baris)
                        <tr>
                            <td class="rp-strong">{{ $baris->namaLengkap }}</td>
                            <td>{{ $baris->namaKostPendek }} · {{ $baris->nomorKamar }}</td>
                            <td>{{ $baris->periodeSewa }}</td>
                            <td>
                                <span class="rp-status {{ $baris->statusKelas }}">{{ $baris->status }}</span>
                            </td>
                            <td>
                                <a
                                    class="rp-link"
                                    href="{{ route('pemilik.penghuni.detail', $baris->idPenghuni) }}"
                                >Lihat detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="rp-no-match">
                                Tidak ada penghuni yang sesuai dengan filter atau pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('layouts.partials.paginasi', ['data' => $daftar])

    </section>

@endif

@endsection
