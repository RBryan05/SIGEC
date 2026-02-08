<?php
/**
 * Controlador para verificar sesión activa
 * Verifica si el usuario sigue autenticado
 */

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';

header('Content-Type: application/json');

// Solo aceptar peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['active' => false]);
    exit;
}

$auth = new Auth();
$isActive = $auth->isAuthenticated();

echo json_encode(['active' => $isActive]);