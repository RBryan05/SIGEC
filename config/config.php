<?php
/**
 * Configuración General del Sistema
 * Sistema de Gestión de Expedientes Médicos
 */

// Constante de acceso
define('ACCESS_ALLOWED', true);

// Configurar sesión ANTES de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 al HTTPS en producción
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// Configuración de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Zona horaria
date_default_timezone_set('America/El_Salvador');

// Configuración de seguridad
define('SESSION_LIFETIME', 3600); // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutos

// Incluir base de datos
require_once __DIR__ . '/database.php';

// Función para sanitizar entrada
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para generar token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}