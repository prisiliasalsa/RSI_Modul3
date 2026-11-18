{{--
    Partial visual-only. Tidak mengubah controller, data halaman, atau route bisnis.
    Template dirender di server; kostio-unified.js hanya mengganti chrome
    (sidebar + topbar) milik halaman yang sudah mempunyai keduanya.
--}}
@php
    $kioRole = (string) session('jenisAkun', '');
    if (!in_array($kioRole, ['Pemilik Kost', 'Penghuni', 'Calon Penghuni'], true)) {
        $kioRole = request()->is('pemilik/*', 'kelola-kost*', 'kost/*/kamar*')
            ? 'Pemilik Kost'
            : (request()->is('tagihan-sewa*', 'keluhan/*') ? 'Penghuni' : 'Calon Penghuni');
    }

    $kioName = trim((string) (
        ($pemilik->namaLengkap ?? null)
        ?? ($penghuni->namaLengkap ?? null)
        ?? ($pengguna['nama'] ?? null)
        ?? data_get($akunLoginLaravel ?? null, 'namaLengkap')
        ?? data_get($akunLogin ?? null, 'namaLengkap')
        ?? session('namaLengkap')
        ?? ''
    ));

    // Beberapa halaman lama mengirim peran sebagai nama tampilan cadangan.
    if (in_array($kioName, ['Pemilik Kost', 'Penghuni', 'Calon Penghuni'], true)) {
        $kioName = '';
    }

    $kioIdAkun = session('idAkun');
    $kioKostId = request()->route('idKost');

    if ($kioIdAkun && ($kioName === '' || ($kioRole === 'Pemilik Kost' && !$kioKostId))) {
        try {
            if ($kioName === '') {
                $kioName = (string) (\Illuminate\Support\Facades\DB::table('akun')
                    ->where('idAkun', $kioIdAkun)
                    ->value('namaLengkap') ?: '');
            }
            if ($kioRole === 'Pemilik Kost' && !$kioKostId) {
                $kioKostId = \Illuminate\Support\Facades\DB::table('kost')
                    ->where('idPemilik', $kioIdAkun)
                    ->orderBy('idKost')
                    ->value('idKost');
            }
        } catch (\Throwable $ignored) {
            // Halaman tetap terbuka saat data profil/kost belum tersedia.
        }
    }

    $kioName = $kioName !== '' ? $kioName : $kioRole;
    $kioInitials = collect(preg_split('/\s+/', $kioName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $kioIsOwner = $kioRole === 'Pemilik Kost';
    $kioMenu = $kioIsOwner ? [
        ['icon' => 'home', 'label' => 'Kelola Kost', 'route' => 'kost.daftar', 'active' => request()->routeIs('kost.daftar', 'kost.tambah', 'kost.ubah', 'kost.publikasi')],
        ['icon' => 'key', 'label' => 'Kelola Kamar', 'route' => 'kamar.index', 'active' => request()->routeIs('kamar.*', 'kost.kamar')],
        ['icon' => 'file', 'label' => 'Pengajuan Sewa', 'route' => 'pemilik.pengajuan-sewa.index', 'active' => request()->routeIs('pemilik.pengajuan-sewa.*')],
        ['icon' => 'users', 'label' => 'Data Penghuni', 'route' => 'pemilik.penghuni.daftar', 'active' => request()->routeIs('pemilik.penghuni.*')],
        ['icon' => 'wallet', 'label' => 'Verifikasi Pembayaran', 'route' => 'pemilik.verifikasi.daftar', 'active' => request()->routeIs('pemilik.verifikasi.*')],
        ['icon' => 'chat', 'label' => 'Keluhan / Kerusakan', 'route' => 'pemilik.keluhan.daftar', 'active' => request()->routeIs('pemilik.keluhan.*')],
        ['icon' => 'chart', 'label' => 'Laporan Keuangan', 'route' => 'pemilik.keuangan.index', 'active' => request()->routeIs('pemilik.keuangan.*')],
    ] : [
        ['icon' => 'search', 'label' => 'Cari Kost', 'route' => 'kost.cari', 'active' => request()->routeIs('kost.cari', 'kost.detail', 'kamar.detail', 'pengajuan.ajukan')],
        ['icon' => 'file', 'label' => 'Status Pengajuan', 'route' => 'pengajuan.status', 'active' => request()->routeIs('pengajuan.status', 'pengajuan.detail', 'pengajuan.muatUlang')],
        ['icon' => 'wallet', 'label' => 'Tagihan Sewa', 'route' => 'tagihan-sewa.index', 'active' => request()->routeIs('tagihan-sewa.*')],
    ];

    if ($kioRole === 'Penghuni' && $kioIdAkun && (
        \Illuminate\Support\Facades\DB::table('penghuni')
            ->where('idAkun', $kioIdAkun)
            ->where('status', 'Aktif')
            ->exists()
    )) {
        $kioMenu[] = ['icon' => 'chat', 'label' => 'Keluhan Saya', 'route' => 'keluhan.status', 'active' => request()->routeIs('keluhan.*')];
    }

    $kioHome = $kioIsOwner ? 'kost.daftar' : 'kost.cari';
@endphp

@if (!empty($kioRenderServer))
    @section('kioResidentSidebar')
@else
    <template id="kostio-ui-shell-template">
@endif
    <div class="kio-sidebar-content" data-kio-sidebar-content data-kio-space="{{ $kioIsOwner ? 'pemilik' : 'penghuni' }}">
        <a class="kio-brand" href="{{ route($kioHome) }}">
            @include('keluhan.pemilik.icon', ['name' => 'home', 'size' => 20])
            <strong>kost.io</strong>
        </a>
        <div class="kio-workspace">
            <small>{{ $kioIsOwner ? 'RUANG PEMILIK' : 'RUANG PENGHUNI' }}</small>
            <span>{{ $kioIsOwner ? 'Kelola hunianmu' : 'Temukan nyamannya' }}</span>
        </div>
        <nav class="kio-nav" aria-label="Navigasi {{ $kioRole }}">
            @foreach ($kioMenu as $kioItem)
                @php
                    $kioRoute = $kioItem['route'];
                    $kioExists = \Illuminate\Support\Facades\Route::has($kioRoute);
                    $kioUrl = null;
                    if ($kioExists && $kioRoute === 'kamar.index') {
                        $kioUrl = $kioKostId
                            ? route('kamar.index', ['idKost' => $kioKostId])
                            : (\Illuminate\Support\Facades\Route::has('kost.daftar') ? route('kost.daftar') : null);
                    } elseif ($kioExists) {
                        $kioUrl = route($kioRoute);
                    }
                @endphp
                @if ($kioUrl)
                    <a class="kio-nav-link {{ $kioItem['active'] ? 'is-active' : '' }}"
                       href="{{ $kioUrl }}" @if($kioItem['active']) aria-current="page" @endif>
                        @include('keluhan.pemilik.icon', ['name' => $kioItem['icon']])
                        <span>{{ $kioItem['label'] }}</span>
                    </a>
                @else
                    <span class="kio-nav-link is-disabled" aria-disabled="true">
                        @include('keluhan.pemilik.icon', ['name' => $kioItem['icon']])
                        <span>{{ $kioItem['label'] }}</span>
                    </span>
                @endif
            @endforeach
        </nav>
        <div class="kio-sidebar-grow"></div>
        <div class="kio-help"><strong>Butuh bantuan?</strong><span>Kelola tempat tinggal dengan lebih tenang.</span></div>
        @if (\Illuminate\Support\Facades\Route::has('logout'))
            <form class="kio-logout" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" aria-label="Keluar dari akun">
                    @include('keluhan.pemilik.icon', ['name' => 'logout'])
                    <span>Keluar</span>
                </button>
            </form>
        @else
            <a class="kio-logout-link" href="{{ route('login') }}">Masuk / ganti akun</a>
        @endif
    </div>
@if (!empty($kioRenderServer))
    @endsection
    @section('kioResidentHeader')
@endif
    <div class="kio-header-content" data-kio-header-content>
        <div class="kio-head-path">Kost.io <span>/</span> {{ $kioRole }}</div>
        <div class="kio-account">
            <span class="kio-bell" aria-hidden="true">@include('keluhan.pemilik.icon', ['name' => 'bell', 'size' => 20])</span>
            <span class="kio-avatar" aria-hidden="true">{{ $kioInitials ?: 'K' }}</span>
            <div class="kio-account-text"><strong>{{ $kioName }}</strong><small>{{ $kioRole }}</small></div>
        </div>
    </div>
@if (!empty($kioRenderServer))
    @endsection
@else
    </template>
@endif
