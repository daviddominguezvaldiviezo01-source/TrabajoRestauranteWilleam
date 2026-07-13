document.querySelectorAll('.metodo-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('activo'));
        this.classList.add('activo');
        const val = this.querySelector('input').value;
        document.getElementById('qrPlin').style.display = val === 'plin' ? 'block' : 'none';
    });
});

const selectDir = document.querySelector('select[name="id_direccion"]');
const inputDir = document.querySelector('input[name="nueva_direccion"]');
const inputRef = document.querySelector('input[name="referencia"]');

if (selectDir) {
    selectDir.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            inputDir.value = opt.getAttribute('data-dir');
            inputRef.value = opt.getAttribute('data-ref');
            inputDir.classList.remove('is-invalid'); // In case it had validation errors
        } else {
            inputDir.value = '';
            inputRef.value = '';
        }
    });

    const resetSelect = () => { selectDir.value = ''; };
    if (inputDir) inputDir.addEventListener('input', resetSelect);
    if (inputRef) inputRef.addEventListener('input', resetSelect);
}