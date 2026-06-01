document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            document.querySelectorAll('.producto-item').forEach(el => {
                const name = el.querySelector('[data-nombre]')?.getAttribute('data-nombre') || '';
                el.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    const toastMsg = document.getElementById('toastMsg');
    if (toastMsg) {
        window.setTimeout(() => {
            toastMsg.style.opacity = '0';
            toastMsg.style.transform = 'translateX(20px)';
            window.setTimeout(() => {
                toastMsg.remove();
            }, 300);
        }, 3000);
    }
});
