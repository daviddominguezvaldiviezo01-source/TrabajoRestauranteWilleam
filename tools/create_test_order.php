<?php
include(__DIR__ . '/../conexion.php');

// Config
$email_delivery = 'delivery@restaurante.test';
$product_id = 16; // producto de prueba (ajusta si es necesario)
$cantidad = 1;
$metodo_pago = 'efectivo';

// Obtener id_repartidor
$su = mysqli_prepare($conexion, "SELECT id_usuario FROM usuarios WHERE email=? LIMIT 1");
mysqli_stmt_bind_param($su,'s',$email_delivery); mysqli_stmt_execute($su);
$resu = mysqli_stmt_get_result($su);
if (!$row = mysqli_fetch_assoc($resu)) { echo "ERROR: delivery user not found\n"; exit(1); }
$id_repartidor = $row['id_usuario'];

// Obtener precio del producto
$sp = mysqli_prepare($conexion, "SELECT precio, nombre FROM productos WHERE id_producto = ? LIMIT 1");
mysqli_stmt_bind_param($sp,'i',$product_id); mysqli_stmt_execute($sp);
$rp = mysqli_stmt_get_result($sp);
if (!$prod = mysqli_fetch_assoc($rp)) { echo "ERROR: product not found\n"; exit(1); }
$precio = floatval($prod['precio']);
$nombre = $prod['nombre'];
$subtotal = $precio * $cantidad;
$impuesto = $subtotal * 0.18;
$delivery_fee = 5;
$total = $subtotal + $impuesto + $delivery_fee;

// Insertar pedido
$estado = 'ir a recoger';
$stmt = mysqli_prepare($conexion, "INSERT INTO pedidos (id_usuario, id_direccion, estado, total, id_repartidor) VALUES (NULL, NULL, ?, ? , ?)");
mysqli_stmt_bind_param($stmt,'sdi',$estado,$total,$id_repartidor);
if (!mysqli_stmt_execute($stmt)) { echo "ERROR: could not insert pedido: " . mysqli_error($conexion) . "\n"; exit(1); }
$id_pedido = mysqli_insert_id($conexion);

// Insertar detalle_pedido
$sdet = mysqli_prepare($conexion, "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) VALUES (?,?,?,?)");
mysqli_stmt_bind_param($sdet,'iiid',$id_pedido,$product_id,$cantidad,$subtotal);
mysqli_stmt_execute($sdet);

// Insertar pago
$estado_pago = 'pagado';
$spago = mysqli_prepare($conexion, "INSERT INTO pagos (id_pedido, metodo, estado) VALUES (?,?,?)");
mysqli_stmt_bind_param($spago,'iss',$id_pedido,$metodo_pago,$estado_pago);
mysqli_stmt_execute($spago);

echo "CREATED_ORDER:" . $id_pedido . " (product: $nombre)\n";
?>