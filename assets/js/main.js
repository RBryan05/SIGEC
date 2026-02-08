/**
 * Sistema SIGEC - JavaScript Principal
 * Funcionalidad de la página main
 */

(function() {
    'use strict';

    /**
     * Cerrar sesión
     */
    window.logout = async function() {
        if (!confirm('¿Estás seguro que deseas cerrar sesión?')) {
            return;
        }

        try {
            const response = await fetch('controllers/logout_controller.php', {
                method: 'POST',
                credentials: 'same-origin'
            });

            if (response.ok) {
                window.location.href = 'index.php';
            } else {
                alert('Error al cerrar sesión');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    };

    /**
     * Navegación
     */
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover clase active de todos
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            
            // Agregar clase active al clickeado
            this.classList.add('active');
            
            // Aquí irá la lógica para cargar contenido dinámico
            const section = this.getAttribute('href').substring(1);
            console.log('Navegando a:', section);
            
            // Por ahora solo mostramos un mensaje
            alert('Funcionalidad "' + section + '" en desarrollo');
        });
    });

    /**
     * Verificar sesión activa cada 5 minutos
     */
    setInterval(async function() {
        try {
            const response = await fetch('controllers/check_session.php', {
                method: 'GET',
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (!data.active) {
                alert('Tu sesión ha expirado. Serás redirigido al login.');
                window.location.href = 'index.php';
            }
        } catch (error) {
            console.error('Error verificando sesión:', error);
        }
    }, 300000); // 5 minutos

})();