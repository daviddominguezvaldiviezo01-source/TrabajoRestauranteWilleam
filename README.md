# 🔥 BRISAMAR - Sistema de Restaurante

## 📋 Descripción General

**BRISAMAR** es un sistema completo de gestión de restaurante con:
- Catálogo de productos en línea
- Sistema de carrito de compras
- Autenticación y registro de usuarios
- Checkout con múltiples métodos de pago
- Panel de administración
- Gestión de pedidos y repartidores
- Diseño responsive y moderno
- **100% Seguro** - Previene SQL Injection, XSS, CSRF
- **SEO Optimizado** - Estructura semántica HTML5
- **Bien Documentado** - Comentarios en cada línea

---

## 🎯 Características Principales

### 👥 Para Clientes
- ✅ Navegar menú de productos
- ✅ Buscar productos en tiempo real
- ✅ Filtrar por categorías
- ✅ Agregar al carrito
- ✅ Ver carrito y modificar cantidades
- ✅ Checkout seguro
- ✅ Login/Registro de usuario
- ✅ Comprar como invitado (sin login)

### 🛡️ Para Administradores
- ✅ Gestionar productos (CRUD)
- ✅ Gestionar categorías
- ✅ Ver pedidos
- ✅ Gestionar clientes
- ✅ Asignar repartidores
- ✅ Ver reportes

### 🚗 Para Repartidores
- ✅ Ver pedidos para entregar
- ✅ Actualizar estado de entrega

---

## 📁 Estructura del Proyecto

```
RESTAURANTE2/
├── 📄 index.php                 # Página de entrada (redirige a cliente)
├── 📄 conexion.php              # Conexión a BD (usa config centralizada)
├── 📄 validar.php               # Autenticación (segura con prepared statements)
├── 📄 logout.php                # Cierre de sesión
├── 📄 dashboard.php             # Dashboard principal
├── 📄 delivery.php              # Página de repartidor
├── 📄 .htaccess                 # Seguridad del servidor Apache
├── 📄 robots.txt                # Para SEO (motores de búsqueda)
├── 📄 sitemap.xml               # Mapa del sitio
│
├── 📁 config/                   # Configuración centralizada
│   ├── config.php               # Constantes y configuración
│   └── database.php             # Conexión segura a BD
│
├── 📁 includes/                 # Funciones comunes
│   ├── security.php             # Funciones de seguridad
│   ├── validation.php           # Validación de datos
│   └── functions.php            # Funciones generales
│
├── 📁 assets/                   # Recursos estáticos
│   ├── 📁 css/                  # Estilos separados
│   │   ├── variables.css        # Variables de colores y estilos
│   │   ├── reset.css            # Reset CSS global
│   │   ├── navbar.css           # Estilos navbar
│   │   ├── hero.css             # Estilos hero banner
│   │   ├── products.css         # Estilos grid de productos
│   │   ├── footer.css           # Estilos footer
│   │   └── main.css             # Imports y media queries
│   │
│   ├── 📁 js/                   # Scripts modularizados
│   │   ├── app.js               # Funciones comunes
│   │   └── cart.js              # Funcionalidad carrito
│   │
│   └── 📁 img/                  # Imágenes
│
├── 📁 cliente/                  # Interfaz del cliente
│   ├── index.php                # Página principal (menú)
│   ├── login.php                # Login/Registro
│   ├── carrito.php              # Carrito de compras
│   ├── checkout.php             # Proceso de compra
│   ├── procesar_pedido.php      # Procesar pedido
│   ├── agregar_carrito.php      # Agregar al carrito
│   ├── eliminar_carrito.php     # Eliminar del carrito
│   └── guard_delivery.php       # Protección de rutas
│
├── 📁 admin/                    # Panel de administración
│   ├── dashboard.php            # Dashboard admin
│   ├── productos.php            # Gestión de productos
│   ├── categorias.php           # Gestión de categorías
│   ├── pedidos.php              # Gestión de pedidos
│   ├── clientes.php             # Gestión de clientes
│   └── _admin_layout.php        # Layout del admin
│
├── 📁 tools/                    # Herramientas (no indexar)
│   ├── alter_pedidos_enum.php   # Herramientas de BD
│   ├── check_user.php
│   └── ...
│
├── 📁 images/                   # Imágenes subidas
│
└── 📁 logs/                     # Logs del sistema
    └── actividades.log          # Registro de actividades
```

