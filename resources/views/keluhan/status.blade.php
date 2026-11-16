@extends('pengajuan._kerangka')

@section('judul', $heading)
@section('heading', $heading)
@section('jejak', 'MELIHAT STATUS PENGAJUAN')

@section('konten')
    @if ($mode === 'daftar')
        <div class="kartu">
            <div class="kartu-atas">
                <strong>Daftar data</strong>

                <label class="cari">
                    <svg width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="#5f6b76" stroke-width="2"
                         stroke-linecap="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-3.5-3.5"/>
                    </svg>
                    <input type="search" id="cari" placeholder="Cari data..."
                           aria-label="Cari pengajuan">
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
                            <td>
                                <span class="lencana-status {{ $b['gaya'] }}">
                                    {{ $b['status'] }}
                                </span>
                            </td>
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
            @if (count($baris) > 0)
                <a class="btn btn-utama" id="btnKeputusan" href="{{ $baris[0]['url'] }}">
                    Lihat keputusan
                </a>
            @endif

            <a class="btn btn-garis" href="{{ url('/cari-kost') }}">Cari kost lain</a>
        </div>

        <script>
            (() => {
                const baris = [...document.querySelectorAll('tbody tr[data-url]')];
                const tombol = document.getElementById('btnKeputusan');
                const cari = document.getElementById('cari');
                const jumlah = document.getElementById('jumlah');

                function pilih(terpilih) {
                    baris.forEach((item) => {
                        item.classList.toggle('dipilih', item === terpilih);
                    });

                    if (tombol) {
                        tombol.style.display = terpilih ? '' : 'none';

                        if (terpilih) {
                            tombol.href = terpilih.dataset.url;
                        }
                    }
                }

                baris.forEach((item) => {
                    item.addEventListener('click', (event) => {
                        if (!event.target.closest('a')) {
                            pilih(item);
                        }
                    });
                });

                pilih(baris[0]);

                cari.addEventListener('input', () => {
                    const kataKunci = cari.value.trim().toLowerCase();
                    const terlihat = baris.filter((item) => {
                        const cocok = item.textContent.toLowerCase().includes(kataKunci);
                        item.hidden = !cocok;
                        return cocok;
                    });

                    const terpilih = terlihat.find((item) => item.classList.contains('dipilih'));
                    pilih(terpilih ?? terlihat[0]);
                    jumlah.textContent = `Menampilkan ${terlihat.length} data`;
                });
            })();
        </script>

    @elseif ($mode === 'kosong')
        <div class="kartu kosong">
            <div class="ikon">
                <svg width="24" height="24" viewBox="0 0 24 24"
                     fill="none" stroke="#5a0a24" stroke-width="1.8"
                     stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-3.5-3.5"/>
                </svg>
            </div>
            <h2>Pengajuanmu akan muncul di sini</h2>
            <p>Cari kost dan ajukan sewa pada kamar yang tersedia.</p>
        </div>

        <div class="aksi">
            <a class="btn btn-utama" href="{{ url('/cari-kost') }}">Cari Kost</a>
        </div>

    @else
        <div class="kartu kosong">
            <div class="ikon">
                <svg width="24" height="24" viewBox="0 0 24 24"
                     fill="none" stroke="#5a0a24" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v5M12 16.5v.5"/>
                </svg>
            </div>
            <h2>Data belum dapat dimuat</h2>
            <p>{{ $pesan }}</p>
        </div>

        <div class="aksi">
            <a class="btn btn-utama" href="{{ route('pengajuan.muatUlang', $qs) }}">
                Muat ulang
            </a>
        </div>
    @endif
@endsection