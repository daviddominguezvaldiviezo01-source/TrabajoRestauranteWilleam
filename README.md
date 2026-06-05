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
- Protección básica contra SQL Injection, XSS y CSRF
- SEO optimizado con `robots.txt` y `sitemap.xml`

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
- ✅ Comprar como invitado

### 🛡️ Para Administradores
- ✅ Gestionar productos (CRUD)
- ✅ Gestionar categorías
- ✅ Ver pedidos
- ✅ Gestionar clientes
- ✅ Asignar repartidores
- ✅ Ajustar configuración de la aplicación

### 🚗 Para Repartidores
- ✅ Ver pedidos asignados
- ✅ Actualizar estado de entrega

---

## 📁 Estructura del Proyecto

```
RESTAURANTE2/
├── .git/                        # Control de versiones local
├── .htaccess                    # Reglas de seguridad de Apache
├── David001.ogg                 # Archivo multimedia adicional
├── INICIO_RAPIDO.md             # Guía rápida de inicio
├── README.md                    # Documentación del proyecto
├── conexion.php                 # Conexión principal a BD
├── dashboard.php                # Dashboard general
├── delivery.php                 # Página para repartidores
├── index.php                    # Página principal / entrada
├── logout.php                   # Cierre de sesión
├── robots.txt                   # Directivas para motores de búsqueda
├── sitemap.xml                  # Mapa del sitio
├── validar.php                  # Lógica de autenticación
│
├── config/
│   ├── config.php               # Configuración global
│   └── database.php             # Conexión a la base de datos
│
├── includes/
│   ├── functions.php            # Funciones de negocio comunes
│   ├── security.php             # Funciones de seguridad
│   └── validation.php           # Validaciones de entrada
│
├── assets/
│   ├── css/
│   │   ├── footer.css
│   │   ├── hero.css
│   │   ├── index.css
│   │   ├── main.css
│   │   ├── navbar.css
│   │   ├── products.css
│   │   ├── reset.css
│   │   └── variables.css
│   ├── js/
│   │   ├── app.js
│   │   └── cart.js
│   └── images/
│       ├── pago_QR.png
│       └── restaurante.jpg
│
├── cliente/
│   ├── agregar_carrito.php      # Añadir producto al carrito
│   ├── carrito.php              # Vista del carrito
│   ├── checkout.php             # Proceso de compra
│   ├── eliminar_carrito.php     # Eliminar ítem del carrito
│   ├── guard_delivery.php       # Protección de rutas
│   ├── index.php                # Menú de cliente
│   ├── login.php                # Login y registro
│   ├── procesar_pedido.php      # Generar pedidos
│   └── partials/
│       └── product-card.php     # Plantilla de producto
│
├── admin/
│   ├── _admin_layout.php        # Layout administrador
│   ├── anuncios.php             # Gestión de anuncios
│   ├── categorias.php           # Gestión de categorías
│   ├── clientes.php             # Gestión de clientes
│   ├── configuracion.php        # Ajustes del sistema
│   ├── dashboard.php            # Dashboard admin
│   ├── pedidos.php              # Gestión de pedidos
│   └── productos.php            # Gestión de productos
│
├── tools/
│   ├── add_guest_fields_to_pedidos.php
│   ├── alter_pedidos_enum.php
│   ├── alter_roles.php
│   ├── check_user.php
│   ├── create_test_order.php
│   ├── inspect_order.php
│   ├── query_test_order.php
│   ├── set_delivery_password.php
│   ├── set_delivery_role.php
│   ├── show_columns.php
│   └── show_pedidos_columns.php
│
├── images/
│   ├── .htaccess
│   ├── anuncios/
│   ├── hero/
│   └── pedidos/
│
└── logs/
    └── actividades.log          # Registro de actividades
```

---

## 🔐 Seguridad

### ✅ Protecciones Implementadas

| Ataque | Protección | Ubicación |
|--------|-----------|-----------|
| SQL Injection | Prepared Statements | `includes/functions.php`, `validar.php` |
| XSS | `htmlspecialchars()` + sanitización | `includes/security.php` |
| CSRF | Tokens de sesión | `includes/security.php` |
| Passwords | `password_hash()` / `password_verify()` | `includes/security.php` |
| Session Hijacking | Cookies seguras y HttpOnly | `includes/security.php` |
| Directorio Traversal | Reglas en `.htaccess` | `.htaccess` |
| MIME Sniffing | `X-Content-Type-Options` | `.htaccess` |
| Clickjacking | `X-Frame-Options` | `.htaccess` |

---

## 🚀 Cómo Empezar

### 1️⃣ Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache con `mod_rewrite`
- XAMPP recomendado para desarrollo

### 2️⃣ Instalación

```bash
cd xampp/htdocs/RESTAURANTE2
```

- Crear la base de datos en phpMyAdmin
- Editar `config/config.php` si es necesario
- Verificar credenciales en `config/database.php`

### 3️⃣ Uso

1. Abre `http://localhost/RESTAURANTE2/`
2. Agrega productos al carrito
3. Finaliza el checkout
4. Entra al panel admin para gestionar productos, clientes y pedidos

---

## 📝 Archivos Importantes

### `config/config.php`
Define variables globales del sitio y configuración general.

### `config/database.php`
Establece la conexión a la base de datos.

### `includes/security.php`
Contiene funciones para sesiones, CSRF, sanitización y hashing.

### `includes/validation.php`
Valida entradas del usuario como correo, texto y números.

### `includes/functions.php`
Funciones de negocio como carga de productos, cálculo de totales y mensajes.
