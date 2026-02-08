<?php
/**
 * Clase de Autenticación
 * Maneja login, logout y validación de usuarios
 */

defined('ACCESS_ALLOWED') or die('Acceso denegado');

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Intenta autenticar un usuario
     */
    public function login($usuario, $password) {
        // Verificar intentos de login
        if ($this->isAccountLocked($usuario)) {
            return [
                'success' => false,
                'message' => 'Cuenta bloqueada temporalmente por múltiples intentos fallidos'
            ];
        }
        
        try {
            // Preparar consulta (protección contra SQL injection)
            $stmt = $this->db->prepare("
                SELECT u.id, u.usuario, u.password_hash, u.rol_id, u.activo, r.nombre as rol
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.usuario = :usuario
                LIMIT 1
            ");
            
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch();
            
            // Verificar si el usuario existe
            if (!$user) {
                $this->registerFailedAttempt($usuario);
                return [
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos'
                ];
            }
            
            // Verificar si el usuario está activo
            if (!$user['activo']) {
                return [
                    'success' => false,
                    'message' => 'Usuario desactivado. Contacte al administrador'
                ];
            }
            
            // Verificar contraseña
            if (!password_verify($password, $user['password_hash'])) {
                $this->registerFailedAttempt($usuario);
                return [
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos'
                ];
            }
            
            // Login exitoso - limpiar intentos fallidos
            $this->clearFailedAttempts($usuario);
            
            // Regenerar ID de sesión para prevenir session fixation
            session_regenerate_id(true);
            
            // Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['rol_id'] = $user['rol_id'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            // Registrar en auditoría
            $this->logAudit($user['id'], 'LOGIN_EXITOSO', 'usuarios');
            
            return [
                'success' => true,
                'message' => 'Login exitoso',
                'rol' => $user['rol']
            ];
            
        } catch(PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en el sistema. Intente nuevamente'
            ];
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAudit($_SESSION['user_id'], 'LOGOUT', 'usuarios');
        }
        
        // Destruir sesión
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Verifica si el usuario está autenticado
     */
    public function isAuthenticated() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Verificar timeout de sesión
        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        
        // Actualizar última actividad
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Verifica si la cuenta está bloqueada
     */
    private function isAccountLocked($usuario) {
        if (!isset($_SESSION['login_attempts'][$usuario])) {
            return false;
        }
        
        $attempts = $_SESSION['login_attempts'][$usuario];
        
        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            $time_diff = time() - $attempts['last_attempt'];
            
            if ($time_diff < LOCKOUT_TIME) {
                return true;
            } else {
                // Restablecer intentos después del período de bloqueo
                unset($_SESSION['login_attempts'][$usuario]);
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Registra un intento fallido de login
     */
    private function registerFailedAttempt($usuario) {
        if (!isset($_SESSION['login_attempts'][$usuario])) {
            $_SESSION['login_attempts'][$usuario] = [
                'count' => 0,
                'last_attempt' => time()
            ];
        }
        
        $_SESSION['login_attempts'][$usuario]['count']++;
        $_SESSION['login_attempts'][$usuario]['last_attempt'] = time();
        
        // Registrar en auditoría
        $this->logAudit(null, 'LOGIN_FALLIDO: ' . $usuario, 'usuarios');
    }
    
    /**
     * Limpia los intentos fallidos
     */
    private function clearFailedAttempts($usuario) {
        if (isset($_SESSION['login_attempts'][$usuario])) {
            unset($_SESSION['login_attempts'][$usuario]);
        }
    }
    
    /**
     * Registra acciones en auditoría
     */
    private function logAudit($usuario_id, $accion, $tabla) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO auditoria (usuario_id, accion, tabla_afectada)
                VALUES (:usuario_id, :accion, :tabla)
            ");
            
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'accion' => $accion,
                'tabla' => $tabla
            ]);
        } catch(PDOException $e) {
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene información del usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'usuario' => $_SESSION['usuario'],
            'rol' => $_SESSION['rol'],
            'rol_id' => $_SESSION['rol_id']
        ];
    }
}