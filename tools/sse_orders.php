<?php
// SSE endpoint que emite nuevos pedidos en estado 'ir a recoger'
require_once __DIR__ . '/../conexion.php';

// Evitar timeout y buffers
set_time_limit(0);
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level() > 0) ob_end_flush();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$lastSnapshot = null;

function send_event($name, $data) {
    echo "event: {$name}\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    if (ob_get_level() > 0) ob_flush();
    flush();
}

// Loop largo: comprobar cada 3 segundos
while (!connection_aborted()) {
    // obtener conteos por estado
    $counts = [];
    $states = ['ir a recoger','en camino','entregado','cancelado'];
    foreach ($states as $s) {
        $r = mysqli_query($conexion, "SELECT COUNT(*) AS cnt FROM pedidos WHERE estado = '".mysqli_real_escape_string($conexion,$s)."'");
        $c = 0; if ($r && $row = mysqli_fetch_assoc($r)) $c = intval($row['cnt']);
        $counts[$s] = $c;
    }

    // obtener últimos 10 pedidos (cualquier estado)
    $out = [];
    $q = "SELECT id_pedido, total, estado, COALESCE(nombre_cliente,'') AS nombre_cliente, COALESCE(email_cliente,'') AS email_cliente FROM pedidos ORDER BY id_pedido DESC LIMIT 10";
    $r2 = mysqli_query($conexion, $q);
    while ($row2 = mysqli_fetch_assoc($r2)) {
        $out[] = [
            'id_pedido' => intval($row2['id_pedido']),
            'total' => floatval($row2['total']),
            'estado' => $row2['estado'],
            'cliente' => $row2['nombre_cliente'],
            'email' => $row2['email_cliente']
        ];
    }

    $snapshot = ['counts' => $counts, 'recent' => $out];
    if ($snapshot !== $lastSnapshot) {
        send_event('orders', $snapshot);
        $lastSnapshot = $snapshot;
    }

    // Esperar 1s
    sleep(1);
}

?>