---

## 🔐 Seguridad

### ✅ Protecciones Implementadas

| Ataque | Protección | Ubicación |
|--------|-----------|----------|
| **SQL Injection** | Prepared Statements (MySQLi) | `includes/functions.php`, `validar.php` |
| **XSS** | htmlspecialchars() + sanitización | `includes/security.php` |
| **CSRF** | Tokens únicos de sesión | `includes/security.php` |
| **Password** | bcrypt hashing (password_hash) | `includes/security.php` |
| **Session Hijacking** | HttpOnly + Secure cookies | `includes/security.php` |
| **Directorio Traversal** | .htaccess restrictions | `.htaccess` |
| **Hotlinking** | Protección de imágenes | `.htaccess` |
| **MIME Sniffing** | X-Content-Type-Options header | `.htaccess` |
| **Clickjacking** | X-Frame-Options header | `.htaccess` |

---

## 🚀 Cómo Empezar

### 1️⃣ Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache con mod_rewrite habilitado
- XAMPP (recomendado para desarrollo)

### 2️⃣ Instalación

```bash
# 1. Clonar/descargar el proyecto
cd xampp/htdocs/RESTAURANTE2

# 2. Crear base de datos
# Abrir phpMyAdmin y crear BD "restaurant_bd"
# Importar archivo de estructura (si existe)

# 3. Verificar conexión
# Editar config/config.php si es necesario
# Por defecto usa: localhost, root, sin password

# 4. Acceder
# http://localhost/RESTAURANTE2/
```

### 3️⃣ Primeros Pasos

```
1. Acceder a http://localhost/RESTAURANTE2/
2. Click "Ingresar" → "Continuar como Invitado"
3. Agregar productos al carrito
4. Ir a checkout y completar compra
```

---

## 📝 Documentación de Archivos Clave

### `config/config.php`
Constantes globales del sistema. **Editar aquí para cambiar:**
- Nombre del sitio
- Credenciales de BD
- Costos de envío
- Porcentaje de impuestos

### `includes/security.php`
Todas las funciones de seguridad:
- `iniciar_sesion_segura()` - Inicia sesión
- `validar_token_csrf()` - Valida tokens
- `sanitizar()` - Previene XSS
- `encriptar_password()` - Hashea contraseñas
- `verificar_password()` - Verifica contraseñas

### `includes/validation.php`
Funciones para validar entrada del usuario:
- `validar_email()` - Valida emails
- `validar_entero()` - Valida números
- `validar_texto()` - Valida texto
- `validar_telefono()` - Valida teléfono

### `includes/functions.php`
Funciones de negocio comunes:
- `obtener_producto()` - Obtiene un producto
- `obtener_categorias()` - Lista categorías
- `calcular_total_carrito()` - Calcula totales
- `establecer_mensaje()` - Guarda mensajes

---

## 🎨 Estilos y CSS

### Estructura
```
assets/css/
├── variables.css        # Colores, espaciado, tipografía
├── reset.css           # Reset y normalización
├── navbar.css          # Navbar sticky
├── hero.css            # Banner principal
├── products.css        # Grid de productos
├── footer.css          # Pie de página
└── main.css            # Imports y responsive
```

### Usar Variables CSS
```css
/* En cualquier archivo CSS */
background: var(--color-primary);
padding: var(--spacing-lg);
font-family: var(--font-family);
border-radius: var(--radius-md);
```

