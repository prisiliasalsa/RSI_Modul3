{{-- <<boundary>> HalamanKelolaKost: UI-09.01 Kelola kost (daftar), keadaan kosong, dan pesan gagal --}}
@extends('kost._kerangka')

@section('judul', $heading)
@section('heading', $heading)

@section('konten')

    @if ($berhasil)
        <div class="banner-berhasil" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>
            {{ $berhasil }}
        </div>
    @endif

    <div class="stat-grid">
        <div class="stat"><small>Kost terdaftar</small><strong>{{ $ringkas['kost'] }} kost</strong></div>
        <div class="stat"><small>Kamar terdaftar</small><strong>{{ $ringkas['kamar'] }} kamar</strong></div>
        <div class="stat"><small>Dipublikasikan</small><strong>{{ $ringkas['publik'] }} kost</strong></div>
    </div>

    <div class="aksi">
        <a class="btn btn-utama" href="{{ route('kost.tambah', $qs) }}">+ Tambah Kost</a>
    </div>

    @if ($mode === 'daftar')
        <div class="kartu">
            <div class="kartu-atas">
                <strong>Daftar data</strong>
                <label class="cari">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5f6b76" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" id="cari" placeholder="Cari data...">
                </label>
            </div>

            <div class="tabel-bungkus">
                <table>
                    <thead>
                        <tr>
                            <th>Nama kost</th>
                            <th>Tipe</th>
                            <th>Jumlah kamar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($baris as $b)
                            <tr data-nama="{{ $b['nama'] }}"
                                data-ubah="{{ $b['urlUbah'] }}"
                                data-kamar="{{ $b['urlKamar'] }}"
                                data-publikasi="{{ $b['urlPublikasi'] }}">
                                <td class="nama-kost">{{ $b['nama'] }}</td>
                                <td>{{ $b['tipe'] }}</td>
                                <td>{{ $b['jumlahKamar'] }}</td>
                                <td><span class="lencana-status {{ $b['gaya'] }}">{{ $b['status'] }}</span></td>
                                <td><a href="{{ $b['urlUbah'] }}">Lihat detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="kaki">
                <span id="jumlah">Menampilkan {{ count($baris) }} data</span>
                <span class="pager"><span>‹</span><strong>1</strong><span>›</span></span>
            </div>
        </div>

        <div class="aksi">
            <a class="btn btn-utama" id="btnUbah" href="{{ $baris[0]['urlUbah'] }}">Ubah {{ $baris[0]['nama'] }}</a>
            <a class="btn btn-garis" id="btnKamar" href="{{ $baris[0]['urlKamar'] }}">Kelola Kamar</a>
            <a class="btn btn-garis" id="btnPublikasi" href="{{ $baris[0]['urlPublikasi'] }}">Publikasikan</a>
        </div>

        <script>
            // Memilih satu kost dari daftar; tombol di bawah tabel bekerja untuk kost yang dipilih.
            (function () {
                var baris = document.querySelectorAll('tbody tr[data-nama]');
                var cari = document.getElementById('cari');
                var jumlah = document.getElementById('jumlah');
                var lencana = document.getElementById('lencana');

                function pilih(tr) {
                    baris.forEach(function (r) { r.classList.remove('dipilih'); });
                    tr.classList.add('dipilih');
                    document.getElementById('btnUbah').href = tr.getAttribute('data-ubah');
                    document.getElementById('btnUbah').textContent = 'Ubah ' + tr.getAttribute('data-nama');
                    document.getElementById('btnKamar').href = tr.getAttribute('data-kamar');
                    document.getElementById('btnPublikasi').href = tr.getAttribute('data-publikasi');
                    lencana.textContent = tr.getAttribute('data-nama');
                }

                baris.forEach(function (tr) {
                    tr.addEventListener('click', function (e) {
                        if (e.target.closest('a')) { return; }
                        pilih(tr);
                    });
                });
                if (baris.length) { pilih(baris[0]); }

                cari.addEventListener('input', function () {
                    var q = cari.value.toLowerCase();
                    var tampil = 0;
                    baris.forEach(function (tr) {
                        var cocok = tr.textContent.toLowerCase().indexOf(q) !== -1;
                        tr.hidden = !cocok;
                        if (cocok) { tampil++; }
                    });
                    jumlah.textContent = 'Menampilkan ' + tampil + ' data';
                });
            })();
        </script>

    @elseif ($mode === 'kosong')
        <div class="kartu kosong">
            <div class="ikon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5a0a24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V21H3z"/><path d="M9 21v-7h6v7"/></svg>
            </div>
            <h2>Belum ada kost</h2>
            <p>Tambahkan kost pertamamu untuk mulai menampilkannya kepada calon penghuni.</p>
        </div>

    @else
        <div class="kartu kosong">
            <div class="ikon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5a0a24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v5M12 16.5v.5"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <h2>Data belum dapat dimuat</h2>
            <p>{{ $pesan }}</p>
        </div>
        <div class="aksi">
            <a class="btn btn-utama" href="{{ route('kost.daftar', $qs) }}">Muat ulang</a>
        </div>
    @endif

@endsection