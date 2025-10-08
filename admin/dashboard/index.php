<?php
// Admin Dashboard Page (Simplified)
require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/User.php';

$auth = new Auth();

// Verificar autenticación de admin
if (!$auth->isLoggedIn() || !$auth->checkSessionTimeout() || $auth->getCurrentUserRole() !== 'admin') {
    header('Location: /');
    exit;
}

// Generar token JWT para las peticiones AJAX
$jwtToken = null;
$userData = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    try {
        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);

        if ($user && $user['role'] === 'admin') {
            // Generar token JWT directamente (ahora es método público)
            $jwtToken = $auth->generateToken($user);

            $userData = $user;
            // Limpiar password del array
            unset($userData['password']);
        }
    } catch (Exception $e) {
        error_log("Error generando token JWT para admin: " . $e->getMessage());
    }
}

$title = 'Dashboard Admin - ARA & Bustamante';
$favicon = '🛡️';
$bodyClass = 'admin-body';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'><?= $favicon ?></text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/styles.css">
</head>

<body class="<?= $bodyClass ?>">
    <div class="admin-dashboard">
        <nav class="navbar">
            <div class="nav-brand">
                <div class="brand-logo">
                    <i class="fas fa-user-shield"></i>
                    <span>ARA & BUSTAMANTE - ADMIN</span>
                </div>
            </div>
            <div class="nav-user">
                <button id="notificacionesBtn" class="btn-notification" style="position: relative; background: transparent; border: none; color: #333; font-size: 1.5rem; margin-right: 1.5rem; cursor: pointer; padding: 0.5rem;">
                    <i class="fas fa-bell"></i>
                    <span id="notificationBadge" class="notification-badge" style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.75rem; align-items: center; justify-content: center; font-weight: bold; display: none;">0</span>
                    <span id="messagesBadge" class="messages-badge" style="position: absolute; top: -5px; left: -5px; background: #28a745; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.75rem; align-items: center; justify-content: center; font-weight: bold; display: none;">0</span>
                </button>
                <div class="user-info">
                    <span>Panel Administrativo</span>
                    <div class="user-role">Admin{{user.name}}</div>

                </div>
                <button id="logoutBtn" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i>
                    Salir
                </button>
            </div>
        </nav>

        <div class="dashboard-container">
            <div class="dashboard-header">
                <h2><i class="fas fa-tachometer-alt"></i> Panel de Administración</h2>
                <p>Gestión y administración del sistema</p>
            </div>

            <div class="admin-grid">
                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Gestión de Clientes</h3>
                    <p>Administrar clientes y empresas registradas</p>
                    <button id="clientesBtn" class="btn btn-primary">Gestionar</button>
                </div>

                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <h3>Gestión de Documentos</h3>
                    <p>Administrar categorías y documentos</p>
                    <button id="documentosBtn" class="btn btn-primary">Gestionar</button>
                </div>

                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Estadísticas</h3>
                    <p>Ver estadísticas del sistema</p>
                    <button id="estadisticasBtn" class="btn btn-primary">Ver</button>
                </div>

                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Administradores</h3>
                    <p>Gestionar usuarios administradores</p>
                    <button id="administradoresBtn" class="btn btn-primary">Gestionar</button>
                </div>

                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3>Consultas de Clientes</h3>
                    <p>Ver y responder consultas</p>
                    <button id="consultasBtn" class="btn btn-primary">Ver Consultas</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <span id="notificationText"></span>
            <button class="notification-close" onclick="hideNotification()">×</button>
        </div>
    </div>

    <!-- Modal Chat Consulta -->
    <div id="chatConsultaModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px; height: 80vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="flex-shrink: 0;">
                <div>
                    <h3><i class="fas fa-comments"></i> <span id="chatConsultaTitulo"></span></h3>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #666;"><span id="chatConsultaCliente"></span></p>
                </div>
                <button class="modal-close" onclick="adminDashboard.cerrarChatConsulta()">&times;</button>
            </div>
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 1.5rem; background: #f5f5f5;">
                <div id="chatMensajes" style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Los mensajes se cargarán aquí -->
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0; display: flex; gap: 0.5rem; padding: 1rem; border-top: 2px solid #eee;">
                <textarea id="chatNuevoMensaje" placeholder="Escribe tu respuesta..." style="flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; resize: none; font-family: inherit;" rows="2"></textarea>
                <button onclick="adminDashboard.enviarMensajeChat()" class="btn btn-primary" style="align-self: flex-end;">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Notificaciones -->
    <div id="notificacionesModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-bell"></i> Notificaciones</h3>
                <button class="modal-close" onclick="adminDashboard.cerrarModalNotificaciones()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div id="notificacionesList" style="max-height: 500px; overflow-y: auto;">
                    <!-- Las notificaciones se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gestión de Categorías -->
    <div id="categoriasModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-tags"></i> Gestión de Categorías</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('categoriasModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h4 id="clienteNameCategorias">Cliente: </h4>
                    <p>Gestiona las categorías de documentos disponibles para este cliente.</p>
                </div>

                <div class="modal-section">
                    <h5>Crear Nueva Categoría</h5>
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" id="nombreCategoria" class="form-control" placeholder="Ej: Facturas">
                    </div>
                    <div class="form-group">
                        <label>Código</label>
                        <input type="text" id="codigoCategoria" class="form-control" placeholder="Ej: FACT">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="descripcionCategoria" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                    </div>
                    <button id="agregarCategoriaBtn" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Categoría
                    </button>
                </div>

                <div class="modal-section">
                    <h5>Categorías Asignadas</h5>
                    <div id="categoriasAsignadas" class="categorias-list">
                        <p class="text-muted">Cargando categorías...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gestión de Documentos -->
    <div id="documentosModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3><i class="fas fa-folder"></i> Gestión de Documentos</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('documentosModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h4 id="clienteNameDocumentos">Cliente: </h4>
                    <p>Gestiona los documentos por categoría para este cliente.</p>
                    <button id="crearCategoriaDesdeDocumentosBtn" class="btn btn-primary" onclick="adminDashboard.abrirModalCrearCategoria()" style="margin-bottom: 1rem;">
                        <i class="fas fa-plus"></i> Crear Nueva Categoría
                    </button>
                </div>

                <div class="categorias-grid" id="categoriasDocumentosGrid">
                    <p class="text-muted">Cargando categorías...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Documentos por Categoría -->
    <div id="documentosCategoriaModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3><i class="fas fa-file-alt"></i> Documentos - <span id="categoriaNombre"></span></h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('documentosCategoriaModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <div class="form-row">
                        <button id="subirDocumentoBtn" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Subir Documento
                        </button>
                        <button id="eliminarCategoriaClienteBtn" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar Categoría del Cliente
                        </button>
                    </div>
                </div>

                <!-- Upload Form -->
                <div id="uploadSection" class="modal-section" style="display: none;">
                    <h5>Subir Nuevo Documento</h5>
                    <form id="adminUploadForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <input type="file" id="adminFileInput" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Subir
                            </button>
                            <button type="button" onclick="adminDashboard.cancelarSubida()" class="btn btn-secondary">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="modal-section">
                    <h5>Documentos en esta Categoría</h5>
                    <div id="documentosLista" class="documentos-list">
                        <p class="text-muted">Cargando documentos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Eliminar Categoría -->
    <div id="eliminarCategoriaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('eliminarCategoriaModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar la categoría <strong id="categoriaEliminarNombre"></strong> para este cliente?</p>
                <p class="text-warning"><i class="fas fa-warning"></i> Esta acción no se puede deshacer.</p>
                <div class="form-row">
                    <button id="confirmarEliminarCategoriaBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </button>
                    <button onclick="adminDashboard.closeModal('eliminarCategoriaModal')" class="btn btn-secondary">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Eliminar Documento -->
    <div id="eliminarDocumentoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('eliminarDocumentoModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el documento <strong id="documentoEliminarNombre"></strong>?</p>
                <p class="text-warning"><i class="fas fa-warning"></i> Esta acción no se puede deshacer y el archivo se eliminará permanentemente.</p>
                <div class="form-row">
                    <button id="confirmarEliminarDocumentoBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </button>
                    <button onclick="adminDashboard.closeModal('eliminarDocumentoModal')" class="btn btn-secondary">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Categoría -->
    <div id="editarCategoriaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Categoría</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('editarCategoriaModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="editarCategoriaNombre" class="form-control" placeholder="Nombre de la categoría">
                </div>
                <div class="form-group">
                    <label>Código</label>
                    <input type="text" id="editarCategoriaCodigo" class="form-control" placeholder="Código">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea id="editarCategoriaDescripcion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                </div>
                <div class="form-row">
                    <button onclick="adminDashboard.confirmarEditarCategoria()" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button onclick="adminDashboard.closeModal('editarCategoriaModal')" class="btn btn-secondary">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Cliente -->
    <div id="agregarClienteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Agregar Nuevo Cliente</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('agregarClienteModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="agregarClienteForm">
                    <div class="modal-section">
                        <h5>Información de la Empresa</h5>

                        <div class="form-group">
                            <label for="clienteRazonSocial">Razón Social *</label>
                            <input type="text" id="clienteRazonSocial" class="form-control" required
                                   placeholder="Ej: Empresa Demo S.A.">
                        </div>

                        <div class="form-group">
                            <label for="clienteRut">RUT Empresa *</label>
                            <input type="text" id="clienteRut" class="form-control" required
                                   placeholder="Ej: 76.123.456-7">
                        </div>

                        <div class="form-group">
                            <label for="clienteEmail">Email *</label>
                            <input type="email" id="clienteEmail" class="form-control" required
                                   placeholder="contacto@empresa.com">
                        </div>

                        <div class="form-group">
                            <label for="clienteTelefono">Teléfono</label>
                            <input type="text" id="clienteTelefono" class="form-control"
                                   placeholder="+56 9 1234 5678">
                        </div>

                        <div class="form-group">
                            <label for="clienteDireccion">Dirección</label>
                            <textarea id="clienteDireccion" class="form-control" rows="2"
                                      placeholder="Calle, número, ciudad"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="clienteNombre">Nombre de Contacto</label>
                            <input type="text" id="clienteNombre" class="form-control"
                                   placeholder="Nombre del contacto principal">
                        </div>
                    </div>

                    <div class="modal-section">
                        <h5>Datos de Acceso al Sistema</h5>
                        <p class="text-muted">Si deseas crear un usuario para que el cliente pueda acceder al portal, completa estos campos:</p>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="clienteCrearUsuario" onchange="adminDashboard.toggleUsuarioFields()">
                                Crear usuario de acceso para este cliente
                            </label>
                        </div>

                        <div id="usuarioFields" style="display: none;">
                            <div class="form-group">
                                <label for="clienteUsername">Usuario *</label>
                                <input type="text" id="clienteUsername" class="form-control"
                                       placeholder="Ej: empresa_demo">
                            </div>

                            <div class="form-group">
                                <label for="clientePassword">Contraseña *</label>
                                <input type="password" id="clientePassword" class="form-control"
                                       placeholder="Mínimo 6 caracteres">
                            </div>

                            <div class="form-group">
                                <label for="clientePasswordConfirm">Confirmar Contraseña *</label>
                                <input type="password" id="clientePasswordConfirm" class="form-control"
                                       placeholder="Repite la contraseña">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cliente
                        </button>
                        <button type="button" onclick="adminDashboard.closeModal('agregarClienteModal')" class="btn btn-secondary">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle Cliente -->
    <div id="detalleClienteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Detalle del Cliente</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('detalleClienteModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h5>Información de la Empresa</h5>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>ID:</strong>
                            <span id="detalleId">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Razón Social:</strong>
                            <span id="detalleRazonSocial">-</span>
                        </div>
                        <div class="info-item">
                            <strong>RUT:</strong>
                            <span id="detalleRut">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <span id="detalleEmail">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Teléfono:</strong>
                            <span id="detalleTelefono">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Dirección:</strong>
                            <span id="detalleDireccion">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Nombre Contacto:</strong>
                            <span id="detalleNombre">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Usuario Asociado:</strong>
                            <span id="detalleUsername">-</span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha Creación:</strong>
                            <span id="detalleFecha">-</span>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <button onclick="adminDashboard.closeModal('detalleClienteModal')" class="btn btn-secondary">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Cliente -->
    <div id="editarClienteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Cliente</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('editarClienteModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editarClienteForm">
                    <input type="hidden" id="editClienteId">

                    <div class="modal-section">
                        <h5>Información de la Empresa</h5>

                        <div class="form-group">
                            <label for="editClienteRazonSocial">Razón Social *</label>
                            <input type="text" id="editClienteRazonSocial" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="editClienteRut">RUT Empresa *</label>
                            <input type="text" id="editClienteRut" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="editClienteEmail">Email *</label>
                            <input type="email" id="editClienteEmail" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="editClienteTelefono">Teléfono</label>
                            <input type="text" id="editClienteTelefono" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="editClienteDireccion">Dirección</label>
                            <textarea id="editClienteDireccion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="editClienteNombre">Nombre de Contacto</label>
                            <input type="text" id="editClienteNombre" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" onclick="adminDashboard.closeModal('editarClienteModal')" class="btn btn-secondary">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminar Cliente -->
    <div id="eliminarClienteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('eliminarClienteModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Advertencia!</strong> Esta acción no se puede deshacer.
                </div>

                <p>¿Estás seguro de que deseas eliminar el cliente <strong id="eliminarClienteNombre"></strong>?</p>

                <p class="text-muted">Se eliminarán también:</p>
                <ul class="text-muted">
                    <li>Todos los documentos asociados</li>
                    <li>Las categorías asignadas</li>
                    <li>El historial del cliente</li>
                </ul>

                <div class="form-group">
                    <label for="eliminarClientePassword">Para confirmar, ingresa tu contraseña de administrador:</label>
                    <input type="password" id="eliminarClientePassword" class="form-control"
                           placeholder="Contraseña de administrador" required>
                </div>

                <div class="form-row">
                    <button id="confirmarEliminarClienteBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar Cliente
                    </button>
                    <button onclick="adminDashboard.closeModal('eliminarClienteModal')" class="btn btn-secondary">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Administrador -->
    <div id="crearAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-shield"></i> Crear Nuevo Administrador</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('crearAdminModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="crearAdminForm">
                    <div class="modal-section">
                        <h5>Información del Administrador</h5>

                        <div class="form-group">
                            <label for="adminUsername">Nombre de Usuario *</label>
                            <input type="text" id="adminUsername" class="form-control" required
                                   placeholder="Ej: admin2" pattern="[a-zA-Z0-9_]{3,20}">
                            <small class="text-muted">Solo letras, números y guiones bajos (3-20 caracteres)</small>
                        </div>

                        <div class="form-group">
                            <label for="adminEmail">Email *</label>
                            <input type="email" id="adminEmail" class="form-control" required
                                   placeholder="admin@araybustamante.com">
                        </div>

                        <div class="form-group">
                            <label for="adminPassword">Contraseña *</label>
                            <input type="password" id="adminPassword" class="form-control" required
                                   placeholder="Mínimo 6 caracteres" minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="adminPasswordConfirm">Confirmar Contraseña *</label>
                            <input type="password" id="adminPasswordConfirm" class="form-control" required
                                   placeholder="Repite la contraseña">
                        </div>
                    </div>

                    <div class="form-row">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Crear Administrador
                        </button>
                        <button type="button" onclick="adminDashboard.closeModal('crearAdminModal')" class="btn btn-secondary">
                            Cancelar
                        </button>
                    </div>
                </form>

                <div class="modal-section" style="margin-top: 2rem; border-top: 2px solid #e0e0e0; padding-top: 1.5rem;">
                    <h5><i class="fas fa-users-cog"></i> Administradores Actuales</h5>
                    <div id="listaAdministradores" class="administradores-list">
                        <p class="text-muted">Cargando administradores...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/assets/app.js"></script>
    <script>
    // Admin Dashboard App
    class AdminDashboard {
        constructor() {
            this.baseURL = '/api';
            this.userInfo = JSON.parse(localStorage.getItem('admin_userInfo') || '{}');
            this.init();
        }

        init() {
            this.checkAuth();
            this.bindEvents();
            this.cargarNotificaciones();

            // Actualizar notificaciones cada 30 segundos
            setInterval(() => {
                this.cargarNotificaciones();
            }, 30000);
        }

        checkAuth() {
            const token = localStorage.getItem('admin_token');
            if (!token || !this.userInfo.username || this.userInfo.role !== 'admin') {
                window.location.href = '/admin/login/';
                return;
            }
        }

        bindEvents() {
            // Logout
            document.getElementById('logoutBtn').addEventListener('click', () => {
                this.logout();
            });

            // Notificaciones
            document.getElementById('notificacionesBtn').addEventListener('click', () => {
                this.abrirModalNotificaciones();
            });

            // Gestión de Clientes
            document.getElementById('clientesBtn').addEventListener('click', () => {
                this.loadClientesManagement();
            });

            // Gestión de Documentos
            document.getElementById('documentosBtn').addEventListener('click', () => {
                this.loadDocumentosManagement();
            });

            // Estadísticas
            document.getElementById('estadisticasBtn').addEventListener('click', () => {
                this.loadEstadisticas();
            });

            // Administradores
            document.getElementById('administradoresBtn').addEventListener('click', () => {
                this.mostrarModalCrearAdmin();
            });

            // Consultas
            document.getElementById('consultasBtn').addEventListener('click', () => {
                this.loadConsultasManagement();
            });
        }

        loadClientesManagement() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-users"></i> Gestión de Clientes';
            document.querySelector('.dashboard-header p').textContent = 'Administrar clientes y empresas registradas';

            // Crear contenido de gestión de clientes
            const container = document.querySelector('.dashboard-container');
            let clientesSection = document.getElementById('clientes-section');

            if (!clientesSection) {
                clientesSection = document.createElement('div');
                clientesSection.id = 'clientes-section';
                clientesSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                        <button id="addClienteBtn" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Cliente
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Razón Social</th>
                                    <th>RUT</th>
                                    <th>Email</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientesTableBody">
                                <tr><td colspan="6">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
                container.appendChild(clientesSection);
            }

            clientesSection.style.display = 'block';
            this.loadClientes();
            this.bindClientesEvents();
        }

        loadDocumentosManagement() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-folder"></i> Gestión de Documentos';
            document.querySelector('.dashboard-header p').textContent = 'Administrar documentos y categorías por cliente';

            // Crear contenido de gestión de documentos
            const container = document.querySelector('.dashboard-container');
            let documentosSection = document.getElementById('documentos-section');

            if (!documentosSection) {
                documentosSection = document.createElement('div');
                documentosSection.id = 'documentos-section';
                documentosSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard2" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                    </div>
                    <div class="table-container">
                        <h4 style="margin-bottom: 1rem;">Selecciona un Cliente</h4>
                        <p class="text-muted" style="margin-bottom: 1.5rem;">Haz click en un cliente para ver sus categorías y documentos</p>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Razón Social</th>
                                    <th>RUT</th>
                                    <th>Email</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="clientesDocumentosTableBody">
                                <tr><td colspan="6">Cargando clientes...</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
                container.appendChild(documentosSection);
            }

            documentosSection.style.display = 'block';

            // Ocultar otras secciones
            const clientesSection = document.getElementById('clientes-section');
            const statsSection = document.getElementById('stats-section');
            if (clientesSection) clientesSection.style.display = 'none';
            if (statsSection) statsSection.style.display = 'none';

            this.loadClientesForDocumentos();
            this.bindDocumentosManagementEvents();
        }

        bindDocumentosManagementEvents() {
            document.getElementById('backToDashboard2')?.addEventListener('click', () => {
                this.showDashboard();
            });
        }

        async loadClientesForDocumentos() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/clientes`);
                const tbody = document.getElementById('clientesDocumentosTableBody');

                if (response.success && response.data && response.data.length > 0) {
                    tbody.innerHTML = response.data.map(cliente => {
                        const razonSocial = (cliente.razon_social || 'N/A').replace(/'/g, "\\'");
                        return `
                        <tr>
                            <td>${cliente.id}</td>
                            <td>${cliente.razon_social || 'N/A'}</td>
                            <td>${cliente.rut_empresa || 'N/A'}</td>
                            <td>${cliente.email || 'N/A'}</td>
                            <td>${cliente.username || 'Sin usuario'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="adminDashboard.verCategoriasCliente(${cliente.id}, '${razonSocial}')" title="Ver Categorías y Documentos">
                                    <i class="fas fa-folder-open"></i> Ver Documentos
                                </button>
                            </td>
                        </tr>
                    `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6">No hay clientes registrados</td></tr>';
                }
            } catch (error) {
                console.error('Error loading clientes:', error);
                showNotification('Error al cargar clientes: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        verCategoriasCliente(clienteId, clienteNombre) {
            this.currentClienteId = clienteId;
            this.currentClienteNombre = clienteNombre;

            document.getElementById('clienteNameDocumentos').textContent = `Cliente: ${clienteNombre}`;
            this.showModal('documentosModal');
            this.loadCategoriasParaDocumentos();
        }

        abrirModalCrearCategoria() {
            // Cerrar modal de documentos y abrir modal de categorías
            this.closeModal('documentosModal');

            // Abrir modal de gestión de categorías con los datos del cliente actual
            document.getElementById('clienteNameCategorias').textContent = `Cliente: ${this.currentClienteNombre}`;
            this.showModal('categoriasModal');
            this.loadCategoriasAsignadas();

            // Vincular botón de crear categoría
            document.getElementById('agregarCategoriaBtn').onclick = () => this.asignarCategoria();
        }

        loadEstadisticas() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-chart-line"></i> Estadísticas del Sistema';
            document.querySelector('.dashboard-header p').textContent = 'Métricas y estadísticas generales';

            // Crear contenido de estadísticas
            const container = document.querySelector('.dashboard-container');
            let statsSection = document.getElementById('stats-section');

            if (!statsSection) {
                statsSection = document.createElement('div');
                statsSection.id = 'stats-section';
                statsSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard3" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-content">
                                <h3 id="totalUsuarios">-</h3>
                                <p>Total Usuarios</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-building"></i></div>
                            <div class="stat-content">
                                <h3 id="totalClientes">-</h3>
                                <p>Total Clientes</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                            <div class="stat-content">
                                <h3 id="totalDocumentos">-</h3>
                                <p>Documentos Transados</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-tags"></i></div>
                            <div class="stat-content">
                                <h3 id="totalCategorias">-</h3>
                                <p>Total Categorías</p>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(statsSection);
            }

            statsSection.style.display = 'block';
            this.loadStats();
            this.bindStatsEvents();
        }

        loadConsultasManagement() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-question-circle"></i> Consultas de Clientes';
            document.querySelector('.dashboard-header p').textContent = 'Ver y gestionar consultas de clientes';

            // Crear contenido de consultas
            const container = document.querySelector('.dashboard-container');
            let consultasSection = document.getElementById('consultas-section');

            if (!consultasSection) {
                consultasSection = document.createElement('div');
                consultasSection.id = 'consultas-section';
                consultasSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard4" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                    </div>
                    <div id="consultasContainer" class="consultas-list">
                        <!-- Las consultas se cargarán aquí -->
                    </div>
                `;
                container.appendChild(consultasSection);
            }

            consultasSection.style.display = 'block';
            this.loadConsultas();
            this.bindConsultasEvents();
        }

        bindClientesEvents() {
            document.getElementById('backToDashboard')?.addEventListener('click', () => {
                this.showDashboard();
            });

            // Bind agregar cliente
            document.getElementById('addClienteBtn')?.addEventListener('click', () => {
                this.mostrarModalAgregarCliente();
            });
        }

        // bindDocumentosEvents() eliminado - ya no se necesita

        bindStatsEvents() {
            document.getElementById('backToDashboard3')?.addEventListener('click', () => {
                this.showDashboard();
            });
        }

        bindConsultasEvents() {
            document.getElementById('backToDashboard4')?.addEventListener('click', () => {
                this.showDashboard();
            });
        }

        showDashboard() {
            // Ocultar todas las secciones
            document.getElementById('clientes-section')?.style.setProperty('display', 'none');
            document.getElementById('documentos-section')?.style.setProperty('display', 'none');
            document.getElementById('stats-section')?.style.setProperty('display', 'none');
            document.getElementById('consultas-section')?.style.setProperty('display', 'none');

            // Mostrar dashboard principal
            document.querySelector('.admin-grid').style.display = 'grid';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-tachometer-alt"></i> Panel de Administración';
            document.querySelector('.dashboard-header p').textContent = 'Gestión y administración del sistema';
        }

        switchTab(tabName) {
            // Remover clase active de todos los botones y contenidos
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Activar el botón y contenido seleccionado
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');

            // Cargar datos según la pestaña
            if (tabName === 'categorias') {
                this.loadCategorias();
            } else if (tabName === 'documentos') {
                this.loadAllDocumentos();
            }
        }

        async loadClientes() {
            try {
                showLoading();
                console.log('Cargando clientes...');
                const response = await makeRequest(`${this.baseURL}/admin/clientes`);
                console.log('Respuesta del servidor:', response);
                const tbody = document.getElementById('clientesTableBody');

                if (!tbody) {
                    console.error('No se encontró el elemento clientesTableBody');
                    return;
                }

                // Extraer el array de datos según la estructura de respuesta
                let clientes = [];
                if (response && response.success && response.data) {
                    clientes = response.data;
                } else if (Array.isArray(response)) {
                    clientes = response;
                }

                console.log('Clientes procesados:', clientes);
                console.log('Total de clientes:', clientes.length);

                if (clientes && clientes.length > 0) {
                    tbody.innerHTML = clientes.map(cliente => {
                        // Manejar diferentes estructuras de email
                        const email = cliente.email || cliente.cliente_email || cliente.correo_contacto || 'N/A';
                        const razonSocial = (cliente.razon_social || cliente.empresa || 'Sin nombre').replace(/'/g, "\\'");

                        return `
                        <tr>
                            <td>${cliente.id}</td>
                            <td>${cliente.razon_social || cliente.empresa || 'Sin nombre'}</td>
                            <td>${cliente.rut_empresa || 'N/A'}</td>
                            <td>${email}</td>
                            <td>${cliente.username || 'N/A'}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="adminDashboard.verDetalleCliente(${cliente.id})" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="adminDashboard.gestionarCategorias(${cliente.id}, '${razonSocial}')" title="Gestionar Categorías">
                                    <i class="fas fa-tags"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="adminDashboard.gestionarDocumentos(${cliente.id}, '${razonSocial}')" title="Gestionar Documentos">
                                    <i class="fas fa-folder"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="adminDashboard.editarCliente(${cliente.id})" title="Editar Cliente">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.eliminarCliente(${cliente.id}, '${razonSocial}')" title="Eliminar Cliente">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6">No hay clientes registrados</td></tr>';
                }
            } catch (error) {
                console.error('Error loading clientes:', error);
                showNotification('Error al cargar clientes: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Métodos loadCategorias() y loadAllDocumentos() eliminados
        // La gestión de categorías y documentos ahora se realiza por cliente

        async loadStats() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/stats`);

                console.log('Stats response:', response);

                if (response) {
                    document.getElementById('totalUsuarios').textContent = response.usuarios || 0;
                    document.getElementById('totalClientes').textContent = response.clientes || 0;
                    document.getElementById('totalDocumentos').textContent = response.documentos || 0;
                    document.getElementById('totalCategorias').textContent = response.categorias || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                showNotification('Error al cargar estadísticas', 'error');
            } finally {
                hideLoading();
            }
        }

        async loadConsultas() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/consultas`);

                const container = document.getElementById('consultasContainer');

                if (!response || !response.data || response.data.length === 0) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <h3>No hay consultas</h3>
                            <p>No se han recibido consultas de clientes aún.</p>
                        </div>
                    `;
                    return;
                }

                const consultas = response.data;
                const consultasConRespuestas = this.consultasConRespuestas || [];

                container.innerHTML = consultas.map(consulta => {
                    const consultaConRespuesta = consultasConRespuestas.find(c => c.consulta_id == consulta.id);
                    const tieneRespuestas = consultaConRespuesta && consultaConRespuesta.total_no_leidos > 0;

                    return `
                    <div class="consulta-card" style="background: white; border: 1px solid ${tieneRespuestas ? '#28a745' : '#e0e0e0'}; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.3s; position: relative;" onclick="adminDashboard.abrirChatConsulta(${consulta.id}, '${escapeHtml(consulta.asunto).replace(/'/g, "\\'")}', '${escapeHtml(consulta.cliente_razon_social || 'Sin cliente').replace(/'/g, "\\'")}')">
                        ${tieneRespuestas ? `
                            <div style="position: absolute; top: 10px; right: 10px; background: #28a745; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">
                                ${consultaConRespuesta.total_no_leidos}
                            </div>
                        ` : ''}
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #333;">
                                    <i class="fas fa-comments"></i> ${escapeHtml(consulta.asunto)}
                                    ${tieneRespuestas ? '<span style="color: #28a745; font-size: 0.9rem; margin-left: 0.5rem;"><i class="fas fa-circle" style="font-size: 0.6rem;"></i> Nueva respuesta</span>' : ''}
                                </h3>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: #666;">
                                    <span><i class="fas fa-building"></i> ${escapeHtml(consulta.cliente_razon_social || 'Sin cliente')}</span>
                                    <span><i class="fas fa-calendar"></i> ${formatDate(consulta.fecha)}</span>
                                    <span class="badge ${consulta.estado === 'pendiente' ? 'badge-warning' : consulta.estado === 'resuelto' ? 'badge-success' : 'badge-info'}">
                                        ${consulta.estado === 'pendiente' ? 'Pendiente' : consulta.estado === 'resuelto' ? 'Resuelto' : 'En Proceso'}
                                    </span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;" onclick="event.stopPropagation();">
                                ${consulta.estado !== 'resuelto' ? `
                                    <button class="btn btn-sm btn-success" onclick="adminDashboard.cambiarEstadoConsulta(${consulta.id}, 'resuelto')">
                                        <i class="fas fa-check"></i> Resuelto
                                    </button>
                                ` : ''}
                                ${consulta.estado === 'pendiente' ? `
                                    <button class="btn btn-sm btn-info" onclick="adminDashboard.cambiarEstadoConsulta(${consulta.id}, 'en_proceso')">
                                        <i class="fas fa-hourglass-half"></i> En Proceso
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                        <div style="padding: 1rem; background: #f8f9fa; border-radius: 6px;">
                            <strong style="color: #333;">Mensaje inicial:</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #666; white-space: pre-wrap;">${escapeHtml(consulta.mensaje)}</p>
                        </div>
                        <div style="margin-top: 1rem; padding: 0.75rem; background: #e7f3ff; border-left: 4px solid #007cba; border-radius: 4px; text-align: center;">
                            <i class="fas fa-mouse-pointer" style="color: #007cba;"></i>
                            <strong style="color: #005a8b;">Clic para abrir el chat y responder</strong>
                        </div>
                    </div>
                `;
                }).join('');

            } catch (error) {
                console.error('Error loading consultas:', error);
                showNotification('Error al cargar consultas', 'error');
                document.getElementById('consultasContainer').innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h3>Error al cargar consultas</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            } finally {
                hideLoading();
            }
        }

        async cambiarEstadoConsulta(consultaId, nuevoEstado) {
            try {
                showLoading();
                await makeRequest(`${this.baseURL}/consultas/${consultaId}/estado`, {
                    method: 'PUT',
                    body: JSON.stringify({ estado: nuevoEstado })
                });

                showNotification('Estado actualizado correctamente', 'success');
                this.loadConsultas();
                this.cargarNotificaciones(); // Actualizar contador
            } catch (error) {
                console.error('Error cambiando estado:', error);
                showNotification('Error al cambiar estado', 'error');
            } finally {
                hideLoading();
            }
        }

        async abrirChatConsulta(consultaId, asunto, clienteNombre) {
            this.currentConsultaId = consultaId;
            document.getElementById('chatConsultaTitulo').textContent = asunto;
            document.getElementById('chatConsultaCliente').textContent = `Cliente: ${clienteNombre}`;
            document.getElementById('chatConsultaModal').style.display = 'flex';
            document.getElementById('chatNuevoMensaje').value = '';

            await this.cargarMensajesChat();

            // Recargar notificaciones después de abrir el chat (los mensajes se marcan como leídos)
            setTimeout(() => {
                this.cargarNotificaciones();
            }, 500);
        }

        cerrarChatConsulta() {
            document.getElementById('chatConsultaModal').style.display = 'none';
            this.currentConsultaId = null;
        }

        abrirModalNotificaciones() {
            document.getElementById('notificacionesModal').style.display = 'flex';
            this.cargarListaNotificaciones();
        }

        cerrarModalNotificaciones() {
            document.getElementById('notificacionesModal').style.display = 'none';
        }

        async cargarListaNotificaciones() {
            try {
                const container = document.getElementById('notificacionesList');

                // Obtener consultas con respuestas no leídas
                const response = await makeRequest(`${this.baseURL}/consultas/con-respuestas`);

                if (!response || !response.success || !response.data || response.data.length === 0) {
                    container.innerHTML = `
                        <div style="padding: 3rem; text-align: center; color: #000000ff;">
                            <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No hay notificaciones nuevas</p>
                        </div>
                    `;
                    return;
                }

                const notificaciones = response.data;

                container.innerHTML = notificaciones.map(notif => `
                    <div class="notificacion-item" onclick="adminDashboard.abrirConsultaDesdeNotificacion(${notif.consulta_id})" style="padding: 1.25rem; border-bottom: 1px solid #e0e0e0; cursor: pointer; transition: background 0.2s; display: flex; gap: 1rem; align-items: start;">
                        <div style="flex-shrink: 0; width: 45px; height: 45px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; font-size: 1rem; color: #333;">
                                    ${escapeHtml(notif.asunto)}
                                </h4>
                                <span style="background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: bold;">
                                    ${notif.total_no_leidos}
                                </span>
                            </div>
                            <p style="margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem;">
                                <i class="fas fa-building"></i> ${escapeHtml(notif.cliente_razon_social || 'Sin cliente')}
                            </p>
                            <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: #999;">
                                <span><i class="fas fa-comments"></i> ${notif.total_no_leidos} mensaje${notif.total_no_leidos > 1 ? 's' : ''} nuevo${notif.total_no_leidos > 1 ? 's' : ''}</span>
                                <span class="badge ${notif.estado === 'pendiente' ? 'badge-warning' : notif.estado === 'resuelto' ? 'badge-success' : 'badge-info'}">
                                    ${notif.estado === 'pendiente' ? 'Pendiente' : notif.estado === 'resuelto' ? 'Resuelto' : 'En Proceso'}
                                </span>
                            </div>
                        </div>
                        <div style="flex-shrink: 0; color: #28a745; font-size: 1.25rem;">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `).join('');

                // Agregar hover effect
                const items = container.querySelectorAll('.notificacion-item');
                items.forEach(item => {
                    item.addEventListener('mouseenter', () => {
                        item.style.background = '#f8f9fa';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.background = 'white';
                    });
                });

            } catch (error) {
                console.error('Error cargando lista de notificaciones:', error);
                document.getElementById('notificacionesList').innerHTML = `
                    <div style="padding: 2rem; text-align: center; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar las notificaciones</p>
                    </div>
                `;
            }
        }

        async abrirConsultaDesdeNotificacion(consultaId) {
            try {
                // Cerrar modal de notificaciones
                this.cerrarModalNotificaciones();

                // Obtener datos de la consulta
                const response = await makeRequest(`${this.baseURL}/consultas`);

                if (response && response.success) {
                    const consulta = response.data.find(c => c.id == consultaId);
                    if (consulta) {
                        // Abrir el chat de la consulta
                        this.abrirChatConsulta(
                            consulta.id,
                            consulta.asunto,
                            consulta.cliente_razon_social || 'Sin cliente'
                        );
                    }
                }

            } catch (error) {
                console.error('Error abriendo consulta desde notificación:', error);
                showNotification('Error al abrir la consulta', 'error');
            }
        }

        async cargarNotificaciones() {
            try {
                // Cargar consultas pendientes
                const response = await makeRequest(`${this.baseURL}/consultas/pendientes/count`);

                if (response && response.success) {
                    const total = response.total || 0;
                    const badge = document.getElementById('notificationBadge');

                    if (total > 0) {
                        badge.textContent = total;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                // Cargar consultas con respuestas del cliente
                const respuestasResponse = await makeRequest(`${this.baseURL}/consultas/con-respuestas`);

                if (respuestasResponse && respuestasResponse.success) {
                    const consultasConRespuestas = respuestasResponse.data || [];
                    const totalMensajes = consultasConRespuestas.reduce((sum, item) => sum + (item.total_no_leidos || 0), 0);
                    const messagesBadge = document.getElementById('messagesBadge');

                    // Guardar las consultas con respuestas para uso posterior
                    this.consultasConRespuestas = consultasConRespuestas;

                    if (totalMensajes > 0) {
                        messagesBadge.textContent = totalMensajes;
                        messagesBadge.style.display = 'flex';
                    } else {
                        messagesBadge.style.display = 'none';
                    }
                }

            } catch (error) {
                console.error('Error cargando notificaciones:', error);
            }
        }

        async cargarMensajesChat() {
            try {
                const response = await makeRequest(`${this.baseURL}/consultas/${this.currentConsultaId}/mensajes`);

                const container = document.getElementById('chatMensajes');

                if (!response || !response.data || response.data.length === 0) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 2rem; color: #999;">
                            <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No hay mensajes aún. Sé el primero en responder.</p>
                        </div>
                    `;
                    return;
                }

                const mensajes = response.data;
                container.innerHTML = mensajes.map(mensaje => this.crearMensajeBurbuja(mensaje)).join('');

                // Scroll al final
                container.parentElement.scrollTop = container.parentElement.scrollHeight;

            } catch (error) {
                console.error('Error cargando mensajes:', error);
                showNotification('Error al cargar mensajes', 'error');
            }
        }

        crearMensajeBurbuja(mensaje) {
            const esAdmin = mensaje.es_admin == 1 || mensaje.es_admin === true;
            const alignment = esAdmin ? 'flex-end' : 'flex-start';
            const bgColor = esAdmin ? '#007cba' : '#e0e0e0';
            const textColor = esAdmin ? 'white' : '#333';
            const labelColor = esAdmin ? 'rgba(255,255,255,0.9)' : '#666';

            return `
                <div style="display: flex; flex-direction: column; align-items: ${alignment};">
                    <div style="max-width: 70%; background: ${bgColor}; color: ${textColor}; padding: 0.75rem 1rem; border-radius: 12px; ${esAdmin ? 'border-bottom-right-radius: 4px;' : 'border-bottom-left-radius: 4px;'}">
                        <div style="font-size: 0.85rem; color: ${labelColor}; margin-bottom: 0.25rem; font-weight: 500;">
                            ${esAdmin ? '<i class="fas fa-user-shield"></i> Admin' : '<i class="fas fa-user"></i> Cliente'} ${mensaje.username ? `(${escapeHtml(mensaje.username)})` : ''}
                        </div>
                        <div style="white-space: pre-wrap; line-height: 1.4;">${escapeHtml(mensaje.mensaje)}</div>
                        <div style="font-size: 0.75rem; color: ${labelColor}; margin-top: 0.5rem; text-align: right;">
                            ${formatDate(mensaje.fecha)}
                        </div>
                    </div>
                </div>
            `;
        }

        async enviarMensajeChat() {
            const textarea = document.getElementById('chatNuevoMensaje');
            const mensaje = textarea.value.trim();

            if (!mensaje) {
                showNotification('Escribe un mensaje', 'warning');
                return;
            }

            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/consultas/${this.currentConsultaId}/mensajes`, {
                    method: 'POST',
                    body: JSON.stringify({ mensaje })
                });

                textarea.value = '';

                // Agregar el mensaje al chat inmediatamente
                if (response.success && response.data) {
                    const container = document.getElementById('chatMensajes');
                    const mensajeHTML = this.crearMensajeBurbuja(response.data);
                    container.insertAdjacentHTML('beforeend', mensajeHTML);

                    // Scroll al final
                    container.parentElement.scrollTop = container.parentElement.scrollHeight;
                }

                showNotification('Mensaje enviado', 'success');

                // Recargar notificaciones para actualizar contador
                this.cargarNotificaciones();

            } catch (error) {
                console.error('Error enviando mensaje:', error);
                showNotification('Error al enviar mensaje', 'error');
            } finally {
                hideLoading();
            }
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        async downloadDocument(ruta) {
            try {
                showLoading();

                // Primero verificar que la descarga está autorizada
                const verifyResponse = await makeRequest(`${this.baseURL}/documentos/verify-download?file=${encodeURIComponent(ruta)}`);

                if (verifyResponse.success) {
                    // Si está autorizada, proceder con la descarga
                    const token = localStorage.getItem('admin_token');
                    window.open(`${this.baseURL}/documentos/download?file=${encodeURIComponent(ruta)}&token=${encodeURIComponent(token)}`, '_blank');
                    showNotification('Descarga iniciada', 'success');
                } else {
                    showNotification('No tienes permisos para descargar este archivo', 'error');
                }
            } catch (error) {
                console.error('Error en descarga admin:', error);
                showNotification('Error al iniciar descarga: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        async logout() {
            try {
                await makeRequest(`${this.baseURL}/auth/logout`, { method: 'POST' });
            } catch (error) {
                console.error('Logout error:', error);
            }

            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_userInfo');
            showNotification('Sesión cerrada correctamente');

            setTimeout(() => {
                window.location.href = '/admin/login/';
            }, 1000);
        }

        // Nuevas funcionalidades para gestión de categorías y documentos
        gestionarCategorias(clienteId, clienteNombre) {
            this.currentClienteId = clienteId;
            this.currentClienteNombre = clienteNombre;

            document.getElementById('clienteNameCategorias').textContent = `Cliente: ${clienteNombre}`;
            this.showModal('categoriasModal');
            this.loadCategoriasAsignadas();

            // Vincular botón de crear categoría
            document.getElementById('agregarCategoriaBtn').onclick = () => this.asignarCategoria();
        }

        gestionarDocumentos(clienteId, clienteNombre) {
            this.currentClienteId = clienteId;
            this.currentClienteNombre = clienteNombre;

            document.getElementById('clienteNameDocumentos').textContent = `Cliente: ${clienteNombre}`;
            this.showModal('documentosModal');
            this.loadCategoriasParaDocumentos();
        }

        async loadCategoriasDisponibles() {
            // Ya no se usa este método - las categorías son individuales por cliente
            // Se crea directamente desde el modal
        }

        async loadCategoriasAsignadas() {
            try {
                const response = await makeRequest(`${this.baseURL}/clientes/${this.currentClienteId}/categorias`);
                const container = document.getElementById('categoriasAsignadas');

                if (response.success && response.data && response.data.length > 0) {
                    container.innerHTML = response.data.map(categoria => {
                        const nombreEscaped = (categoria.nombre || '').replace(/'/g, "\\'");
                        return `
                        <div class="categoria-item">
                            <div class="categoria-info">
                                <h6>${categoria.nombre}</h6>
                                <p>${categoria.descripcion || 'Sin descripción'}</p>
                                <small class="text-muted">Código: ${categoria.codigo || 'N/A'}</small>
                            </div>
                            <div class="categoria-actions">
                                <button class="btn btn-sm btn-primary" onclick="adminDashboard.editarCategoria(${categoria.id}, '${nombreEscaped}', '${categoria.descripcion || ''}', '${categoria.codigo || ''}')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.eliminarCategoriaCliente(${categoria.id}, '${nombreEscaped}')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    }).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">Este cliente no tiene categorías. Crea una nueva categoría usando el botón de abajo.</p>';
                }

            } catch (error) {
                console.error('Error loading categorías asignadas:', error);
                showNotification('Error al cargar categorías asignadas', 'error');
            }
        }

        async asignarCategoria() {
            const nombre = document.getElementById('nombreCategoria').value.trim();
            const codigo = document.getElementById('codigoCategoria').value.trim();
            const descripcion = document.getElementById('descripcionCategoria').value.trim();

            if (!nombre) {
                showNotification('El nombre de la categoría es requerido', 'warning');
                return;
            }

            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/clientes/${this.currentClienteId}/categorias`, {
                    method: 'POST',
                    body: JSON.stringify({
                        nombre,
                        codigo: codigo || null,
                        descripcion: descripcion || null
                    })
                });

                if (response.success) {
                    showNotification('Categoría creada exitosamente', 'success');
                    this.loadCategoriasAsignadas();
                    this.closeModal('categoriasModal');
                    document.getElementById('nombreCategoria').value = '';
                    document.getElementById('codigoCategoria').value = '';
                    document.getElementById('descripcionCategoria').value = '';
                } else {
                    showNotification(response.message || 'Error al crear categoría', 'error');
                }

            } catch (error) {
                console.error('Error creando categoría:', error);
                showNotification('Error al crear categoría', 'error');
            } finally {
                hideLoading();
            }
        }

        editarCategoria(categoriaId, nombre, descripcion, codigo) {
            this.currentCategoriaId = categoriaId;
            document.getElementById('editarCategoriaNombre').value = nombre;
            document.getElementById('editarCategoriaCodigo').value = codigo || '';
            document.getElementById('editarCategoriaDescripcion').value = descripcion || '';
            this.showModal('editarCategoriaModal');
        }

        async confirmarEditarCategoria() {
            const nombre = document.getElementById('editarCategoriaNombre').value.trim();
            const codigo = document.getElementById('editarCategoriaCodigo').value.trim();
            const descripcion = document.getElementById('editarCategoriaDescripcion').value.trim();

            if (!nombre) {
                showNotification('El nombre de la categoría es requerido', 'warning');
                return;
            }

            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/categorias/${this.currentCategoriaId}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        nombre,
                        codigo: codigo || null,
                        descripcion: descripcion || null
                    })
                });

                if (response.success) {
                    showNotification('Categoría actualizada exitosamente', 'success');
                    this.loadCategoriasAsignadas();
                    this.closeModal('editarCategoriaModal');
                } else {
                    showNotification(response.message || 'Error al actualizar categoría', 'error');
                }

            } catch (error) {
                console.error('Error actualizando categoría:', error);
                showNotification('Error al actualizar categoría', 'error');
            } finally {
                hideLoading();
            }
        }

        eliminarCategoriaCliente(categoriaId, categoriaNombre) {
            this.currentCategoriaId = categoriaId;
            document.getElementById('categoriaEliminarNombre').textContent = categoriaNombre;
            this.showModal('eliminarCategoriaModal');

            document.getElementById('confirmarEliminarCategoriaBtn').onclick = () => this.confirmarEliminarCategoria();
        }

        async confirmarEliminarCategoria() {
            try {
                showLoading();
                const response = await makeRequest(
                    `${this.baseURL}/categorias/${this.currentCategoriaId}`,
                    { method: 'DELETE' }
                );

                if (response.success) {
                    showNotification('Categoría eliminada exitosamente', 'success');
                    this.loadCategoriasAsignadas();
                    this.closeModal('eliminarCategoriaModal');
                } else {
                    showNotification(response.message || 'Error al eliminar categoría', 'error');
                }

            } catch (error) {
                console.error('Error eliminando categoría:', error);
                showNotification('Error al eliminar categoría', 'error');
            } finally {
                hideLoading();
            }
        }

        async loadCategoriasParaDocumentos() {
            try {
                const response = await makeRequest(`${this.baseURL}/clientes/${this.currentClienteId}/categorias`);
                const container = document.getElementById('categoriasDocumentosGrid');

                if (response.success && response.data && response.data.length > 0) {
                    container.innerHTML = response.data.map(categoria => {
                        const nombreEscaped = (categoria.nombre || '').replace(/'/g, "\\'");
                        return `
                        <div class="categoria-card" onclick="adminDashboard.abrirDocumentosCategoria(${categoria.id}, '${nombreEscaped}')" style="cursor: pointer;">
                            <div class="categoria-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <h5>${categoria.nombre}</h5>
                            <p>${categoria.descripcion || 'Sin descripción'}</p>
                            <div class="categoria-stats">
                                <small class="text-muted"><i class="fas fa-mouse-pointer"></i> Click para ver documentos</small>
                            </div>
                        </div>
                    `;
                    }).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">Este cliente no tiene categorías. Crea una categoría primero en la gestión de categorías.</p>';
                }

            } catch (error) {
                console.error('Error loading categorías para documentos:', error);
                showNotification('Error al cargar categorías', 'error');
            }
        }

        async abrirDocumentosCategoria(categoriaId, categoriaNombre) {
            this.currentCategoriaId = categoriaId;
            this.currentCategoriaNombre = categoriaNombre;

            document.getElementById('categoriaNombre').textContent = categoriaNombre;
            this.showModal('documentosCategoriaModal');
            this.loadDocumentosCategoria();

            // Bind events
            document.getElementById('subirDocumentoBtn').onclick = () => this.mostrarFormularioSubida();
            document.getElementById('eliminarCategoriaClienteBtn').onclick = () => this.eliminarCategoriaClienteFromDocs();
            document.getElementById('adminUploadForm').onsubmit = (e) => this.subirDocumentoAdmin(e);
        }

        async loadDocumentosCategoria() {
            try {
                const response = await makeRequest(
                    `${this.baseURL}/admin/clientes/${this.currentClienteId}/documentos/${this.currentCategoriaId}`
                );
                const container = document.getElementById('documentosLista');

                if (response.success && response.data && response.data.length > 0) {
                    container.innerHTML = response.data.map(doc => {
                        const nombreEscaped = (doc.nombre_archivo || '').replace(/'/g, "\\'");
                        return `
                        <div class="documento-item">
                            <div class="documento-info">
                                <i class="fas ${this.getFileIcon(doc.nombre_archivo)}"></i>
                                <div class="documento-details">
                                    <h6>${doc.nombre_archivo}</h6>
                                    <small>Subido: ${new Date(doc.created_at).toLocaleDateString()}</small>
                                    <small>Tamaño: ${this.formatFileSize(doc.tamano)}</small>
                                </div>
                            </div>
                            <div class="documento-actions">
                                <button class="btn btn-sm btn-secondary" onclick="adminDashboard.downloadDocument('${doc.ruta_archivo}')" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.confirmarEliminarDocumento(${doc.id}, '${nombreEscaped}')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    }).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">No hay documentos en esta categoría.</p>';
                }

            } catch (error) {
                console.error('Error loading documentos:', error);
                showNotification('Error al cargar documentos', 'error');
            }
        }

        mostrarFormularioSubida() {
            document.getElementById('uploadSection').style.display = 'block';
            document.getElementById('subirDocumentoBtn').style.display = 'none';
        }

        cancelarSubida() {
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('subirDocumentoBtn').style.display = 'inline-block';
            document.getElementById('adminUploadForm').reset();
        }

        async subirDocumentoAdmin(event) {
            event.preventDefault();

            const fileInput = document.getElementById('adminFileInput');
            const file = fileInput.files[0];

            if (!file) {
                showNotification('Selecciona un archivo', 'warning');
                return;
            }

            try {
                showLoading();

                const formData = new FormData();
                formData.append('archivo', file);
                formData.append('categoria_id', this.currentCategoriaId);
                formData.append('cliente_id', this.currentClienteId);

                const response = await fetch(`${this.baseURL}/documentos/upload`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Documento subido exitosamente', 'success');
                    this.loadDocumentosCategoria();
                    this.cancelarSubida();
                } else {
                    showNotification(result.error || 'Error al subir documento', 'error');
                }

            } catch (error) {
                console.error('Error subiendo documento:', error);
                showNotification('Error al subir documento', 'error');
            } finally {
                hideLoading();
            }
        }

        eliminarCategoriaClienteFromDocs() {
            this.eliminarCategoriaCliente(this.currentCategoriaId, this.currentCategoriaNombre);
        }

        confirmarEliminarDocumento(documentoId, documentoNombre) {
            this.currentDocumentoId = documentoId;
            document.getElementById('documentoEliminarNombre').textContent = documentoNombre;
            this.showModal('eliminarDocumentoModal');

            document.getElementById('confirmarEliminarDocumentoBtn').onclick = () => this.eliminarDocumento();
        }

        async eliminarDocumento() {
            try {
                showLoading();
                const response = await makeRequest(
                    `${this.baseURL}/documentos/delete/${this.currentDocumentoId}`,
                    { method: 'DELETE' }
                );

                if (response.success) {
                    showNotification('Documento eliminado exitosamente', 'success');
                    this.loadDocumentosCategoria();
                    this.closeModal('eliminarDocumentoModal');
                } else {
                    showNotification(response.error || 'Error al eliminar documento', 'error');
                }

            } catch (error) {
                console.error('Error eliminando documento:', error);
                showNotification('Error al eliminar documento', 'error');
            } finally {
                hideLoading();
            }
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
                'png': 'fa-file-image'
            };
            return iconMap[extension] || 'fa-file';
        }

        showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Funciones para agregar cliente
        mostrarModalAgregarCliente() {
            document.getElementById('agregarClienteForm').reset();
            document.getElementById('usuarioFields').style.display = 'none';
            document.getElementById('clienteCrearUsuario').checked = false;
            this.showModal('agregarClienteModal');

            // Bind submit event
            document.getElementById('agregarClienteForm').onsubmit = (e) => this.guardarCliente(e);
        }

        toggleUsuarioFields() {
            const checkbox = document.getElementById('clienteCrearUsuario');
            const fields = document.getElementById('usuarioFields');
            fields.style.display = checkbox.checked ? 'block' : 'none';

            // Hacer campos requeridos o no según el checkbox
            const usernameInput = document.getElementById('clienteUsername');
            const passwordInput = document.getElementById('clientePassword');
            const passwordConfirmInput = document.getElementById('clientePasswordConfirm');

            if (checkbox.checked) {
                usernameInput.required = true;
                passwordInput.required = true;
                passwordConfirmInput.required = true;
            } else {
                usernameInput.required = false;
                passwordInput.required = false;
                passwordConfirmInput.required = false;
            }
        }

        async guardarCliente(event) {
            event.preventDefault();

            // Obtener valores del formulario
            const razonSocial = document.getElementById('clienteRazonSocial').value.trim();
            const rut = document.getElementById('clienteRut').value.trim();
            const email = document.getElementById('clienteEmail').value.trim();
            const telefono = document.getElementById('clienteTelefono').value.trim();
            const direccion = document.getElementById('clienteDireccion').value.trim();
            const nombreCliente = document.getElementById('clienteNombre').value.trim();

            const crearUsuario = document.getElementById('clienteCrearUsuario').checked;
            const username = document.getElementById('clienteUsername').value.trim();
            const password = document.getElementById('clientePassword').value;
            const passwordConfirm = document.getElementById('clientePasswordConfirm').value;

            // Validaciones
            if (!razonSocial || !rut || !email) {
                showNotification('Completa todos los campos obligatorios', 'error');
                return;
            }

            if (crearUsuario) {
                if (!username || !password) {
                    showNotification('Completa los datos de usuario', 'error');
                    return;
                }

                if (password !== passwordConfirm) {
                    showNotification('Las contraseñas no coinciden', 'error');
                    return;
                }

                if (password.length < 6) {
                    showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                    return;
                }
            }

            try {
                showLoading();

                // Preparar datos
                const clienteData = {
                    razon_social: razonSocial,
                    rut_empresa: rut,
                    email: email,
                    telefono: telefono || null,
                    direccion: direccion || null,
                    nombre_cliente: nombreCliente || null,
                    empresa: razonSocial, // Para compatibilidad con diferentes esquemas
                    correo_contacto: email // Para compatibilidad
                };

                if (crearUsuario) {
                    clienteData.crear_usuario = true;
                    clienteData.username = username;
                    clienteData.password = password;
                }

                // Enviar al backend
                const response = await makeRequest(`${this.baseURL}/admin/clientes/create`, {
                    method: 'POST',
                    body: JSON.stringify(clienteData)
                });

                if (response.success) {
                    showNotification(response.message || 'Cliente creado exitosamente', 'success');
                    this.closeModal('agregarClienteModal');
                    this.loadClientes(); // Recargar lista de clientes
                } else {
                    showNotification(response.message || 'Error al crear cliente', 'error');
                }

            } catch (error) {
                console.error('Error guardando cliente:', error);
                showNotification('Error al guardar el cliente: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Ver detalle del cliente
        async verDetalleCliente(clienteId) {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/clientes/${clienteId}`);

                let cliente;
                if (response.success && response.data) {
                    cliente = response.data;
                } else if (response && !response.error) {
                    cliente = response;
                } else {
                    throw new Error(response.message || 'Cliente no encontrado');
                }

                // Poblar modal con datos
                document.getElementById('detalleId').textContent = cliente.id || '-';
                document.getElementById('detalleRazonSocial').textContent = cliente.razon_social || cliente.empresa || '-';
                document.getElementById('detalleRut').textContent = cliente.rut_empresa || '-';
                document.getElementById('detalleEmail').textContent = cliente.email || cliente.correo_contacto || cliente.cliente_email || '-';
                document.getElementById('detalleTelefono').textContent = cliente.telefono || '-';
                document.getElementById('detalleDireccion').textContent = cliente.direccion || '-';
                document.getElementById('detalleNombre').textContent = cliente.nombre_cliente || '-';
                document.getElementById('detalleUsername').textContent = cliente.username || 'No tiene usuario';
                document.getElementById('detalleFecha').textContent = cliente.created_at ? new Date(cliente.created_at).toLocaleDateString() : '-';

                this.showModal('detalleClienteModal');

            } catch (error) {
                console.error('Error cargando detalle cliente:', error);
                showNotification('Error al cargar detalle: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Editar cliente
        async editarCliente(clienteId) {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/clientes/${clienteId}`);

                let cliente;
                if (response.success && response.data) {
                    cliente = response.data;
                } else if (response && !response.error) {
                    cliente = response;
                } else {
                    throw new Error(response.message || 'Cliente no encontrado');
                }

                // Poblar formulario de edición
                document.getElementById('editClienteId').value = cliente.id;
                document.getElementById('editClienteRazonSocial').value = cliente.razon_social || cliente.empresa || '';
                document.getElementById('editClienteRut').value = cliente.rut_empresa || '';
                document.getElementById('editClienteEmail').value = cliente.email || cliente.correo_contacto || cliente.cliente_email || '';
                document.getElementById('editClienteTelefono').value = cliente.telefono || '';
                document.getElementById('editClienteDireccion').value = cliente.direccion || '';
                document.getElementById('editClienteNombre').value = cliente.nombre_cliente || '';

                this.showModal('editarClienteModal');

                // Bind submit event
                document.getElementById('editarClienteForm').onsubmit = (e) => this.guardarEdicionCliente(e);

            } catch (error) {
                console.error('Error cargando cliente para editar:', error);
                showNotification('Error al cargar cliente: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Guardar edición de cliente
        async guardarEdicionCliente(event) {
            event.preventDefault();

            const clienteId = document.getElementById('editClienteId').value;
            const razonSocial = document.getElementById('editClienteRazonSocial').value.trim();
            const rut = document.getElementById('editClienteRut').value.trim();
            const email = document.getElementById('editClienteEmail').value.trim();
            const telefono = document.getElementById('editClienteTelefono').value.trim();
            const direccion = document.getElementById('editClienteDireccion').value.trim();
            const nombreCliente = document.getElementById('editClienteNombre').value.trim();

            if (!razonSocial || !rut || !email) {
                showNotification('Completa todos los campos obligatorios', 'error');
                return;
            }

            try {
                showLoading();

                const clienteData = {
                    razon_social: razonSocial,
                    rut_empresa: rut,
                    email: email,
                    telefono: telefono || null,
                    direccion: direccion || null,
                    nombre_cliente: nombreCliente || null,
                    empresa: razonSocial,
                    correo_contacto: email
                };

                const response = await makeRequest(`${this.baseURL}/admin/clientes/${clienteId}`, {
                    method: 'PUT',
                    body: JSON.stringify(clienteData)
                });

                if (response.success) {
                    showNotification(response.message || 'Cliente actualizado exitosamente', 'success');
                    this.closeModal('editarClienteModal');
                    this.loadClientes();
                } else {
                    showNotification(response.message || 'Error al actualizar cliente', 'error');
                }

            } catch (error) {
                console.error('Error actualizando cliente:', error);
                showNotification('Error al actualizar: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Eliminar cliente
        eliminarCliente(clienteId, clienteNombre) {
            this.currentClienteEliminarId = clienteId;
            document.getElementById('eliminarClienteNombre').textContent = clienteNombre;
            document.getElementById('eliminarClientePassword').value = '';
            this.showModal('eliminarClienteModal');

            // Bind click event
            document.getElementById('confirmarEliminarClienteBtn').onclick = () => this.confirmarEliminarCliente();
        }

        // Confirmar eliminación de cliente
        async confirmarEliminarCliente() {
            const password = document.getElementById('eliminarClientePassword').value;

            if (!password) {
                showNotification('Ingresa tu contraseña de administrador', 'warning');
                return;
            }

            console.log('Intentando eliminar cliente ID:', this.currentClienteEliminarId);
            console.log('Password ingresada:', password ? '***' : 'vacía');

            try {
                showLoading();

                const url = `${this.baseURL}/admin/clientes/${this.currentClienteEliminarId}`;
                console.log('URL de eliminación:', url);

                const payload = { admin_password: password };
                console.log('Payload:', payload);

                // Hacer fetch manualmente para capturar el error completo
                const token = localStorage.getItem('admin_token');
                const fetchResponse = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'X-Access-Token': token
                    },
                    body: JSON.stringify(payload)
                });

                console.log('Response status:', fetchResponse.status);
                console.log('Response ok:', fetchResponse.ok);

                const responseText = await fetchResponse.text();
                console.log('Response text:', responseText);

                let response;
                try {
                    response = JSON.parse(responseText);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    throw new Error('Respuesta inválida del servidor: ' + responseText);
                }

                console.log('Response parsed:', response);

                if (response.success) {
                    showNotification(response.message || 'Cliente eliminado exitosamente', 'success');
                    this.closeModal('eliminarClienteModal');
                    this.loadClientes();
                } else {
                    showNotification(response.message || response.error || 'Error al eliminar cliente', 'error');
                }

            } catch (error) {
                console.error('Error completo eliminando cliente:', error);
                showNotification('Error al eliminar: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        // Gestión de Administradores
        mostrarModalCrearAdmin() {
            document.getElementById('crearAdminForm').reset();
            this.showModal('crearAdminModal');

            // Bind submit event
            document.getElementById('crearAdminForm').onsubmit = (e) => this.crearAdministrador(e);

            // Cargar lista de administradores
            this.loadAdministradores();
        }

        async loadAdministradores() {
            try {
                const response = await makeRequest(`${this.baseURL}/admin/usuarios`);
                const container = document.getElementById('listaAdministradores');

                if (response.success && response.data && response.data.length > 0) {
                    // Filtrar solo administradores
                    const admins = response.data.filter(user => user.role === 'admin');

                    if (admins.length > 0) {
                        container.innerHTML = admins.map(admin => {
                            const isActive = admin.is_active;
                            const statusClass = isActive ? 'success' : 'danger';
                            const statusText = isActive ? 'Activo' : 'Inactivo';

                            return `
                            <div class="admin-item" style="padding: 1rem; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 0.75rem; background: #f9f9f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h6 style="margin: 0 0 0.25rem 0; color: #1a365d;">
                                            <i class="fas fa-user-shield"></i> ${admin.username}
                                        </h6>
                                        <small style="color: #666; display: block;">
                                            <i class="fas fa-envelope"></i> ${admin.email || 'Sin email'}
                                        </small>
                                        <small style="color: #999; display: block; margin-top: 0.25rem;">
                                            <i class="fas fa-calendar"></i> Creado: ${new Date(admin.created_at).toLocaleDateString()}
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge badge-${statusClass}" style="padding: 0.5rem 1rem; border-radius: 20px; background: ${isActive ? '#28a745' : '#dc3545'}; color: white; font-size: 0.85rem;">
                                            ${statusText}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                        }).join('');
                    } else {
                        container.innerHTML = '<p class="text-muted">No hay otros administradores en el sistema.</p>';
                    }
                } else {
                    container.innerHTML = '<p class="text-muted">No se pudieron cargar los administradores.</p>';
                }
            } catch (error) {
                console.error('Error loading administradores:', error);
                document.getElementById('listaAdministradores').innerHTML = '<p class="text-danger">Error al cargar administradores.</p>';
            }
        }

        async crearAdministrador(event) {
            event.preventDefault();

            const username = document.getElementById('adminUsername').value.trim();
            const email = document.getElementById('adminEmail').value.trim();
            const password = document.getElementById('adminPassword').value;
            const passwordConfirm = document.getElementById('adminPasswordConfirm').value;

            // Validaciones
            if (!username || !email || !password) {
                showNotification('Completa todos los campos obligatorios', 'error');
                return;
            }

            // Validar username
            const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
            if (!usernameRegex.test(username)) {
                showNotification('El nombre de usuario debe tener entre 3-20 caracteres (solo letras, números y guiones bajos)', 'error');
                return;
            }

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showNotification('El email no es válido', 'error');
                return;
            }

            // Validar contraseña
            if (password.length < 6) {
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }

            if (password !== passwordConfirm) {
                showNotification('Las contraseñas no coinciden', 'error');
                return;
            }

            try {
                showLoading();

                const adminData = {
                    username: username,
                    email: email,
                    password: password,
                    role: 'admin'
                };

                const response = await makeRequest(`${this.baseURL}/admin/usuarios/create`, {
                    method: 'POST',
                    body: JSON.stringify(adminData)
                });

                if (response.success) {
                    showNotification('Administrador creado exitosamente', 'success');

                    // Recargar lista de administradores
                    this.loadAdministradores();

                    // Limpiar formulario
                    document.getElementById('crearAdminForm').reset();

                    // Mostrar información del nuevo admin
                    setTimeout(() => {
                        showNotification(`Usuario: ${username} creado con éxito. Ya puede iniciar sesión.`, 'success');
                    }, 2000);
                } else {
                    showNotification(response.message || 'Error al crear administrador', 'error');
                }

            } catch (error) {
                console.error('Error creando administrador:', error);
                showNotification('Error al crear administrador: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }
    }

    // Función para obtener token de sesión de manera confiable (Admin)
    async function setupAdminSessionToken() {
        try {
            // Intentar usar token generado por PHP primero
            <?php if ($jwtToken && $userData): ?>
            localStorage.setItem('admin_token', '<?= $jwtToken ?>');
            localStorage.setItem('admin_userInfo', '<?= addslashes(json_encode($userData)) ?>');
            console.log('Token JWT Admin configurado desde PHP');
            return true;
            <?php endif; ?>

            // Si no hay token PHP, obtenerlo via API (fallback para producción)
            console.log('Obteniendo token admin via API como fallback...');
            const response = await fetch('/api/auth/session-token', {
                method: 'GET',
                credentials: 'include', // Importante para incluir cookies de sesión
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.token && data.user && data.user.role === 'admin') {
                    localStorage.setItem('admin_token', data.token);
                    localStorage.setItem('admin_userInfo', JSON.stringify(data.user));
                    console.log('Token admin configurado via API');
                    return true;
                }
            } else {
                console.error('Error obteniendo token admin:', await response.text());
            }

            return false;
        } catch (error) {
            console.error('Error configurando token admin:', error);
            return false;
        }
    }

    // Inicializar dashboard admin después de configurar el token
    setupAdminSessionToken().then(tokenReady => {
        if (!tokenReady) {
            console.warn('No se pudo configurar el token admin, redirigiendo al login...');
            window.location.href = '/';
            return;
        }

        // Initialize admin dashboard
        const adminDashboard = new AdminDashboard();

        // Make adminDashboard globally available
        window.adminDashboard = adminDashboard;
    }).catch(error => {
        console.error('Error inicializando dashboard admin:', error);
        window.location.href = '/';
    });
    </script>
</body>
</html>