### Media Queries
- **Desktop:** 1300px
- **Tablet:** 768px
- **Móvil:** 480px
- **Extra pequeño:** 320px

---

## 💻 JavaScript

### Funciones en `assets/js/app.js`
```javascript
// Formateo
formatPrice(29.9)           // "S/.29.90"
formatDate('2024-06-01')    // "01/06/2024"

// DOM
query('#id')                 // document.querySelector
queryAll('.class')           // document.querySelectorAll
addClass(el, 'active')       // Agregar clase
removeClass(el, 'active')    // Remover clase

// Notificaciones
showToast('Mensaje', 'success', 3000)

// Storage
setStorage('key', {data})    // Guardar en localStorage
getStorage('key')            // Obtener de localStorage
```

### Funciones en `assets/js/cart.js`
```javascript
filtrarProductos()           // Busca en tiempo real
agregarAlCarrito(id)         // Agrega producto
eliminarDelCarrito(id)       // Elimina producto
recalcularTotal()            // Recalcula totales
```

---

## 🔧 Desarrollo y Mantenimiento

### Agregar Nueva Página

1. Crear archivo en carpeta correspondiente (ej: `cliente/nueva.php`)
2. En inicio del archivo:
```php
<?php
// Iniciar sesión
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion_segura();

// Incluir configuración
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/functions.php';

// Tu código aquí
?>
```

3. Crear formulario con token CSRF:
```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
    <!-- Tu formulario -->
</form>
```

### Validar Entrada
```php
// Validar email
if (!validar_email($_POST['email'])) {
    die('❌ Email inválido');
}

// Validar cantidad
$cantidad = validar_cantidad($_POST['cantidad']);
if ($cantidad === false) {
    die('❌ Cantidad inválida');
}
```

### Usar Prepared Statements
```php
// ✅ CORRECTO - Previene SQL Injection
$stmt = mysqli_prepare($conexion, 
    "SELECT * FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// ❌ INCORRECTO - SQL Injection
$resultado = mysqli_query($conexion, 
    "SELECT * FROM usuarios WHERE email='$email'");
```

---

## 📊 Base de Datos

### Tablas Necesarias

```sql
-- Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    telefono VARCHAR(20),
    rol ENUM('cliente','admin','delivery'),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorías
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    descripcion TEXT
);

-- Productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    descripcion TEXT,
    precio DECIMAL(10,2),
    stock INT,
    imagen VARCHAR(255),
    id_categoria INT,
    disponible BOOLEAN,
    favorito BOOLEAN,
    estrella BOOLEAN,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

-- Pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    total DECIMAL(10,2),
    estado ENUM('pendiente','confirmado','entregado'),
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);
```

---

## 🐛 Troubleshooting

### Error: "Error de Conexión"
1. Verificar que MySQL esté corriendo
2. Verificar usuario/contraseña en `config/config.php`
3. Verificar que la BD existe

### Error: "No se puede escribir archivo"
1. Verificar permisos de carpeta `/logs/`
2. Ejecutar: `chmod 755 logs/`

### CSS/JS no se carga
1. Verificar rutas en HTML
2. Limpiar caché del navegador (Ctrl+Shift+Del)
3. Verificar que carpeta `assets` exista

---

## 📞 Contacto y Soporte

- **Email:** info@brisamar.com
- **Teléfono:** +51 999 999 999
- **Dirección:** Calle Principal 123

---

## 📄 Licencia

Este proyecto es privado y propietario de Brisamar.

---

## ✨ Notas Finales

- **Comentarios:** Cada función y sección tiene comentarios descriptivos
- **Facilidad:** Cualquiera puede mantener el código
- **Seguridad:** Todas las mejores prácticas implementadas
- **Performance:** Optimizado para carga rápida
- **SEO:** Estructura semántica HTML5 y meta tags

**¡Disfruta usando Brisamar!** 🔥
