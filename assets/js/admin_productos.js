(function () {
    const input     = document.getElementById('imagenArchivo');
    const zone      = document.getElementById('uploadZone');
    const prevWrap  = document.getElementById('imgPreviewWrap');
    const prevImg   = document.getElementById('imgPreview');
    const btnRemove = document.getElementById('btnRemoveImg');

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            prevImg.src = e.target.result;
            prevWrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function clearPreview() {
        prevImg.src = '';
        prevWrap.style.display = 'none';
        input.value = '';
    }

    input.addEventListener('change', () => {
        if (input.files && input.files[0]) showPreview(input.files[0]);
        else clearPreview();
    });

    btnRemove && btnRemove.addEventListener('click', clearPreview);

    // Drag & drop
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            // Asignar al input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(dt.files[0]);
            input.files = dataTransfer.files;
            showPreview(dt.files[0]);
        }
    });
})();

window.handleImgError = (img) => {
    img.style.display = 'none';
};