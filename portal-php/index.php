<?php
// ARA & Bustamante Consultores - Portal Principal
// Archivo principal que sirve la interfaz del cliente

// Configuración básica
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Detectar si es una petición AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Si es una petición AJAX, redirigir a la API correspondiente
if ($isAjax) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARA & Bustamante Consultores - Portal de Clientes</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏢</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos CSS embebidos -->
    <style>
    /* ARA & Bustamante Consultores - Estilos Principales */

    :root {
        /* Paleta de colores elegantes */
        --primary-color: #1a365d;          /* Azul marino profundo */
        --primary-hover: #2d4a68;         /* Azul marino medio */
        --secondary-color: #d4af37;        /* Dorado elegante */
        --secondary-hover: #b8941f;       /* Dorado más oscuro */
        --accent-color: #2c5282;          /* Azul brillante */
        --success-color: #38a169;         /* Verde esmeralda */
        --warning-color: #d69e2e;         /* Ámbar */
        --danger-color: #e53e3e;          /* Rojo coral */

        /* Grises sofisticados */
        --bg-primary: #f8fafc;           /* Gris muy claro */
        --bg-secondary: #edf2f7;         /* Gris claro */
        --bg-card: #ffffff;              /* Blanco */
        --text-primary: #2d3748;         /* Gris oscuro */
        --text-secondary: #4a5568;       /* Gris medio */
        --text-muted: #718096;           /* Gris claro */
        --border-color: #e2e8f0;         /* Borde suave */
        --border-focus: #d4af37;         /* Borde dorado */

        /* Sombras elegantes */
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

        /* Fuentes */
        --font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --font-size-base: 1rem;
        --font-size-lg: 1.125rem;
        --font-size-xl: 1.25rem;
        --font-size-2xl: 1.5rem;
        --font-size-3xl: 1.875rem;
        --font-size-4xl: 2.25rem;

        /* Transiciones */
        --transition-fast: 0.15s ease-in-out;
        --transition-normal: 0.3s ease-in-out;
        --transition-slow: 0.5s ease-in-out;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-family);
        font-size: var(--font-size-base);
        line-height: 1.6;
        color: var(--text-primary);
        background-color: var(--bg-primary);
        overflow-x: hidden;
    }

    /* Layout Principal */
    #app {
        min-height: 100vh;
        position: relative;
    }

    .screen {
        display: none;
        min-height: 100vh;
    }

    .screen.active {
        display: block;
    }

    /* === PANTALLA DE LOGIN === */
    .login-background {
        display: flex;
        min-height: 100vh;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    }

    .login-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .login-form {
        background: var(--bg-card);
        padding: 3rem;
        border-radius: 20px;
        box-shadow: var(--shadow-xl);
        width: 100%;
        max-width: 450px;
        position: relative;
        backdrop-filter: blur(20px);
    }

    .company-logo {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .logo-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--secondary-color), var(--secondary-hover));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: var(--shadow-lg);
    }

    .logo-icon i {
        font-size: 2.5rem;
        color: white;
    }

    .company-logo h1 {
        font-size: var(--font-size-3xl);
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }

    .company-logo p {
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        font-weight: 400;
    }

    .form-section h2 {
        font-size: var(--font-size-2xl);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2rem;
        text-align: center;
    }

    /* Sidebar de login */
    .login-sidebar {
        flex: 0 0 400px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        padding: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-content h3 {
        color: white;
        font-size: var(--font-size-2xl);
        font-weight: 600;
        margin-bottom: 2rem;
        text-align: center;
    }

    .feature-list {
        list-style: none;
    }

    .feature-list li {
        color: rgba(255, 255, 255, 0.9);
        font-size: var(--font-size-lg);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .feature-list li i {
        color: var(--secondary-color);
        font-size: 1.2rem;
    }

    /* === PANTALLA DE REGISTRO === */
    .register-background {
        min-height: 100vh;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .register-container {
        width: 100%;
        max-width: 700px;
    }

    .register-form {
        background: var(--bg-card);
        padding: 3rem;
        border-radius: 20px;
        box-shadow: var(--shadow-xl);
    }

    .form-header {
        text-align: center;
        margin-bottom: 3rem;
        position: relative;
    }

    .btn-back {
        position: absolute;
        left: 0;
        top: 0;
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: var(--font-size-xl);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: var(--transition-fast);
    }

    .btn-back:hover {
        background-color: var(--bg-secondary);
        color: var(--primary-color);
    }

    .form-header h2 {
        font-size: var(--font-size-3xl);
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .form-header p {
        color: var(--text-secondary);
        font-size: var(--font-size-base);
    }

    /* === FORMULARIOS === */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group label {
        display: block;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: var(--font-size-sm);
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrapper i {
        position: absolute;
        left: 1rem;
        color: var(--text-muted);
        font-size: var(--font-size-sm);
        z-index: 1;
    }

    .input-wrapper input {
        padding-left: 3rem;
    }

    input, textarea, select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        font-size: var(--font-size-base);
        font-family: var(--font-family);
        transition: var(--transition-fast);
        background-color: var(--bg-card);
        color: var(--text-primary);
    }

    input:focus, textarea:focus, select:focus {
        outline: none;
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
    }

    input::placeholder, textarea::placeholder {
        color: var(--text-muted);
    }

    /* === BOTONES === */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        font-size: var(--font-size-base);
        font-weight: 500;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition-fast);
        text-decoration: none;
        font-family: var(--font-family);
        min-height: 48px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        color: white;
        box-shadow: var(--shadow-md);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-secondary {
        background: linear-gradient(135deg, var(--secondary-color), var(--secondary-hover));
        color: white;
        box-shadow: var(--shadow-md);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }

    .btn-outline:hover {
        background-color: var(--bg-secondary);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    /* Enlaces */
    .link-secondary {
        color: var(--secondary-color);
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: center;
        padding: 1rem;
        transition: var(--transition-fast);
    }

    .link-secondary:hover {
        color: var(--secondary-hover);
    }

    .login-options {
        margin-top: 2rem;
        text-align: center;
    }

    /* === NAVBAR === */
    .navbar {
        background: var(--bg-card);
        box-shadow: var(--shadow-sm);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 100;
        backdrop-filter: blur(20px);
    }

    .nav-brand .brand-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: var(--font-size-xl);
        font-weight: 700;
        color: var(--primary-color);
    }

    .nav-brand i {
        font-size: 2rem;
        color: var(--secondary-color);
    }

    .nav-center {
        flex: 1;
        display: flex;
        justify-content: center;
    }

    .nav-tabs {
        display: flex;
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 0.25rem;
        gap: 0.25rem;
    }

    .nav-tab {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: transparent;
        border: none;
        border-radius: 8px;
        color: var(--text-secondary);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition-fast);
    }

    .nav-tab.active {
        background: var(--bg-card);
        color: var(--primary-color);
        box-shadow: var(--shadow-sm);
    }

    .nav-tab:hover:not(.active) {
        color: var(--text-primary);
    }

    .nav-user {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-info {
        text-align: right;
    }

    .user-info span {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
    }

    .user-role {
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* === DASHBOARD === */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .dashboard-container {
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 2rem;
    }

    .header-left h2 {
        font-size: var(--font-size-3xl);
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-left p {
        color: var(--text-secondary);
        font-size: var(--font-size-lg);
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    /* === CATEGORÍAS === */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .category-card {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: var(--shadow-md);
        transition: var(--transition-normal);
        cursor: pointer;
        border: 2px solid transparent;
    }

    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--secondary-color);
    }

    .category-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--secondary-color), var(--secondary-hover));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .category-icon i {
        font-size: 1.75rem;
        color: white;
    }

    .category-card h3 {
        font-size: var(--font-size-xl);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .category-card p {
        color: var(--text-secondary);
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .category-stats {
        display: flex;
        gap: 1rem;
        font-size: var(--font-size-sm);
        color: var(--text-muted);
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* === MODALES === */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1000;
        animation: fadeIn 0.3s ease;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: var(--bg-card);
        border-radius: 20px;
        box-shadow: var(--shadow-xl);
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
    }

    .modal-content.large {
        max-width: 900px;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        padding: 2rem 2rem 1rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        font-size: var(--font-size-xl);
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: var(--transition-fast);
    }

    .modal-close:hover {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        padding: 1rem 2rem 2rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    /* === ÁREA DE SUBIDA === */
    .upload-area {
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        padding: 3rem 2rem;
        text-align: center;
        margin-bottom: 2rem;
        transition: var(--transition-fast);
        cursor: pointer;
    }

    .upload-area:hover, .upload-area.drag-over {
        border-color: var(--secondary-color);
        background-color: rgba(212, 175, 55, 0.05);
    }

    .upload-content i {
        font-size: 3rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .upload-content p {
        color: var(--text-secondary);
        font-size: var(--font-size-lg);
    }

    #fileInput {
        display: none;
    }

    /* === LISTA DE DOCUMENTOS === */
    .documents-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .document-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        margin-bottom: 0.75rem;
        transition: var(--transition-fast);
    }

    .document-item:hover {
        background-color: var(--bg-secondary);
        border-color: var(--secondary-color);
    }

    .document-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .document-icon i {
        color: white;
        font-size: 1.1rem;
    }

    .document-info {
        flex: 1;
        min-width: 0;
    }

    .document-name {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .document-meta {
        font-size: var(--font-size-sm);
        color: var(--text-muted);
        display: flex;
        gap: 1rem;
    }

    .document-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-fast);
    }

    .btn-icon:hover {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
    }

    .btn-icon.danger:hover {
        background-color: rgba(229, 62, 62, 0.1);
        color: var(--danger-color);
    }

    /* === PERFIL === */
    .profile-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-header h2 {
        font-size: var(--font-size-3xl);
        font-weight: 700;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .profile-card {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: var(--shadow-md);
    }

    .profile-info {
        display: grid;
        gap: 1.5rem;
    }

    .info-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .info-group:last-child {
        border-bottom: none;
    }

    .info-group label {
        font-weight: 600;
        color: var(--text-secondary);
    }

    .info-group span {
        font-weight: 500;
        color: var(--text-primary);
    }

    /* === LOADING === */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-spinner {
        text-align: center;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--bg-secondary);
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-spinner p {
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* === NOTIFICACIONES === */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--bg-card);
        color: var(--text-primary);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        border-left: 4px solid var(--success-color);
        display: none;
        align-items: center;
        gap: 1rem;
        z-index: 10000;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .notification.show {
        display: flex;
    }

    .notification.success {
        border-left-color: var(--success-color);
    }

    .notification.error {
        border-left-color: var(--danger-color);
    }

    .notification.warning {
        border-left-color: var(--warning-color);
    }

    .notification-content {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notification-icon {
        font-size: 1.25rem;
    }

    .notification.success .notification-icon::before {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: var(--success-color);
    }

    .notification.error .notification-icon::before {
        content: '\f00d';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: var(--danger-color);
    }

    .notification.warning .notification-icon::before {
        content: '\f071';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: var(--warning-color);
    }

    .notification-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: var(--transition-fast);
    }

    .notification-close:hover {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
    }

    /* === RESPONSIVE === */
    @media (max-width: 768px) {
        .login-background {
            flex-direction: column;
        }

        .login-sidebar {
            flex: none;
            padding: 2rem;
        }

        .sidebar-content {
            text-align: center;
        }

        .feature-list li {
            font-size: var(--font-size-base);
        }

        .navbar {
            padding: 1rem;
            flex-direction: column;
            gap: 1rem;
        }

        .nav-center {
            order: -1;
        }

        .nav-tabs {
            width: 100%;
        }

        .dashboard-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .header-actions {
            justify-content: center;
        }

        .categories-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .modal-content {
            margin: 1rem;
            max-width: none;
        }

        .document-meta {
            flex-direction: column;
            gap: 0.25rem;
        }
    }

    @media (max-width: 480px) {
        .login-form, .register-form {
            padding: 2rem 1.5rem;
        }

        .dashboard-container {
            padding: 1rem;
        }

        .modal-header, .modal-body, .modal-footer {
            padding: 1.5rem 1rem;
        }

        .notification {
            left: 10px;
            right: 10px;
            min-width: auto;
        }
    }

    /* Admin Link */
    .admin-link {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: var(--danger-color);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        box-shadow: var(--shadow-lg);
        transition: var(--transition-fast);
        z-index: 1000;
    }

    .admin-link:hover {
        background: #c53030;
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
    }
    </style>
</head>
<body>
    <div id="app">
        <!-- Login Screen -->
        <div id="loginScreen" class="screen active">
            <div class="login-background">
                <div class="login-container">
                    <div class="login-form">
                        <div class="company-logo">
                            <div class="logo-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h1>ARA & BUSTAMANTE</h1>
                            <p>Consultores Contables y Tributarios</p>
                        </div>

                        <div class="form-section">
                            <h2>Iniciar Sesión</h2>
                            <form id="loginForm">
                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fas fa-user"></i>
                                        <input type="text" id="username" name="username" placeholder="Usuario" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" id="password" name="password" placeholder="Contraseña" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Acceder al Portal
                                </button>
                            </form>

                            <div class="login-options">
                                <a href="#" id="showRegister" class="link-secondary">
                                    <i class="fas fa-building"></i>
                                    Registrar Nueva Empresa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="login-sidebar">
                    <div class="sidebar-content">
                        <h3>Bienvenido a nuestro Portal</h3>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Gestión completa de documentos</li>
                            <li><i class="fas fa-check"></i> Acceso 24/7 a sus archivos</li>
                            <li><i class="fas fa-check"></i> Comunicación directa con consultores</li>
                            <li><i class="fas fa-check"></i> Seguridad y confidencialidad garantizada</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Screen -->
        <div id="registerScreen" class="screen">
            <div class="register-background">
                <div class="register-container">
                    <div class="register-form">
                        <div class="form-header">
                            <button id="backToLogin" class="btn-back">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <h2>Registro de Nueva Empresa</h2>
                            <p>Complete los datos de su empresa para acceder al portal</p>
                        </div>

                        <form id="registerForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="razonSocial">Razón Social *</label>
                                    <input type="text" id="razonSocial" name="razon_social" required>
                                </div>
                                <div class="form-group">
                                    <label for="rutEmpresa">RUT Empresa *</label>
                                    <input type="text" id="rutEmpresa" name="rut_empresa" placeholder="12345678-9" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="regUsername">Usuario *</label>
                                    <input type="text" id="regUsername" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Corporativo *</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="regPassword">Contraseña *</label>
                                <input type="password" id="regPassword" name="password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-building"></i>
                                Registrar Empresa
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Screen -->
        <div id="dashboardScreen" class="screen">
            <nav class="navbar">
                <div class="nav-brand">
                    <div class="brand-logo">
                        <i class="fas fa-calculator"></i>
                        <span>ARA & BUSTAMANTE</span>
                    </div>
                </div>
                <div class="nav-center">
                    <div class="nav-tabs">
                        <button class="nav-tab active" data-tab="documents">
                            <i class="fas fa-folder-open"></i>
                            Documentos
                        </button>
                        <button class="nav-tab" data-tab="profile">
                            <i class="fas fa-user-circle"></i>
                            Mi Perfil
                        </button>
                    </div>
                </div>
                <div class="nav-user">
                    <div class="user-info">
                        <span id="userInfo"></span>
                        <div class="user-role" id="userRole"></div>
                    </div>
                    <button id="logoutBtn" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </button>
                </div>
            </nav>

            <!-- Documents Tab -->
            <div id="documentsTab" class="tab-content active">
                <div class="dashboard-container">
                    <div class="dashboard-header">
                        <div class="header-left">
                            <h2><i class="fas fa-folder-open"></i> Gestión de Documentos</h2>
                            <p>Organice y acceda a todos sus documentos contables</p>
                        </div>
                        <div class="header-actions">
                            <button id="createCategoryBtn" class="btn btn-secondary">
                                <i class="fas fa-plus"></i>
                                Nueva Categoría
                            </button>
                            <button id="refreshBtn" class="btn btn-outline">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>

                    <div class="categories-container">
                        <div id="categoriesGrid" class="categories-grid">
                            <!-- Las categorías se cargarán aquí dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profileTab" class="tab-content">
                <div class="dashboard-container">
                    <div class="profile-container">
                        <div class="profile-header">
                            <h2><i class="fas fa-user-circle"></i> Información de la Empresa</h2>
                        </div>
                        <div class="profile-content">
                            <div class="profile-card">
                                <div class="profile-info">
                                    <div class="info-group">
                                        <label>Razón Social:</label>
                                        <span id="profileRazonSocial">-</span>
                                    </div>
                                    <div class="info-group">
                                        <label>RUT:</label>
                                        <span id="profileRut">-</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Email:</label>
                                        <span id="profileEmail">-</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Usuario:</label>
                                        <span id="profileUsername">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <div id="categoryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-folder-plus"></i> Nueva Categoría de Documentos</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <div class="form-group">
                            <label for="categoryName">Nombre de la Categoría *</label>
                            <input type="text" id="categoryName" placeholder="Ej: Cartola Bancaria, Facturas, Boletas, etc." required>
                        </div>
                        <div class="form-group">
                            <label for="categoryDescription">Descripción</label>
                            <textarea id="categoryDescription" placeholder="Descripción opcional de la categoría" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-action="cancel">Cancelar</button>
                    <button type="submit" form="categoryForm" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Crear Categoría
                    </button>
                </div>
            </div>
        </div>

        <!-- Document Modal -->
        <div id="documentModal" class="modal">
            <div class="modal-content large">
                <div class="modal-header">
                    <h3><i class="fas fa-files-o"></i> <span id="modalCategoryName">Documentos</span></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="documents-header">
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Arrastra archivos aquí o haz clic para seleccionar</p>
                                <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                    <div class="documents-list" id="documentsList">
                        <!-- Los documentos se cargarán aquí -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Notification -->
        <div id="notification" class="notification">
            <div class="notification-content">
                <i class="notification-icon"></i>
                <span id="notificationText"></span>
            </div>
            <button id="closeNotification" class="notification-close">&times;</button>
        </div>
    </div>

    <!-- Admin Access Link -->
    <a href="admin/" class="admin-link">
        <i class="fas fa-shield-alt"></i>
        Panel Admin
    </a>

    <!-- JavaScript integrado -->
    <script>
    class PortalApp {
        constructor() {
            this.baseURL = window.location.origin + window.location.pathname.replace('index.php', '') + 'api';
            this.token = localStorage.getItem('token');
            this.userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
            this.categories = [];
            this.currentCategory = null;
            this.init();
        }

        init() {
            this.bindEvents();
            this.checkAuth();
        }

        bindEvents() {
            // Login form
            document.getElementById('loginForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.login();
            });

            // Register form
            document.getElementById('registerForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.register();
            });

            // Navigation
            document.getElementById('showRegister').addEventListener('click', (e) => {
                e.preventDefault();
                this.showScreen('registerScreen');
            });

            document.getElementById('backToLogin').addEventListener('click', (e) => {
                e.preventDefault();
                this.showScreen('loginScreen');
            });

            // Dashboard
            document.getElementById('logoutBtn').addEventListener('click', () => {
                this.logout();
            });

            document.getElementById('refreshBtn').addEventListener('click', () => {
                this.loadCategories();
            });

            // Create category
            document.getElementById('createCategoryBtn').addEventListener('click', () => {
                this.showCategoryModal();
            });

            // Category form
            document.getElementById('categoryForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.createCategory();
            });

            // Category modal
            document.getElementById('categoryModal').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close') || e.target.dataset.action === 'cancel') {
                    this.hideCategoryModal();
                }
            });

            // Document modal
            document.getElementById('documentModal').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close')) {
                    this.hideDocumentModal();
                }
            });

            // File upload
            document.getElementById('uploadArea').addEventListener('click', () => {
                document.getElementById('fileInput').click();
            });

            document.getElementById('fileInput').addEventListener('change', (e) => {
                this.handleFileSelection(e.target.files);
            });

            // Drag and drop
            const uploadArea = document.getElementById('uploadArea');
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('drag-over');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
                this.handleFileSelection(e.dataTransfer.files);
            });

            // Navigation tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    this.switchTab(tab.dataset.tab);
                });
            });

            // Close notification
            document.getElementById('closeNotification').addEventListener('click', () => {
                this.hideNotification();
            });
        }

        checkAuth() {
            if (this.token && this.userInfo.username) {
                this.showScreen('dashboardScreen');
                this.updateUserInfo();
                this.loadCategories();
                this.loadProfile();
            } else {
                this.showScreen('loginScreen');
            }
        }

        updateUserInfo() {
            document.getElementById('userInfo').textContent = this.userInfo.razon_social || this.userInfo.username;
            document.getElementById('userRole').textContent = this.userInfo.role === 'admin' ? 'Administrador' : 'Cliente';
        }

        switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tabName}Tab`).classList.add('active');

            // Load appropriate content
            if (tabName === 'documents') {
                this.loadCategories();
            } else if (tabName === 'profile') {
                this.loadProfile();
            }
        }

        showScreen(screenId) {
            document.querySelectorAll('.screen').forEach(screen => {
                screen.classList.remove('active');
            });
            document.getElementById(screenId).classList.add('active');
        }

        showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');

            notificationText.textContent = message;
            notification.className = `notification ${type} show`;

            setTimeout(() => {
                this.hideNotification();
            }, 5000);
        }

        hideNotification() {
            document.getElementById('notification').classList.remove('show');
        }

        showCategoryModal() {
            document.getElementById('categoryModal').classList.add('show');
            document.getElementById('categoryName').focus();
        }

        hideCategoryModal() {
            document.getElementById('categoryModal').classList.remove('show');
            document.getElementById('categoryForm').reset();
        }

        showDocumentModal(category) {
            this.currentCategory = category;
            document.getElementById('modalCategoryName').textContent = category.nombre;
            document.getElementById('documentModal').classList.add('show');
            this.loadDocumentsForCategory(category.id);
        }

        hideDocumentModal() {
            document.getElementById('documentModal').classList.remove('show');
            this.currentCategory = null;
            document.getElementById('documentsList').innerHTML = '';
        }

        async makeRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (this.token) {
                defaultOptions.headers.Authorization = `Bearer ${this.token}`;
            }

            const finalOptions = { ...defaultOptions, ...options };

            try {
                const response = await fetch(url, finalOptions);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error en la solicitud');
                }

                return data;
            } catch (error) {
                console.error('Request error:', error);
                throw error;
            }
        }

        async login() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!username || !password) {
                this.showNotification('Por favor completa todos los campos', 'error');
                return;
            }

            this.showLoading();

            try {
                const data = await this.makeRequest(`${this.baseURL}/auth.php?action=login`, {
                    method: 'POST',
                    body: JSON.stringify({ username, password })
                });

                this.token = data.token;
                this.userInfo = data.user;
                localStorage.setItem('token', this.token);
                localStorage.setItem('userInfo', JSON.stringify(this.userInfo));

                this.showNotification('Inicio de sesión exitoso');
                this.showScreen('dashboardScreen');
                this.updateUserInfo();
                this.loadCategories();

                // Clear form
                document.getElementById('loginForm').reset();
            } catch (error) {
                this.showNotification(error.message, 'error');
            } finally {
                this.hideLoading();
            }
        }

        async register() {
            const formData = new FormData(document.getElementById('registerForm'));
            const data = Object.fromEntries(formData);

            if (!data.username || !data.password || !data.razon_social || !data.rut_empresa) {
                this.showNotification('Por favor completa todos los campos', 'error');
                return;
            }

            this.showLoading();

            try {
                await this.makeRequest(`${this.baseURL}/auth.php?action=register`, {
                    method: 'POST',
                    body: JSON.stringify(data)
                });

                this.showNotification('Registro exitoso. Ahora puedes iniciar sesión.');
                this.showScreen('loginScreen');

                // Clear form
                document.getElementById('registerForm').reset();
            } catch (error) {
                this.showNotification(error.message, 'error');
            } finally {
                this.hideLoading();
            }
        }

        logout() {
            this.token = null;
            this.userInfo = {};
            localStorage.removeItem('token');
            localStorage.removeItem('userInfo');
            this.showScreen('loginScreen');
            this.showNotification('Sesión cerrada correctamente');
        }

        async loadCategories() {
            this.showLoading();

            try {
                const categories = await this.makeRequest(`${this.baseURL}/tipos-documento.php`);
                this.categories = categories;
                this.renderCategories(categories);
            } catch (error) {
                this.showNotification(error.message, 'error');
                // Show default categories if API fails
                this.renderCategories([]);
            } finally {
                this.hideLoading();
            }
        }

        renderCategories(categories) {
            const container = document.getElementById('categoriesGrid');

            if (categories.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <i class="fas fa-folder-plus" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--text-secondary);">No hay categorías disponibles</h3>
                        <p style="color: var(--text-muted);">Crea tu primera categoría para organizar tus documentos.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = categories.map(category => this.createCategoryCard(category)).join('');
        }

        createCategoryCard(category) {
            return `
                <div class="category-card" onclick="app.showDocumentModal(${JSON.stringify(category).replace(/"/g, '&quot;')})">
                    <div class="category-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>${category.nombre}</h3>
                    <p>${category.descripcion || 'Sin descripción'}</p>
                    <div class="category-stats">
                        <div class="stat-item">
                            <i class="fas fa-file"></i>
                            <span>${category.total_documentos || 0} documentos</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <span>Actualizado</span>
                        </div>
                    </div>
                </div>
            `;
        }

        async createCategory() {
            const nombre = document.getElementById('categoryName').value;
            const descripcion = document.getElementById('categoryDescription').value;

            if (!nombre.trim()) {
                this.showNotification('El nombre de la categoría es requerido', 'error');
                return;
            }

            this.showLoading();

            try {
                await this.makeRequest(`${this.baseURL}/tipos-documento.php`, {
                    method: 'POST',
                    body: JSON.stringify({
                        nombre: nombre.trim(),
                        descripcion: descripcion.trim() || null,
                        codigo: nombre.trim().toUpperCase().replace(/\s+/g, '_')
                    })
                });

                this.showNotification('Categoría creada exitosamente');
                this.hideCategoryModal();
                this.loadCategories();
            } catch (error) {
                this.showNotification(error.message, 'error');
            } finally {
                this.hideLoading();
            }
        }

        async loadDocumentsForCategory(categoryId) {
            try {
                const documents = await this.makeRequest(`${this.baseURL}/documentos.php?categoria_id=${categoryId}`);
                this.renderDocuments(documents);
            } catch (error) {
                this.showNotification(error.message, 'error');
                this.renderDocuments([]);
            }
        }

        renderDocuments(documents) {
            const container = document.getElementById('documentsList');

            if (documents.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        <i class="fas fa-file-plus" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No hay documentos en esta categoría.<br>Sube tu primer archivo usando el área de arriba.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = documents.map(doc => this.createDocumentItem(doc)).join('');
        }

        createDocumentItem(document) {
            const canDelete = document.subido_por_cliente && document.subido_por_cliente === this.userInfo.id;
            const fileIcon = this.getFileIcon(document.nombre_archivo);
            const fileSize = this.formatFileSize(document.tamano);

            return `
                <div class="document-item">
                    <div class="document-icon">
                        <i class="fas ${fileIcon}"></i>
                    </div>
                    <div class="document-info">
                        <div class="document-name">${document.nombre_archivo}</div>
                        <div class="document-meta">
                            <span><i class="fas fa-weight-hanging"></i> ${fileSize}</span>
                            <span><i class="fas fa-calendar"></i> ${this.formatDate(document.fecha_subida)}</span>
                            <span><i class="fas fa-user"></i> ${document.subido_por_cliente ? 'Cliente' : 'Consultora'}</span>
                        </div>
                    </div>
                    <div class="document-actions">
                        <button class="btn-icon" onclick="app.downloadDocument('${document.ruta_archivo}')" title="Descargar">
                            <i class="fas fa-download"></i>
                        </button>
                        ${canDelete ? `
                            <button class="btn-icon danger" onclick="app.deleteDocument(${document.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        getFileIcon(filename) {
            const extension = filename.split('.').pop().toLowerCase();
            const iconMap = {
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'xls': 'fa-file-excel',
                'xlsx': 'fa-file-excel',
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image',
                'txt': 'fa-file-alt'
            };
            return iconMap[extension] || 'fa-file';
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        handleFileSelection(files) {
            if (!this.currentCategory) {
                this.showNotification('Error: No hay categoría seleccionada', 'error');
                return;
            }

            if (files.length === 0) return;

            Array.from(files).forEach(file => {
                this.uploadFile(file);
            });
        }

        async uploadFile(file) {
            if (!file) {
                this.showNotification('Por favor selecciona un archivo', 'error');
                return;
            }

            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                this.showNotification('El archivo es demasiado grande. Máximo 10MB.', 'error');
                return;
            }

            const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                 'image/jpeg', 'image/png'];

            if (!allowedTypes.includes(file.type)) {
                this.showNotification('Tipo de archivo no permitido', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('archivo', file);
            formData.append('categoria_id', this.currentCategory.id);

            this.showLoading();

            try {
                const response = await fetch(`${this.baseURL}/documentos.php?action=subir`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error al subir archivo');
                }

                this.showNotification('Archivo subido exitosamente');
                this.loadDocumentsForCategory(this.currentCategory.id);
            } catch (error) {
                this.showNotification(error.message, 'error');
            } finally {
                this.hideLoading();
            }
        }

        async deleteDocument(documentId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este documento?')) {
                return;
            }

            this.showLoading();

            try {
                await this.makeRequest(`${this.baseURL}/documentos.php?action=eliminar&id=${documentId}`, {
                    method: 'DELETE'
                });

                this.showNotification('Documento eliminado exitosamente');
                this.loadDocumentsForCategory(this.currentCategory.id);
            } catch (error) {
                this.showNotification(error.message, 'error');
            } finally {
                this.hideLoading();
            }
        }

        downloadDocument(filePath) {
            const link = document.createElement('a');
            link.href = `${this.baseURL}/documentos.php?action=descargar&archivo=${encodeURIComponent(filePath)}`;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        async loadProfile() {
            if (!this.userInfo || !this.userInfo.id) return;

            document.getElementById('profileRazonSocial').textContent = this.userInfo.razon_social || '-';
            document.getElementById('profileRut').textContent = this.userInfo.rut_empresa || '-';
            document.getElementById('profileEmail').textContent = this.userInfo.email || '-';
            document.getElementById('profileUsername').textContent = this.userInfo.username || '-';
        }
    }

    // Initialize the app
    const app = new PortalApp();
    </script>
</body>
</html>