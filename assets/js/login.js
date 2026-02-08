/**
 * Sistema de Login - JavaScript
 * Manejo seguro de autenticación
 */

(function() {
    'use strict';

    // Referencias a elementos del DOM
    const loginForm = document.getElementById('loginForm');
    const usuarioInput = document.getElementById('usuario');
    const passwordInput = document.getElementById('password');
    const btnLogin = document.getElementById('btnLogin');
    const btnText = btnLogin.querySelector('.btn-text');
    const btnLoader = btnLogin.querySelector('.btn-loader');
    const errorMessage = document.getElementById('errorMessage');
    const togglePassword = document.getElementById('togglePassword');

    // Obtener CSRF token al cargar la página
    let csrfToken = '';
    
    async function getCsrfToken() {
        try {
            const response = await fetch('controllers/csrf_token.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                csrfToken = data.csrf_token;
            }
        } catch (error) {
            console.error('Error al obtener CSRF token');
        }
    }

    // Inicializar CSRF token
    getCsrfToken();

    // Toggle mostrar/ocultar contraseña
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Cambiar ícono
        const eyeIcon = this.querySelector('.eye-icon');
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            `;
        } else {
            eyeIcon.innerHTML = `
                <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            `;
        }
    });

    // Limpiar mensaje de error al escribir
    usuarioInput.addEventListener('input', hideError);
    passwordInput.addEventListener('input', hideError);

    // Manejo del formulario
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validar campos
        const usuario = usuarioInput.value.trim();
        const password = passwordInput.value;

        if (!validateForm(usuario, password)) {
            return;
        }

        // Deshabilitar botón y mostrar loader
        setLoadingState(true);

        try {
            // Realizar petición de login
            const response = await fetch('controllers/login_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    usuario: usuario,
                    password: password,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Login exitoso
                showSuccess('Acceso concedido. Redirigiendo...');
                
                // Redirigir a la página principal (todos los roles van a main.php)
                setTimeout(() => {
                    window.location.href = 'main.php';
                }, 1000);

            } else {
                // Login fallido
                showError(data.message || 'Error al iniciar sesión');
                setLoadingState(false);
                
                // Limpiar contraseña
                passwordInput.value = '';
                passwordInput.focus();
                
                // Renovar CSRF token después de error
                getCsrfToken();
            }

        } catch (error) {
            console.error('Error:', error);
            showError('Error de conexión. Por favor, intente nuevamente.');
            setLoadingState(false);
            getCsrfToken();
        }
    });

    /**
     * Valida el formulario
     */
    function validateForm(usuario, password) {
        if (usuario.length < 3 || usuario.length > 50) {
            showError('El usuario debe tener entre 3 y 50 caracteres');
            usuarioInput.focus();
            return false;
        }

        if (password.length < 6 || password.length > 100) {
            showError('La contraseña debe tener entre 6 y 100 caracteres');
            passwordInput.focus();
            return false;
        }

        // Validar caracteres especiales peligrosos
        const dangerousChars = /[<>'"]/;
        if (dangerousChars.test(usuario)) {
            showError('El usuario contiene caracteres no permitidos');
            usuarioInput.focus();
            return false;
        }

        return true;
    }

    /**
     * Muestra mensaje de error
     */
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }

    /**
     * Muestra mensaje de éxito
     */
    function showSuccess(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        errorMessage.style.background = 'rgba(16, 185, 129, 0.1)';
        errorMessage.style.borderColor = 'rgba(16, 185, 129, 0.3)';
        errorMessage.style.color = '#10b981';
    }

    /**
     * Oculta mensaje de error
     */
    function hideError() {
        errorMessage.style.display = 'none';
        errorMessage.style.background = 'rgba(239, 68, 68, 0.1)';
        errorMessage.style.borderColor = 'rgba(239, 68, 68, 0.3)';
        errorMessage.style.color = '#ef4444';
    }

    /**
     * Establece estado de carga
     */
    function setLoadingState(loading) {
        if (loading) {
            btnLogin.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
        } else {
            btnLogin.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
        }
    }

    // Prevenir ataques de timing
    window.addEventListener('beforeunload', function() {
        // Limpiar datos sensibles
        usuarioInput.value = '';
        passwordInput.value = '';
        csrfToken = '';
    });

    // Prevenir copiar/pegar en campo de contraseña (opcional)
    passwordInput.addEventListener('paste', function(e) {
        // Permitir paste - comentar si se desea deshabilitar
        // e.preventDefault();
    });

})();