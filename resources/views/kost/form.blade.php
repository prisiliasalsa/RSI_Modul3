{{-- <<boundary>> HalamanKelolaKost: UI-09.02 Tambah / ubah data kost --}}
@extends('kost._kerangka')

@section('judul', $heading)
@section('heading', $heading)

@section('konten')

    @if ($banner)
        <div class="banner" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
            {{ $banner }}
        </div>
    @endif

    @php
        $dipilih = array_map('mb_strtolower', $data['fasilitasUmum']);
    @endphp

    <div class="dua-kolom">
        <form class="kartu form" method="post" action="{{ $aksi }}" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="grid2">
                <div class="field{{ isset($kesalahan['namaKost']) ? ' has-error' : '' }}">
                    <label class="lbl" for="namaKost">Nama kost *</label>
                    <input type="text" id="namaKost" name="namaKost" maxlength="100" value="{{ $data['namaKost'] }}">
                    @isset($kesalahan['namaKost'])<div class="err">{{ $kesalahan['namaKost'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['tipeKost']) ? ' has-error' : '' }}">
                    <label class="lbl" for="tipeKost">Tipe kost *</label>
                    <select id="tipeKost" name="tipeKost">
                        @foreach ($tipeKost as $tipe)
                            <option value="{{ $tipe }}" @selected($data['tipeKost'] === $tipe)>{{ $tipe }}</option>
                        @endforeach
                    </select>
                    @isset($kesalahan['tipeKost'])<div class="err">{{ $kesalahan['tipeKost'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['alamat']) ? ' has-error' : '' }}">
                    <label class="lbl" for="alamat">Alamat *</label>
                    <input type="text" id="alamat" name="alamat" maxlength="255" value="{{ $data['alamat'] }}">
                    @isset($kesalahan['alamat'])<div class="err">{{ $kesalahan['alamat'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['fasilitasUmum']) ? ' has-error' : '' }}">
                    <label class="lbl">Fasilitas umum *</label>
                    <details class="dd" id="ddFasilitas">
                        <summary>
                            <span id="ringkasFasilitas">{{ count($data['fasilitasUmum']) ? implode(', ', $data['fasilitasUmum']) : 'Pilih fasilitas' }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#5f6b76" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="dd-list">
                            @foreach ($fasilitas as $opsi)
                                <label>
                                    <input type="checkbox" name="fasilitasUmum[]" value="{{ $opsi }}" @checked(in_array(mb_strtolower($opsi), $dipilih, true))>
                                    {{ $opsi }}
                                </label>
                            @endforeach
                        </div>
                    </details>
                    @isset($kesalahan['fasilitasUmum'])<div class="err">{{ $kesalahan['fasilitasUmum'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['deskripsi']) ? ' has-error' : '' }}">
                    <label class="lbl" for="deskripsi">Deskripsi *</label>
                    <textarea id="deskripsi" name="deskripsi" maxlength="2000">{{ $data['deskripsi'] }}</textarea>
                    @isset($kesalahan['deskripsi'])<div class="err">{{ $kesalahan['deskripsi'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['peraturan']) ? ' has-error' : '' }}">
                    <label class="lbl" for="peraturan">Peraturan *</label>
                    <textarea id="peraturan" name="peraturan" maxlength="2000">{{ $data['peraturan'] }}</textarea>
                    @isset($kesalahan['peraturan'])<div class="err">{{ $kesalahan['peraturan'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['foto']) ? ' has-error' : '' }}">
                    <label class="lbl" for="foto">Foto kost *</label>
                    <label class="upload" id="kotakUnggah" for="foto">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5a0a24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
                        <strong id="namaBerkas">Pilih berkas atau tarik ke sini</strong>
                        <small>JPG atau PNG · maksimal 2 MB</small>
                    </label>
                    <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden>
                    @isset($kesalahan['foto'])<div class="err">{{ $kesalahan['foto'] }}</div>@endisset
                </div>

                <div class="field{{ isset($kesalahan['kontakPemilik']) ? ' has-error' : '' }}">
                    <label class="lbl" for="kontakPemilik">Kontak pemilik</label>
                    <input type="tel" id="kontakPemilik" name="kontakPemilik" maxlength="100" placeholder="08xxxxxxxxxx" value="{{ $data['kontakPemilik'] }}">
                    @isset($kesalahan['kontakPemilik'])<div class="err">{{ $kesalahan['kontakPemilik'] }}</div>@endisset
                </div>
            </div>

            <div class="aksi" style="margin-bottom:0">
                <button type="submit" class="btn btn-utama">Simpan sebagai Draft</button>
                <a class="btn btn-garis" href="{{ $batal }}">Batal</a>
            </div>
        </form>

        <!-- Pratinjau kost -->
        <aside class="kartu preview">
            @if ($fotoUrl)
                <img id="pratinjauFoto" src="{{ $fotoUrl }}" alt="Foto kost">
                <div class="ph" id="pratinjauKosong" hidden></div>
            @else
                <img id="pratinjauFoto" alt="Foto kost" hidden>
                <div class="ph" id="pratinjauKosong"></div>
            @endif
            <h3 id="pratinjauNama">{{ $data['namaKost'] !== '' ? $data['namaKost'] : 'Nama kost' }}</h3>
            <p id="pratinjauAlamat">{{ $data['alamat'] !== '' ? $data['alamat'] : 'Alamat kost' }}</p>
            <span class="tag">Hunian nyaman</span>
        </aside>
    </div>

    <script>
        (function () {
            var nama = document.getElementById('namaKost');
            var alamat = document.getElementById('alamat');
            var foto = document.getElementById('foto');
            var kotak = document.getElementById('kotakUnggah');
            var namaBerkas = document.getElementById('namaBerkas');
            var gambar = document.getElementById('pratinjauFoto');
            var kosong = document.getElementById('pratinjauKosong');

            // Pratinjau nama dan alamat saat diketik
            nama.addEventListener('input', function () {
                document.getElementById('pratinjauNama').textContent = nama.value || 'Nama kost';
                document.getElementById('lencana').textContent = nama.value || 'Kost.io';
            });
            alamat.addEventListener('input', function () {
                document.getElementById('pratinjauAlamat').textContent = alamat.value || 'Alamat kost';
            });

            // Ringkasan fasilitas yang dicentang
            var kotakCentang = document.querySelectorAll('#ddFasilitas input[type=checkbox]');
            function ringkas() {
                var pilihan = [];
                kotakCentang.forEach(function (c) { if (c.checked) { pilihan.push(c.value); } });
                document.getElementById('ringkasFasilitas').textContent = pilihan.length ? pilihan.join(', ') : 'Pilih fasilitas';
            }
            kotakCentang.forEach(function (c) { c.addEventListener('change', ringkas); });

            // Pratinjau foto yang dipilih
            function tampilkanBerkas() {
                if (!foto.files.length) { return; }
                namaBerkas.textContent = foto.files[0].name;
                gambar.src = URL.createObjectURL(foto.files[0]);
                gambar.hidden = false;
                kosong.hidden = true;
            }
            foto.addEventListener('change', tampilkanBerkas);

            // Tarik dan lepas berkas ke kotak unggah
            kotak.addEventListener('dragover', function (e) { e.preventDefault(); });
            kotak.addEventListener('drop', function (e) {
                e.preventDefault();
                if (e.dataTransfer.files.length) {
                    foto.files = e.dataTransfer.files;
                    tampilkanBerkas();
                }
            });
        })();
    </script>

@endsection