<?php
/**
 * Generador de Hash de Contraseñas
 * Utilidad para crear contraseñas seguras para usuarios
 */

// Función para generar hash de contraseña
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Contraseñas de prueba
$passwords = [
    'Admin123!' => 'admin',
    'Doctor123!' => 'doctor1', 
    'Enfermero123!' => 'enfermero1'
];

echo "=== GENERADOR DE CONTRASEÑAS ===\n\n";

foreach ($passwords as $plaintext => $usuario) {
    $hash = generatePasswordHash($plaintext);
    echo "Usuario: $usuario\n";
    echo "Contraseña: $plaintext\n";
    echo "Hash: $hash\n";
    echo "\nINSERT: \n";
    echo "INSERT INTO usuarios (usuario, password_hash, rol_id, activo) VALUES\n";
    echo "('$usuario', '$hash', X, 1);\n";
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "\nPara generar una contraseña personalizada, modifica el array \$passwords\n";
echo "y ejecuta este script desde la terminal: php generar_password.php\n";