<?php
/**
 * Controlador de Logout
 * Cierra la sesión del usuario
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

$auth = new Auth();
$auth->logout();

echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);