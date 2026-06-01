/**
 * ============================================================
 * ARCHIVO: assets/js/app.js
 * ============================================================
 * DESCRIPCIÓN: Funciones JavaScript comunes del sistema
 * Utility functions reutilizables en toda la aplicación
 * ============================================================
 */

/**
 * ──────────────────────────────────────────────────────────
 * UTILIDADES DE STRING
 * ──────────────────────────────────────────────────────────
 */

/**
 * Sanitiza una cadena removiendo caracteres especiales
 * Previene XSS (Cross-Site Scripting)
 * 
 * @param {string} str - Cadena a sanitizar
 * @returns {string} Cadena sanitizada
 */
function sanitizeString(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Capitaliza la primera letra de una cadena
 * 
 * @param {string} str - Cadena
 * @returns {string} Cadena capitalizada
 */
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * ──────────────────────────────────────────────────────────
 * UTILIDADES DE FORMATO
 * ──────────────────────────────────────────────────────────
 */

/**
 * Formatea un número como moneda (S/.)
 * 
 * @param {number} num - Número a formatear
 * @returns {string} Número formateado (ej: "S/.29.90")
 */
function formatPrice(num) {
    return 'S/.' + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Formatea una fecha
 * 
 * @param {string|Date} date - Fecha
 * @param {string} format - Formato (ej: 'DD/MM/YYYY')
 * @returns {string} Fecha formateada
 */
function formatDate(date, format = 'DD/MM/YYYY') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    
    return format
        .replace('DD', day)
        .replace('MM', month)
        .replace('YYYY', year);
}

/**
 * ──────────────────────────────────────────────────────────
 * UTILIDADES DE DOM
 * ──────────────────────────────────────────────────────────
 */

/**
 * Obtiene un elemento del DOM
 * 
 * @param {string} selector - Selector CSS
 * @returns {Element|null} Elemento o null
 */
function query(selector) {
    return document.querySelector(selector);
}

/**
 * Obtiene todos los elementos que coincidan
 * 
 * @param {string} selector - Selector CSS
 * @returns {NodeList} Lista de elementos
 */
function queryAll(selector) {
    return document.querySelectorAll(selector);
}

/**
 * Crea un elemento HTML
 * 
 * @param {string} tag - Etiqueta HTML
 * @param {Object} attrs - Atributos
 * @param {string} text - Texto contenido
 * @returns {Element} Elemento creado
 */
function createElement(tag, attrs = {}, text = '') {
    const el = document.createElement(tag);
    
    for (let key in attrs) {
        if (attrs.hasOwnProperty(key)) {
            el.setAttribute(key, attrs[key]);
        }
    }
    
    if (text) el.textContent = text;
    
    return el;
}

/**
 * Agrega una clase a un elemento
 * 
 * @param {Element} el - Elemento
 * @param {string} className - Nombre de clase
 */
function addClass(el, className) {
    el && el.classList.add(className);
}

/**
 * Remueve una clase de un elemento
 * 
 * @param {Element} el - Elemento
 * @param {string} className - Nombre de clase
 */
function removeClass(el, className) {
    el && el.classList.remove(className);
}

/**
 * Toggle clase en un elemento
 * 
 * @param {Element} el - Elemento
 * @param {string} className - Nombre de clase
 */
function toggleClass(el, className) {
    el && el.classList.toggle(className);
}

/**
 * Verifica si un elemento tiene una clase
 * 
 * @param {Element} el - Elemento
 * @param {string} className - Nombre de clase
 * @returns {boolean} true si tiene la clase
 */
function hasClass(el, className) {
    return el && el.classList.contains(className);
}

/**
 * ──────────────────────────────────────────────────────────
 * NOTIFICACIONES/TOASTS
 * ──────────────────────────────────────────────────────────
 */

/**
 * Muestra una notificación toast
 * 
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duración en ms (0 = sin auto-cerrar)
 */
function showToast(message, type = 'info', duration = 3000) {
    // Crear elemento toast
    const toast = createElement('div', {
        class: `toast-msg toast-${type}`
    });
    
    // Icono según tipo
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    // HTML del toast
    toast.innerHTML = `
        <i class="${icons[type] || icons.info}"></i>
        <span>${sanitizeString(message)}</span>
    `;
    
    // Agregar al DOM
    document.body.appendChild(toast);
    
    // Auto-cerrar después de duration
    if (duration > 0) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    return toast;
}

/**
 * ──────────────────────────────────────────────────────────
 * VALIDACIÓN DE FORMULARIOS
 * ──────────────────────────────────────────────────────────
 */

/**
 * Valida un email
 * 
 * @param {string} email - Email a validar
 * @returns {boolean} true si es válido
 */
function validateEmail(email) {
    const regex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
    return regex.test(email);
}

/**
 * Valida una contraseña (mínimo 6 caracteres)
 * 
 * @param {string} password - Contraseña a validar
 * @returns {boolean} true si es válida
 */
function validatePassword(password) {
    return password && password.length >= 6;
}

/**
 * Valida un teléfono
 * 
 * @param {string} phone - Teléfono a validar
 * @returns {boolean} true si es válido
 */
function validatePhone(phone) {
    const regex = /^[0-9+\\-\\s\\(\\)]+$/;
    return regex.test(phone) && phone.replace(/\\D/g, '').length >= 7;
}

/**
 * ──────────────────────────────────────────────────────────
 * STORAGE (LocalStorage)
 * ──────────────────────────────────────────────────────────
 */

/**
 * Guarda datos en localStorage
 * 
 * @param {string} key - Clave
 * @param {*} value - Valor (se convierte a JSON)
 */
function setStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

/**
 * Obtiene datos de localStorage
 * 
 * @param {string} key - Clave
 * @returns {*} Valor o null
 */
function getStorage(key) {
    const value = localStorage.getItem(key);
    try {
        return value ? JSON.parse(value) : null;
    } catch (e) {
        return value;
    }
}

/**
 * Elimina datos de localStorage
 * 
 * @param {string} key - Clave
 */
function removeStorage(key) {
    localStorage.removeItem(key);
}

/**
 * ──────────────────────────────────────────────────────────
 * PETICIONES HTTP (AJAX)
 * ──────────────────────────────────────────────────────────
 */

/**
 * Realiza una petición GET
 * 
 * @param {string} url - URL
 * @returns {Promise} Promise con respuesta
 */
async function fetchGet(url) {
    try {
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error en GET:', error);
        throw error;
    }
}

/**
 * Realiza una petición POST
 * 
 * @param {string} url - URL
 * @param {Object} data - Datos a enviar
 * @returns {Promise} Promise con respuesta
 */
async function fetchPost(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        return await response.json();
    } catch (error) {
        console.error('Error en POST:', error);
        throw error;
    }
}

/**
 * ──────────────────────────────────────────────────────────
 * UTILIDADES VARIAS
 * ──────────────────────────────────────────────────────────
 */

/**
 * Espera X milisegundos
 * 
 * @param {number} ms - Milisegundos
 * @returns {Promise}
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Genera un ID único
 * 
 * @returns {string} ID único
 */
function generateId() {
    return '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * Copia texto al portapapeles
 * 
 * @param {string} text - Texto a copiar
 * @returns {Promise}
 */
function copyToClipboard(text) {
    return navigator.clipboard.writeText(text);
}

/**
 * Obtiene parámetro de URL
 * 
 * @param {string} paramName - Nombre del parámetro
 * @returns {string|null} Valor o null
 */
function getUrlParam(paramName) {
    const params = new URLSearchParams(window.location.search);
    return params.get(paramName);
}
