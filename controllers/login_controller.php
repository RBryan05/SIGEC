<?php
/**
 * Controlador de Login
 * Procesa las peticiones de autenticación
 */

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';

header('Content-Type: application/json');

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar Content-Type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Content-Type inválido']);
    exit;
}

// Leer datos JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

// Validar CSRF token
if (!isset($data['csrf_token']) || !verify_csrf_token($data['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
    exit;
}

// Validar datos requeridos
if (empty($data['usuario']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
    exit;
}

// Sanitizar entrada
$usuario = sanitize_input($data['usuario']);
$password = $data['password']; // No sanitizar password

// Validar longitud
if (strlen($usuario) < 3 || strlen($usuario) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Usuario inválido']);
    exit;
}

if (strlen($password) < 6 || strlen($password) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Contraseña inválida']);
    exit;
}

// Intentar login
$auth = new Auth();
$result = $auth->login($usuario, $password);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(401);
    echo json_encode($result);
}