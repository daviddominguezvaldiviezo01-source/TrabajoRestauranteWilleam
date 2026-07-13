let t = 30;
const el = document.getElementById('t');
const iv = setInterval(() => {
    t--;
    if (el) el.textContent = t;
    if (t <= 0) { clearInterval(iv); window.location.href = 'index.php'; }
}, 1000);