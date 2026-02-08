<?php
/**
 * Página Principal del Sistema
 * Muestra diferentes funcionalidades según el rol del usuario
 */

session_start();

require_once 'config/config.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// Verificar autenticación
if (!$auth->isAuthenticated()) {
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEC - Sistema de Gestión de Expedientes Clínicos</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 11H15M12 8V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>SIGEC</span>
            </div>
            
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user['usuario']); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($user['rol']); ?></span>
                <button onclick="logout()" class="btn-logout">Cerrar Sesión</button>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li><a href="#dashboard" class="nav-link active">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/></svg>
                    Dashboard
                </a></li>
                
                <!-- Funcionalidad común para todos -->
                <li><a href="#pacientes" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" stroke="currentColor" stroke-width="2"/></svg>
                    Pacientes
                </a></li>
                
                <li><a href="#expedientes" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2"/></svg>
                    Expedientes
                </a></li>
                
                <?php if ($user['rol'] === 'ADMIN' || $user['rol'] === 'DOCTOR'): ?>
                <!-- Solo Admin y Doctor pueden recetar -->
                <li><a href="#recetas" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" stroke="currentColor" stroke-width="2"/></svg>
                    Recetas
                </a></li>
                <?php endif; ?>
                
                <li><a href="#examenes" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2"/></svg>
                    Exámenes
                </a></li>
                
                <?php if ($user['rol'] === 'ADMIN'): ?>
                <!-- Solo Admin tiene acceso a gestión de usuarios -->
                <li class="nav-divider"></li>
                <li><a href="#usuarios" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z" stroke="currentColor" stroke-width="2"/></svg>
                    Gestión de Usuarios
                </a></li>
                
                <li><a href="#auditoria" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/></svg>
                    Auditoría
                </a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="content-wrapper">
            <h1>Bienvenid@, <?php echo htmlspecialchars($user['usuario']); ?></h1>
            <p class="subtitle">Rol: <strong><?php echo htmlspecialchars($user['rol']); ?></strong></p>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon">👥</div>
                    <div class="card-content">
                        <h3>Pacientes</h3>
                        <p class="card-number">0</p>
                        <p class="card-label">Registrados</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon">📋</div>
                    <div class="card-content">
                        <h3>Expedientes</h3>
                        <p class="card-number">0</p>
                        <p class="card-label">Activos</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon">🧪</div>
                    <div class="card-content">
                        <h3>Exámenes</h3>
                        <p class="card-number">0</p>
                        <p class="card-label">Pendientes</p>
                    </div>
                </div>
                
                <?php if ($user['rol'] === 'ADMIN'): ?>
                <div class="card">
                    <div class="card-icon">👨‍⚕️</div>
                    <div class="card-content">
                        <h3>Usuarios</h3>
                        <p class="card-number">3</p>
                        <p class="card-label">En el sistema</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Información según el rol -->
            <div class="info-section">
                <?php if ($user['rol'] === 'ADMIN'): ?>
                    <div class="alert alert-info">
                        <strong>Permisos de Administrador/a:</strong> Tienes acceso completo al sistema, incluyendo gestión de usuarios y auditoría.
                    </div>
                <?php elseif ($user['rol'] === 'DOCTOR'): ?>
                    <div class="alert alert-info">
                        <strong>Permisos de Doctor/a:</strong> Puedes gestionar pacientes, expedientes, crear recetas y solicitar exámenes.
                    </div>
                <?php elseif ($user['rol'] === 'ENFERMERO'): ?>
                    <div class="alert alert-info">
                        <strong>Permisos de Enfermer@:</strong> Puedes consultar pacientes, expedientes y registrar exámenes.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>