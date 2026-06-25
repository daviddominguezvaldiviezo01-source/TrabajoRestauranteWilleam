<?php
/**
 * ============================================================
 * ARCHIVO: delivery.php
 * ============================================================
 * DESCRIPCIÓN: Panel de repartidor para revisar pedidos asignados,
 * cambiar estados y ver detalles en tiempo real.
 * ============================================================
 */

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

iniciar_sesion_segura();

if (!validar_rol('delivery')) {
    redirigir('cliente/login.php');
}

$csrf_token = generar_token_csrf();
$id_repartidor = intval($_SESSION['usuario']);

$filtro = $_GET['f'] ?? 'ir a recoger';
$estados_validos = ['ir a recoger', 'en camino', 'entregado', 'cancelado', 'all'];
if (!in_array($filtro, $estados_validos, true)) {
    $filtro = 'ir a recoger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    if (!validar_token_csrf()) {
        $_SESSION['delivery_message'] = '❌ Token CSRF inválido. Recarga la página.';
        redirigir('delivery.php?f=' . urlencode($filtro));
    }

    $id_pedido = intval($_POST['id_pedido'] ?? 0);
    $accion = trim($_POST['accion'] ?? '');

    if ($id_pedido <= 0) {
        $_SESSION['delivery_message'] = '❌ Pedido inválido.';
        redirigir('delivery.php?f=' . urlencode($filtro));
    }

    if ($accion === 'tomar') {
        $stmt = mysqli_prepare($conexion,
            "UPDATE pedidos SET estado='en camino' WHERE id_pedido = ? AND id_repartidor = ? AND estado = 'ir a recoger'");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $id_pedido, $id_repartidor);
            mysqli_stmt_execute($stmt);
            $_SESSION['delivery_message'] = mysqli_stmt_affected_rows($stmt) > 0
                ? '✅ Pedido en camino. Actualiza la página para ver el cambio.'
                : '❌ No se pudo iniciar la entrega. Verifica que el pedido te pertenezca y esté listo para recoger.';
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['delivery_message'] = '❌ Error de servidor al cambiar el estado: ' . mysqli_error($conexion);
        }
    } elseif ($accion === 'entregar') {
        $stmt = mysqli_prepare($conexion,
            "UPDATE pedidos SET estado='entregado' WHERE id_pedido = ? AND id_repartidor = ? AND estado = 'en camino'");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $id_pedido, $id_repartidor);
            mysqli_stmt_execute($stmt);
            $_SESSION['delivery_message'] = mysqli_stmt_affected_rows($stmt) > 0
                ? '✅ Pedido marcado como entregado.'
                : '❌ No se pudo marcar como entregado. Verifica el estado del pedido.';
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['delivery_message'] = '❌ Error de servidor al cambiar el estado: ' . mysqli_error($conexion);
        }
    } elseif ($accion === 'cancelar') {
        $stmt = mysqli_prepare($conexion,
            "UPDATE pedidos SET estado='cancelado' WHERE id_pedido = ? AND id_repartidor = ? AND estado IN ('ir a recoger', 'en camino')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $id_pedido, $id_repartidor);
            mysqli_stmt_execute($stmt);
            $_SESSION['delivery_message'] = mysqli_stmt_affected_rows($stmt) > 0
                ? '✅ Pedido cancelado.'
                : '❌ No se pudo cancelar el pedido. Verifica el estado del pedido.';
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['delivery_message'] = '❌ Error de servidor al cambiar el estado: ' . mysqli_error($conexion);
        }
    } else {
        $_SESSION['delivery_message'] = '❌ Acción no permitida.';
    }

    redirigir('delivery.php?f=' . urlencode($filtro));
}

$mensaje = $_SESSION['delivery_message'] ?? '';
unset($_SESSION['delivery_message']);

$pedidos = obtener_pedidos_delivery($id_repartidor, $filtro);
$pedidos_asignados = obtener_pedidos_delivery($id_repartidor, 'all');

$contador_ir = 0;
$contador_camino = 0;
$contador_entregado = 0;
$contador_cancelado = 0;
foreach ($pedidos_asignados as $pedido) {
    if ($pedido['estado'] === 'ir a recoger') {
        $contador_ir++;
    } elseif ($pedido['estado'] === 'en camino') {
        $contador_camino++;
    } elseif ($pedido['estado'] === 'entregado') {
        $contador_entregado++;
    } elseif ($pedido['estado'] === 'cancelado') {
        $contador_cancelado++;
    }
}

