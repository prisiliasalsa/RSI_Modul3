// Menampilkan nama berkas yang dipilih pada kotak unggah bukti
document.querySelectorAll('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const caption = document.getElementById('upload-caption');
        if (!caption) return;
        caption.textContent = input.files?.length
            ? input.files[0].name
            : 'Pilih berkas atau tarik ke sini';
    });
});

// Efek saat berkas ditarik ke kotak unggah
document.querySelectorAll('[data-dropzone]').forEach((zone) => {
    ['dragenter', 'dragover'].forEach((nama) => {
        zone.addEventListener(nama, (e) => {
            e.preventDefault();
            zone.classList.add('is-over');
        });
    });
    ['dragleave', 'drop'].forEach((nama) => {
        zone.addEventListener(nama, () => zone.classList.remove('is-over'));
    });
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        const input = zone.querySelector('input[type="file"]');
        if (input && e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
});

// Memilih metode atau bank memuat ulang halaman dengan pilihan baru
const pindahPilihan = (metode, bank) => {
    const alamat = new URL(window.location.href);
    alamat.searchParams.set('metode', metode);
    if (bank) alamat.searchParams.set('bank', bank);
    else alamat.searchParams.delete('bank');
    window.location.href = alamat.toString();
};

document.querySelector('[data-pilih-metode]')?.addEventListener(
    'change',
    (e) => pindahPilihan(e.target.value, null)
);
document.querySelector('[data-pilih-bank]')?.addEventListener(
    'change',
    (e) => pindahPilihan('Transfer Bank', e.target.value)
);

// Pencarian tagihan pada daftar
document.querySelector('[data-cari-tagihan]')?.addEventListener(
    'input',
    (e) => {
        const kata = e.target.value.trim().toLowerCase();
        document.querySelectorAll('[data-baris-tagihan]').forEach((baris) => {
            baris.hidden = !baris.textContent.toLowerCase().includes(kata);
        });
    }
);
