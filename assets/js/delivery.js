(function(){
    const btn = document.getElementById('notifBtn');
    const panel = document.getElementById('notifPanel');
    let lastIr = 0;
    let initialLoaded = false;

    function showToast(message){
        let toast = document.getElementById('notifToast');
        if(!toast){
            toast = document.createElement('div');
            toast.id = 'notifToast';
            toast.className = 'notif-toast';
            toast.innerHTML = '<div class="bell"><i class="fas fa-bell" style="color:#fff"></i></div><div class="msg"></div>';
            document.body.appendChild(toast);
        }
        toast.querySelector('.msg').textContent = message;
        toast.classList.add('show');
        setTimeout(()=>{ toast.classList.remove('show'); },5000);
    }

    // Usar SSE para recibir notificaciones en tiempo real
    let es;
    try {
        es = new EventSource('tools/sse_orders.php');
    } catch(e) { console.error('EventSource no disponible', e); }

    function renderRecent(orders){
        panel.innerHTML = '';
        if(!orders || orders.length===0){ panel.innerHTML = '<div class="notif-item">No hay nuevos pedidos</div>'; return; }
        orders.forEach(o=>{
            const div = document.createElement('div');
            div.className = 'notif-item';
            div.innerHTML = '<strong>Pedido #' + o.id_pedido + '</strong>' +
                '<div>Total: S/ ' + (o.total.toFixed? o.total.toFixed(2): Number(o.total).toFixed(2)) + '</div>' +
                '<div>Cliente: ' + (o.cliente || o.email || 'Invitado') + '</div>';
            panel.appendChild(div);
        });
    }

    let pollingIntervalId = null;
    function startPolling(){
        if (pollingIntervalId) return;
        async function fetchCountPolling(){
            try{
                const r = await fetch('tools/get_new_orders_count.php');
                const j = await r.json();
                const c = parseInt(j.count || 0,10);
                if (!initialLoaded) {
                    lastIr = c;
                    initialLoaded = true;
                } else {
                    if (c > lastIr) {
                        const msg = 'Nuevo pedido pendiente';
                        showToast(msg);
                        try{ new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg').play(); }catch(e){}
                        if (panel.classList.contains('show')) fetch('tools/get_recent_orders.php').then(r=>r.json()).then(j=>renderRecent(j.orders||[])).catch(()=>{});
                    }
                    lastIr = c;
                }
            }catch(e){ console.error('poll fetchCount error', e); }
        }
        fetchCountPolling();
        pollingIntervalId = setInterval(fetchCountPolling, 2000);
    }

    if (es) {
        es.addEventListener('orders', function(e){
            try{
                const payload = JSON.parse(e.data);
                const counts = payload.counts || {};
                const totalIr = counts['ir a recoger'] || 0;
                if (!initialLoaded) {
                    lastIr = totalIr;
                    initialLoaded = true;
                } else {
                    if (totalIr > lastIr) {
                        const recent = payload.recent || payload.orders || [];
                        const last = recent.length ? recent[0] : null;
                        const msg = last ? ('Nuevo pedido #' + last.id_pedido + (last.cliente? (' - ' + last.cliente) : '')) : 'Nuevo pedido pendiente';
                        showToast(msg);
                        try{ new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg').play(); }catch(e){}
                        renderRecent(payload.recent || payload.orders || []);
                    }
                    lastIr = totalIr;
                }
            }catch(err){ console.error('Parse SSE orders', err); }
        });

        es.onerror = function(err){ console.error('SSE error', err); startPolling(); };
    } else {
        startPolling();
    }

    btn.addEventListener('click', function(e){
        const was = panel.classList.toggle('show');
        panel.setAttribute('aria-hidden', !was);
        if (was && es) {
            fetch('tools/get_recent_orders.php').then(r=>r.json()).then(j=>{ renderRecent(j.orders || []); }).catch(()=>{});
        }
    });

    // Interceptar formularios de acción para ejecutar vía AJAX
    document.addEventListener('submit', function(ev){
        const form = ev.target;
        if (!form.classList.contains('delivery-action-form')) return;
        ev.preventDefault();
        const data = new FormData(form);
        fetch('tools/update_order_status.php', {
            method: 'POST',
            body: data,
            credentials: 'same-origin'
        }).then(r=>r.json()).then(j=>{
            if (j.success) {
                // Actualizar visualmente la tarjeta: cambiar badge a 'En camino' o 'Entregado' o 'Cancelado'
                const card = form.closest('.delivery-card');
                const badge = card && card.querySelector('.badge-estado');
                if (badge) {
                    if (j.action === 'tomar') { badge.textContent = 'En camino'; badge.className = 'badge-estado ' + 'badge-en_camino'; }
                    if (j.action === 'entregar') { badge.textContent = 'Entregado'; badge.className = 'badge-estado ' + 'badge-entregado'; }
                    if (j.action === 'cancelar') { badge.textContent = 'Cancelado'; badge.className = 'badge-estado ' + 'badge-cancelado'; }
                }
                // Mostrar alerta breve
                const alert = document.createElement('div'); alert.className='delivery-alert'; alert.textContent = '✔ Acción aplicada: ' + j.action; document.body.prepend(alert);
                setTimeout(()=>alert.remove(),3000);
            } else {
                const alert = document.createElement('div'); alert.className='delivery-alert'; alert.textContent = '✖ Error: ' + (j.message || 'No se aplicó'); document.body.prepend(alert);
                setTimeout(()=>alert.remove(),4000);
            }
        }).catch(err=>{ console.error('update err', err); });
    });
})();