function tipo_badge($estado) {
    switch ($estado) {
        case 'ir a recoger': return 'badge-preparando';
        case 'en camino': return 'badge-en_camino';
        case 'entregado': return 'badge-entregado';
        case 'cancelado': return 'badge-cancelado';
        default: return 'badge-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery - Pedidos Asignados</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/delivery.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-ykZ1QQlA4Z5ZQ5BcKrquzVm7M0t1w6kI+cNVYVMVJq/pBcV2aMPNq0+Y9PGJ1xSjQ9hnYz/5+2QrYnxmz6l6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <main class="delivery-wrap">
        <section class="delivery-header">
            <div>
                <span class="delivery-tag">Delivery</span>
                <h1>Pedidos asignados</h1>
                <p class="delivery-description">Revisa tus entregas, actualiza estados y visualiza el detalle completo de cada pedido asignado.</p>
            </div>
            <div style="display:flex;gap:12px;align-items:center">
                <div class="delivery-notifications">
                    <button id="notifBtn" class="notif-btn" aria-label="Notificaciones">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div id="notifPanel" class="notif-panel" aria-hidden="true"></div>
                </div>
                <a href="logout.php" class="btn-delivery btn-delivery-secondary">Cerrar sesión</a>
            </div>
        </section>

        <section class="delivery-stats" aria-label="Resumen de pedidos">
            <article class="stat-card">
                <span class="stat-label">Por recoger</span>
                <strong><?php echo $contador_ir; ?></strong>
            </article>
            <article class="stat-card">
                <span class="stat-label">En camino</span>
                <strong><?php echo $contador_camino; ?></strong>
            </article>
            <article class="stat-card">
                <span class="stat-label">Entregados</span>
                <strong><?php echo $contador_entregado; ?></strong>
            </article>
            <article class="stat-card">
                <span class="stat-label">Cancelados</span>
                <strong><?php echo $contador_cancelado; ?></strong>
            </article>
        </section>

        <section class="delivery-filters" aria-label="Filtros de pedidos">
            <?php foreach (['ir a recoger' => 'Ir a recoger', 'en camino' => 'En camino', 'entregado' => 'Entregados', 'cancelado' => 'Cancelados', 'all' => 'Todos'] as $key => $label): ?>
                <a href="delivery.php?f=<?php echo urlencode($key); ?>" class="filter-pill <?php echo $filtro === $key ? 'active' : ''; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </section>

        <?php if ($mensaje): ?>
            <section class="delivery-alert" aria-live="polite">
                <i class="fas fa-info-circle"></i>
                <?php echo htmlspecialchars($mensaje); ?>
            </section>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <section class="delivery-empty">
                <h2>No hay pedidos en este filtro</h2>
                <p>Cuando el administrador te asigne pedidos, aparecerán aquí automáticamente.</p>
            </section>
        <?php else: ?>
            <section class="delivery-list">
                <?php foreach ($pedidos as $pedido): ?>
                    <?php $items = obtener_items_pedido($pedido['id_pedido']); ?>
                    <article class="delivery-card">
                        <header class="delivery-card-header">
                            <div>
                                <h3>Pedido #<?php echo intval($pedido['id_pedido']); ?></h3>
                                <span class="badge-estado <?php echo tipo_badge($pedido['estado']); ?>"><?php echo htmlspecialchars(ucfirst($pedido['estado'])); ?></span>
                            </div>
                            <div class="delivery-card-meta">
                                <span><?php echo htmlspecialchars($pedido['metodo'] ?? 'Método desconocido'); ?></span>
                                <strong><?php echo formatear_precio($pedido['total']); ?></strong>
                            </div>
                        </header>

                        <div class="delivery-card-body">
                            <div class="delivery-card-block">
                                <span class="block-title">Cliente</span>
                                <p><?php echo htmlspecialchars($pedido['cliente'] ?? 'Invitado'); ?></p>
                                <p><?php echo htmlspecialchars($pedido['telefono'] ?? '-'); ?></p>
                                <p><?php echo htmlspecialchars($pedido['email'] ?? '-'); ?></p>
                            </div>
                            <div class="delivery-card-block">
                                <span class="block-title">Dirección</span>
                                <p><?php echo htmlspecialchars($pedido['direccion'] ?? 'No especificada'); ?></p>
                                <p><?php echo htmlspecialchars($pedido['referencia'] ?? '-'); ?></p>
                            </div>
                            <div class="delivery-card-block delivery-card-items">
                                <span class="block-title">Items</span>
                                <?php if (empty($items)): ?>
                                    <p>No hay productos registrados en este pedido.</p>
                                <?php else: ?>
                                    <ul>
                                        <?php foreach ($items as $item): ?>
                                            <li><?php echo htmlspecialchars($item['nombre'] ?? 'Producto'); ?> × <?php echo intval($item['cantidad']); ?> <span><?php echo formatear_precio($item['subtotal']); ?></span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <footer class="delivery-card-footer">
                            <?php if ($pedido['estado'] === 'ir a recoger'): ?>
                                <form method="POST" class="delivery-action-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="id_pedido" value="<?php echo intval($pedido['id_pedido']); ?>">
                                    <input type="hidden" name="accion" value="tomar">
                                    <button type="submit" name="actualizar_estado" class="btn-delivery btn-delivery-primary">
                                        <i class="fas fa-truck-moving"></i> Tomar pedido
                                    </button>
                                </form>
                                <form method="POST" class="delivery-action-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="id_pedido" value="<?php echo intval($pedido['id_pedido']); ?>">
                                    <input type="hidden" name="accion" value="cancelar">
                                    <button type="submit" name="actualizar_estado" class="btn-delivery btn-delivery-danger">
                                        <i class="fas fa-times"></i> Cancelar pedido
                                    </button>
                                </form>
                            <?php elseif ($pedido['estado'] === 'en camino'): ?>
                                <form method="POST" class="delivery-action-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="id_pedido" value="<?php echo intval($pedido['id_pedido']); ?>">
                                    <input type="hidden" name="accion" value="entregar">
                                    <button type="submit" name="actualizar_estado" class="btn-delivery btn-delivery-success">
                                        <i class="fas fa-check"></i> Marcar entregado
                                    </button>
                                </form>
                                <form method="POST" class="delivery-action-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="id_pedido" value="<?php echo intval($pedido['id_pedido']); ?>">
                                    <input type="hidden" name="accion" value="cancelar">
                                    <button type="submit" name="actualizar_estado" class="btn-delivery btn-delivery-danger">
                                        <i class="fas fa-times"></i> Cancelar pedido
                                    </button>
                                </form>
                            <?php elseif ($pedido['estado'] === 'entregado'): ?>
                                <span class="delivery-label">Pedido entregado</span>
                            <?php else: ?>
                                <span class="delivery-label delivery-label-canceled">Pedido cancelado</span>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
    <style>
    .delivery-notifications{position:relative}
    .notif-btn{background:transparent;border:none;color:inherit;font-size:18px;position:relative;cursor:pointer}
    .notif-count{display:none}
    .notif-panel{position:absolute;right:0;top:40px;background:#fff;color:#111;border:1px solid #ddd;border-radius:8px;min-width:260px;box-shadow:0 6px 24px rgba(0,0,0,.12);display:none;z-index:30;padding:8px}
    .notif-panel.show{display:block}
    .notif-item{padding:8px;border-bottom:1px solid #f1f1f1;font-size:13px}
    .notif-item:last-child{border-bottom:none}
    .notif-item strong{display:block}
    /* Toast notification */
    .notif-toast{position:fixed;right:24px;top:80px;z-index:9999;display:flex;align-items:center;gap:12px;background:#1f1f1f;border:1px solid #333;padding:12px 16px;border-radius:10px;color:#fff;box-shadow:0 8px 30px rgba(0,0,0,.5);opacity:0;transform:translateY(-8px);transition:opacity .25s,transform .25s}
    .notif-toast.show{opacity:1;transform:translateY(0)}
    .notif-toast .bell{background:#c8102e;padding:8px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center}
    .notif-toast .msg{font-weight:700;font-size:14px}
    </style>

    <script>
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
    </script>
</body>
</html>