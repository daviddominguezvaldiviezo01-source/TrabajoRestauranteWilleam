<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Términos y Condiciones - Brisamar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#111; color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; }
.navbar-top { background:#c8102e; padding:0 40px; height:64px; display:flex; align-items:center; position:sticky; top:0; z-index:200; box-shadow:0 2px 12px rgba(0,0,0,.5); }
.navbar-inner { max-width:1300px; width:100%; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
.logo { font-size:22px; font-weight:900; color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; }
.btn-nav-back { color:#fff; text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:7px; opacity:.85; transition:.2s; }
.btn-nav-back:hover { opacity:1; color:#fff; }
.page-wrap { max-width:1000px; margin:40px auto; padding:0 20px 60px; }
.page-title { font-size:2rem; font-weight:900; margin-bottom:20px; display:flex; align-items:center; gap:12px; }
.content-box { background:#1a1a1a; border:1px solid #2a2a2a; border-radius:18px; padding:32px; }
.content-box h2 { color:#fff; margin-top:24px; font-size:1.3rem; }
.content-box h3 { color:#ffdd99; margin-top:22px; font-size:1.1rem; }
.content-box p, .content-box li { color:rgba(255,255,255,.8); line-height:1.8; margin-bottom:12px; }
.content-box ol { margin-top:12px; padding-left:22px; }
.content-box ol li { margin-bottom:12px; }
.content-box a { color:#c8102e; text-decoration:none; }
.content-box a:hover { text-decoration:underline; }
</style>
</head>
<body>
<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire" style="color:#ffcc00"></i> Brisamar</a>
        <a href="index.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al menú</a>
    </div>
</nav>

<div class="page-wrap">
    <div class="page-title"><i class="fas fa-file-contract" style="color:#c8102e"></i> Términos y Condiciones</div>
    <div class="content-box">
        <p><strong>Última actualización:</strong> 18 de mayo de 2026</p>
        <p>Bienvenido a Brisamar. Los presentes Términos y Condiciones regulan el acceso y uso de nuestro sitio web y la realización de pedidos a través del mismo. Al navegar, registrarse o realizar un pedido, usted acepta de manera libre, voluntaria e informada estos Términos en su totalidad. Si no está de acuerdo, le rogamos que no utilice el sitio.</p>
        <p>Estos Términos se rigen por la legislación peruana, en especial por la Ley N° 29571 – Código de Protección y Defensa del Consumidor y las disposiciones de INDECOPI, así como por la Ley N° 29733 – Ley de Protección de Datos Personales.</p>

        <h2>1 Información del proveedor</h2>
        <p>Razón social: Brisamar Restaurante</p>
        <p>Dirección: Calle Principal 123, Lima, Perú</p>
        <p>Correo: info@brisamar.com</p>
        <p>Teléfono: +51 922 453 069</p>

        <h2>2 Definiciones</h2>
        <ol>
            <li><strong>Cliente o Usuario:</strong> persona natural mayor de 18 años o persona jurídica que accede al sitio y/o realiza un pedido.</li>
            <li><strong>Producto:</strong> platos, bebidas y demás artículos del menú comercializados a través del sitio.</li>
            <li><strong>Pedido:</strong> solicitud de compra realizada por el Usuario a través del proceso de checkout.</li>
            <li><strong>Cuenta:</strong> perfil creado por el Usuario para gestionar sus pedidos e información personal.</li>
        </ol>

        <h2>3 Uso del sitio y creación de cuenta</h2>
        <p>Para realizar un pedido, el Usuario deberá registrarse o proporcionar sus datos en el checkout. El Usuario se compromete a proporcionar información veraz, exacta y completa, y a mantenerla actualizada.</p>
        <p>Brisamar se reserva el derecho de cancelar cuentas, rechazar pedidos o restringir el acceso ante cualquier uso fraudulento, ilícito o contrario a estos Términos. El Usuario es responsable de mantener la confidencialidad de su contraseña y de toda actividad que ocurra bajo su cuenta.</p>

        <h2>4 Productos, precios e impuestos</h2>
        <p>Todos los precios publicados están expresados en soles peruanos (S/) e incluyen el IGV, salvo indicación en contrario. Brisamar se reserva el derecho de modificar los precios en cualquier momento, sin que ello afecte los pedidos ya confirmados.</p>
        <p>Las imágenes de los productos tienen carácter ilustrativo. Pueden existir ligeras variaciones en la presentación final del plato.</p>

        <h2>5 Proceso de pedido</h2>
        <ol>
            <li>El Usuario selecciona los productos y los añade al carrito.</li>
            <li>En el checkout, revisa su pedido, proporciona los datos de entrega y selecciona el método de pago.</li>
            <li>Al confirmar el pedido, el Usuario acepta estos Términos y emite una oferta de compra vinculante.</li>
            <li>Brisamar enviará una confirmación del pedido. El contrato se perfecciona cuando el pedido es aceptado y puesto en preparación.</li>
            <li>Brisamar podrá cancelar un pedido por error en el precio, falta de disponibilidad, sospecha de fraude u otra causa justificada, notificando al Usuario y reembolsando el importe pagado en un máximo de 15 días calendario.</li>
        </ol>

        <h2>6 Métodos de pago</h2>
        <p>Aceptamos los siguientes métodos de pago:</p>
        <ol>
            <li>Tarjeta de crédito y débito (Visa, Mastercard).</li>
            <li>Transferencia bancaria / Pago por QR (Yape, Plin).</li>
            <li>Pago contra entrega (solo en zonas habilitadas).</li>
        </ol>
        <p>El pago se procesa a través de canales seguros. Brisamar no almacena los datos completos de tarjetas de crédito o débito.</p>

        <h2>7 Política de pago obligatorio previo a la preparación</h2>
        <p>Todo pedido será procesado y puesto en preparación únicamente tras haberse confirmado el pago en su totalidad. Ningún pedido iniciará su proceso de elaboración, preparación o despacho mientras el pago no haya sido verificado de forma satisfactoria por Brisamar.</p>
        <p>Los pedidos pendientes de pago no generan ningún tipo de reserva, compromiso de disponibilidad ni inicio de preparación por parte del establecimiento. La confirmación del pago es un requisito indispensable para dar inicio al proceso de atención del pedido.</p>
        <p>Al realizar su pedido, el cliente acepta que el pago previo es condición esencial para que Brisamar comience su preparación.</p>

        <h2>8 Envíos y plazos de entrega</h2>
        <p>Realizamos entregas a domicilio dentro de las zonas habilitadas. Los tiempos estimados son:</p>
        <ol>
            <li>Zona local: 30 a 60 minutos según demanda y distancia.</li>
            <li>Zonas alejadas: hasta 90 minutos.</li>
        </ol>
        <p>Estos plazos son estimados y pueden variar por tráfico, clima u otras causas ajenas a Brisamar. El costo de envío se calcula en el checkout según la dirección de entrega.</p>
        <p>El Usuario es responsable de proporcionar una dirección correcta y completa. Brisamar no se hace responsable por entregas fallidas por datos erróneos del Usuario.</p>

        <h2>9 Derecho de retracto, cancelaciones y reembolsos</h2>
        <p>Dado que los productos son alimentos preparados, el derecho de retracto y la posibilidad de cancelación aplican únicamente antes de que el pedido entre en preparación. Una vez iniciada la preparación, no se aceptarán cancelaciones ni devoluciones del importe pagado, salvo error imputable a Brisamar (producto incorrecto, en mal estado, etc.).</p>
        <p>Las solicitudes de devolución o reembolso deberán realizarse con suficiente antelación, antes de que el pedido haya sido confirmado en preparación, a través de nuestros canales de atención, indicando el número de pedido y el motivo. El reembolso, de proceder, se efectuará por el mismo medio de pago utilizado originalmente en un plazo máximo de 15 días calendario.</p>
        <p><strong>Importante:</strong> No se aceptarán solicitudes de devolución una vez que el pedido haya iniciado su preparación. Le recomendamos verificar su pedido antes de efectuar el pago.</p>
        <p>Para solicitar una cancelación o reportar un problema, contáctenos de inmediato a través de nuestro correo o teléfono indicando el número de pedido.</p>

        <h2>10 Garantía y devoluciones por defectos</h2>
        <p>Si el producto recibido presenta defectos (producto incorrecto, en mal estado o incompleto), el Usuario debe comunicarlo dentro de los 30 minutos siguientes a la recepción, adjuntando fotografías del problema.</p>
        <p>Verificado el defecto, Brisamar ofrecerá, a su elección: reenvío del producto correcto, descuento en el siguiente pedido o devolución del importe pagado. En estos casos, Brisamar cubrirá todos los costos asociados.</p>

        <h2>11 Propiedad intelectual</h2>
        <p>Todos los contenidos del sitio (textos, imágenes, logotipos, diseños, fotografías y código fuente) son propiedad de Brisamar o de terceros que han autorizado su uso, y están protegidos por la legislación peruana sobre propiedad intelectual (D. Leg. N° 822).</p>
        <p>Queda prohibida la reproducción, distribución o explotación de los contenidos sin autorización previa y por escrito de Brisamar.</p>

        <h2>12 Protección de datos personales</h2>
        <p>El tratamiento de los datos personales del Usuario cumple con la Ley N° 29733 – Ley de Protección de Datos Personales. Los datos recopilados se utilizan exclusivamente para gestionar pedidos, mejorar el servicio y comunicaciones relacionadas con el mismo. No se comparten con terceros sin consentimiento del Usuario, salvo obligación legal.</p>

        <h2>13 Limitación de responsabilidad</h2>
        <p>Brisamar no será responsable por:</p>
        <ol>
            <li>Retrasos en la entrega imputables a causas de fuerza mayor (tráfico, clima, etc.).</li>
            <li>Interrupciones temporales del sitio por mantenimiento o fallos técnicos.</li>
            <li>Errores tipográficos o de precio manifiestos, que serán corregidos tan pronto sean detectados.</li>
            <li>Daños derivados del uso indebido de los productos adquiridos.</li>
        </ol>
        <p>Nada en esta cláusula limita los derechos que la ley peruana reconoce de manera irrenunciable al consumidor.</p>

        <h2>14 Modificaciones a los Términos</h2>
        <p>Brisamar se reserva el derecho de modificar estos Términos en cualquier momento. Las modificaciones entrarán en vigor desde su publicación en el sitio. Para pedidos ya confirmados, se aplicarán los Términos vigentes al momento de la compra.</p>

        <h2>15 Legislación aplicable y resolución de conflictos</h2>
        <p>Estos Términos se rigen por las leyes de la República del Perú. Cualquier controversia será resuelta, en primera instancia, mediante comunicación directa entre el Usuario y Brisamar.</p>
        <p>Si no se alcanza una solución satisfactoria, el Usuario tiene derecho a presentar su reclamo ante INDECOPI o recurrir a los juzgados del distrito judicial de Lima.</p>

        <h2>16 Libro de Reclamaciones</h2>
        <p>De conformidad con el Código de Protección y Defensa del Consumidor y el D.S. N° 006-2014-PCM, Brisamar pone a disposición un Libro de Reclamaciones virtual. Para registrar una queja o reclamo, contáctenos a través del correo indicado abajo. Nos comprometemos a responder en un máximo de 15 días hábiles.</p>

        <h2>17 Contacto</h2>
        <p>info@brisamar.com</p>
        <p>+51 922 453 069</p>
        <p>Calle Principal 123, Lima</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
