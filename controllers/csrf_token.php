<?php
/**
 * Generador de CSRF Token
 * Proporciona tokens de seguridad para formularios
 */

session_start();

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Solo aceptar peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Generar o recuperar token CSRF
$csrf_token = generate_csrf_token();

echo json_encode([
    'csrf_token' => $csrf_token
]);