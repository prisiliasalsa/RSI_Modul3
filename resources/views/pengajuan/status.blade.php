{{-- <<boundary>> HalamanStatusPengajuan: UI-06.01 (daftar), UI-06.03 (belum ada), dan pesan gagal --}}
@extends('pengajuan._kerangka')

@section('judul', $heading)
@section('heading', $heading)
@section('jejak', 'MELIHAT STATUS PENGAJUAN')

@section('konten')

    @if ($mode === 'daftar')
        <!-- ===== UI-06.01 Status pengajuan ===== -->
        <div class="kartu">
            <div class="kartu-atas">
                <strong>Daftar data</strong>
                <label class="cari">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5f6b76" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" id="cari" placeholder="Cari data...">
                </label>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Kost / kamar</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($baris as $b)
                        <tr data-url="{{ $b['url'] }}">
                            <td class="kostkamar">{{ $b['kostKamar'] }}</td>
                            <td>{{ $b['tanggal'] }}</td>
                            <td><span class="lencana-status {{ $b['gaya'] }}">{{ $b['status'] }}</span></td>
                            <td><a href="{{ $b['url'] }}">Lihat detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="kaki">
                <span id="jumlah">Menampilkan {{ count($baris) }} data</span>
                <span class="pager"><span>‹</span><strong>1</strong><span>›</span></span>
            </div>
        </div>

        <div class="aksi">
            <a class="btn btn-utama" id="btnKeputusan" href="{{ $baris[0]['url'] }}">Lihat keputusan</a>
            <a class="btn btn-garis" href="{{ url('/cari-kost') }}">Cari kost lain</a>
        </div>

        <script>
            // Memilih satu pengajuan dari daftar (PC-06), lalu "Lihat keputusan" membuka detail pilihan itu.
            (function () {
                var baris = document.querySelectorAll('tbody tr[data-url]');
                var tombol = document.getElementById('btnKeputusan');
                var cari = document.getElementById('cari');
                var jumlah = document.getElementById('jumlah');

                function pilih(tr) {
                    baris.forEach(function (r) { r.classList.remove('dipilih'); });
                    tr.classList.add('dipilih');
                    tombol.href = tr.getAttribute('data-url');
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
        <!-- ===== UI-06.03 Belum ada pengajuan sewa ===== -->
        <div class="kartu kosong">
            <div class="ikon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5a0a24" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            </div>
            <h2>Pengajuanmu akan muncul di sini</h2>
            <p>Cari kost dan ajukan sewa pada kamar yang tersedia.</p>
        </div>
        <div class="aksi">
            <a class="btn btn-utama" href="{{ url('/cari-kost') }}">Cari Kost</a>
        </div>

    @else
        <!-- ===== Pesan gagal (gangguan data) ===== -->
        <div class="kartu kosong">
            <div class="ikon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5a0a24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v5M12 16.5v.5"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <h2>Data belum dapat dimuat</h2>
            <p>{{ $pesan }}</p>
        </div>
        <div class="aksi">
            <a class="btn btn-utama" href="{{ route('pengajuan.muatUlang', $qs) }}">Muat ulang</a>
        </div>
    @endif

@endsection