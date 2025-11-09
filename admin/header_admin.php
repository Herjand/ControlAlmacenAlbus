<?php
// header_admin.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$usuario = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];

// Determinar la página actual para marcar como activa
$current_page = basename($_SERVER['PHP_SELF']);

// Calcular la URL base automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'ControlAlmacenAlbus'; // Cambia esto si tu proyecto está en subcarpeta
$base_url = $protocol . "://" . $host . "/" . $project_folder . "/admin/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Albus S.A.</title>
    
    <!-- URL Base para todos los enlaces relativos -->
    <base href="<?php echo $base_url; ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #212529;
            z-index: 1030;
            position: fixed;
            top: 0;
            width: 100%;
            height: 56px;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: bold;
        }
        .navbar-nav .nav-link {
            color: #ddd !important;
        }
        .navbar-nav .nav-link:hover {
            color: #fff !important;
        }
        .sidebar {
            height: calc(100vh - 56px);
            background-color: #1e2a38;
            position: fixed;
            width: 250px;
            top: 56px;
            overflow-y: auto;
        }
        .sidebar a {
            color: #fff;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: 0.3s;
            font-size: 0.9rem;
        }
        .sidebar a:hover {
            background-color: #0d6efd;
            border-left: 4px solid #00c3ff;
        }
        .sidebar a.active {
            background-color: #0d6efd;
            border-left: 4px solid #00c3ff;
            font-weight: 500;
        }
        .sidebar-section {
            color: #6c757d;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            padding: 12px 20px 5px 20px;
            border-bottom: 1px solid #2c3e50;
        }
        .sidebar-divider {
            border-top: 1px solid #2c3e50;
            margin: 10px 20px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 76px;
            min-height: calc(100vh - 76px);
        }
        .sidebar .bi {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        /* Scroll personalizado para sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
    </style>
</head>
<body>
<!-- NAV SUPERIOR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #212529;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index_admin.php">
            <i class="bi bi-box-seam"></i> Albus S.A. - Administración
        </a>

        <!-- Botón toggle para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUserMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarUserMenu">
            <ul class="navbar-nav ms-auto">
                <!-- Notificaciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell text-white"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                            <span class="visually-hidden">notificaciones</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notificaciones</h6></li>
                        <li><a class="dropdown-item" href="#"><small>Nuevo pedido recibido</small></a></li>
                        <li><a class="dropdown-item" href="#"><small>Stock crítico en productos</small></a></li>
                        <li><a class="dropdown-item" href="#"><small>Auditoría pendiente</small></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#"><small>Ver todas</small></a></li>
                    </ul>
                </li>

                <!-- Usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> 
                        <?php 
                        echo htmlspecialchars($usuario); 
                        if(isset($rol) && !empty($rol)) {
                            echo " (" . htmlspecialchars($rol) . ")";
                        }
                        ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <!-- Información del usuario -->
                        <li>
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-person-badge fs-4 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($usuario); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($rol); ?></small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Enlaces de perfil -->
                        <li>
                            <a class="dropdown-item" href="navbar_usuarios/mi_perfil.php">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="navbar_usuarios/editar_perfil.php">
                                <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="navbar_usuarios/cambiar_password.php">
                                <i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña
                            </a>
                        </li>
                        
                        <!-- Cerrar sesión -->
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="navbar_usuarios/procesar_logout.php" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <!-- ENLACE INICIO - PRIMER ELEMENTO -->
        <a href="index_admin.php" class="<?php echo ($current_page == 'index_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i> Inicio
        </a>

        <!-- INVENTARIO -->
        <div class="sidebar-section">
            <i class="bi bi-clipboard-data"></i> INVENTARIO
        </div>
        <a href="productos_admin.php" class="<?php echo ($current_page == 'productos_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-box"></i> Productos
        </a>
        <a href="quimicos_admin.php" class="<?php echo ($current_page == 'quimicos_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-droplet"></i> Químicos
        </a>
        <a href="envases_admin.php" class="<?php echo ($current_page == 'envases_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-box-seam"></i> Envases
        </a>
        <a href="entradas_admin.php" class="<?php echo ($current_page == 'entradas_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-down-square"></i> Entradas
        </a>
        <a href="salidas_admin.php" class="<?php echo ($current_page == 'salidas_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-up-square"></i> Salidas
        </a>
        <a href="stock_admin.php" class="<?php echo ($current_page == 'stock_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-graph-up"></i> Stock
        </a>

        <!-- PEDIDOS -->
        <div class="sidebar-section">
            <i class="bi bi-cart-check"></i> PEDIDOS
        </div>
        <a href="clientes_admin.php" class="<?php echo ($current_page == 'clientes_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> Clientes
        </a>

        <a href="pedidos_admin.php" class="<?php echo ($current_page == 'pedidos_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-list-check"></i> Pedidos
        </a>
        
        <!-- REPORTES -->
        <div class="sidebar-section">
            <i class="bi bi-bar-chart"></i> REPORTES
        </div>
        <a href="movimientos_admin.php" class="<?php echo ($current_page == 'movimientos_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-left-right"></i> Movimientos
        </a>
        <a href="stockcritico_admin.php" class="<?php echo ($current_page == 'stockcritico_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-exclamation-triangle"></i> Stock Crítico
        </a>
        <a href="auditoria_admin.php" class="<?php echo ($current_page == 'auditoria_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-activity"></i> Auditoría
        </a>

        <div class="sidebar-divider"></div>

        <!-- AYUDA -->
        <div class="sidebar-section">
            <i class="bi bi-question-circle"></i> AYUDA
        </div>
        <a href="acerca_admin.php" class="<?php echo ($current_page == 'acerca_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-info-circle"></i> Acerca de nosotros
        </a>
        <a href="uso_loteadora_admin.php" class="<?php echo ($current_page == 'uso_loteadora_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-gear-fill"></i> Uso correcto de loteadora
        </a>
        <a href="uso_selladoras_admin.php" class="<?php echo ($current_page == 'uso_selladoras_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-tag-fill"></i> Uso correcto de selladoras
        </a>
        <a href="croquis_admin.php" class="<?php echo ($current_page == 'croquis_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-map"></i> Croquis del almacén
        </a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="content">
        <!-- El contenido específico de cada página irá aquí -->