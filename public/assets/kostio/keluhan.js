document.querySelectorAll('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const caption = document.getElementById('upload-caption');

        if (!caption) return;

        caption.textContent = input.files?.length
            ? input.files[0].name
            : 'Pilih foto kerusakan (opsional)';
    });
});