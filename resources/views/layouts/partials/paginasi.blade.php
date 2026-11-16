{{-- Footer tabel: jumlah data dan tombol halaman. Parameter: $data (paginator) --}}
<div class="rp-table-footer">
    <span>Menampilkan {{ $data->count() }} data</span>

    <nav class="rp-pages" aria-label="Halaman">
        @if ($data->onFirstPage())
            <span aria-hidden="true">‹</span>
        @else
            <a href="{{ $data->previousPageUrl() }}" aria-label="Halaman sebelumnya">‹</a>
        @endif

        <span class="rp-page-current">{{ $data->currentPage() }}</span>

        @if ($data->hasMorePages())
            <a href="{{ $data->nextPageUrl() }}" aria-label="Halaman selanjutnya">›</a>
        @else
            <span aria-hidden="true">›</span>
        @endif
    </nav>
</div>
