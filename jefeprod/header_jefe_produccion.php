<?php
// header_jefe_produccion.php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
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
$base_url = $protocol . "://" . $host . "/" . $project_folder . "/jefe_produccion/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Jefe Producción | Albus S.A.</title>
    
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
            background-color: #27ae60;
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
            background-color: #2ecc71;
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
            background-color: #229954;
            border-left: 4px solid #145a32;
        }
        .sidebar a.active {
            background-color: #229954;
            border-left: 4px solid #145a32;
            font-weight: 500;
        }
        .sidebar-section {
            color: #d5f5e3;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            padding: 12px 20px 5px 20px;
            border-bottom: 1px solid #229954;
        }
        .sidebar-divider {
            border-top: 1px solid #229954;
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
            background: #229954;
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #1e8449;
        }
        .badge-jefe {
            background-color: #f39c12;
            font-size: 0.7em;
        }
        .alert-stock {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
<!-- NAV SUPERIOR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #27ae60;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index_jefe_produccion.php">
            <i class="bi bi-clipboard-data"></i> Albus S.A. - Jefe Producción
        </a>

        <!-- Botón toggle para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUserMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarUserMenu">
            <ul class="navbar-nav ms-auto">

                <!-- Usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-gear"></i> 
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
                                        <i class="bi bi-person-badge fs-4 text-success"></i>
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
        <a href="index_jefe_produccion.php" class="<?php echo ($current_page == 'index_jefe_produccion.php') ? 'active' : ''; ?>">
            <i class="bi bi-house-door"></i> Inicio
        </a>

        <!-- SUPERVISIÓN Y CONTROL -->
        <div class="sidebar-section">
            <i class="bi bi-eye"></i> SUPERVISIÓN
        </div>
        <a href="entradas_jefe.php" class="<?php echo ($current_page == 'entradas_jefe.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-down-square"></i> Control Entradas
        </a>
        <a href="salidas_jefe.php" class="<?php echo ($current_page == 'salidas_jefe.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-up-square"></i> Control Salidas
        </a>
        <a href="movimientos_jefe.php" class="<?php echo ($current_page == 'movimientos_jefe.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-left-right"></i> Movimientos Totales
        </a>

        <!-- STOCK Y SEGURIDAD -->
        <div class="sidebar-section">
            <i class="bi bi-shield-check"></i> STOCK SEGURO
        </div>
        <a href="stock_seguridad.php" class="<?php echo ($current_page == 'stock_seguridad.php') ? 'active' : ''; ?>">
            <i class="bi bi-graph-up-arrow"></i> Stock de Seguridad
        </a>
        <a href="alertas_stock.php" class="<?php echo ($current_page == 'alertas_stock.php') ? 'active' : ''; ?>">
            <i class="bi bi-exclamation-triangle"></i> Alertas Stock
        </a>
        <a href="productos_jefe.php" class="<?php echo ($current_page == 'productos_jefe.php') ? 'active' : ''; ?>">
            <i class="bi bi-box-seam"></i> Catálogo Productos
        </a>

        <!-- PEDIDOS Y PRODUCCIÓN -->
        <div class="sidebar-section">
            <i class="bi bi-clipboard-check"></i> PRODUCCIÓN
        </div>
        <a href="pedidos_jefe.php" class="<?php echo ($current_page == 'pedidos_jefe.php') ? 'active' : ''; ?>">
            <i class="bi bi-list-check"></i> Estado Pedidos
        </a>
        <a href="planificacion_produccion.php" class="<?php echo ($current_page == 'planificacion_produccion.php') ? 'active' : ''; ?>">
            <i class="bi bi-calendar-check"></i> Planificación
        </a>

        <!-- REPORTES Y ANÁLISIS -->
        <div class="sidebar-section">
            <i class="bi bi-bar-chart"></i> REPORTES
        </div>
        <a href="reportes_entradas_salidas.php" class="<?php echo ($current_page == 'reportes_entradas_salidas.php') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text"></i> Entradas/Salidas
        </a>
        <a href="reportes_stock.php" class="<?php echo ($current_page == 'reportes_stock.php') ? 'active' : ''; ?>">
            <i class="bi bi-graph-up"></i> Análisis Stock
        </a>
        <a href="reportes_eficiencia.php" class="<?php echo ($current_page == 'reportes_eficiencia.php') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Eficiencia
        </a>

        <div class="sidebar-divider"></div>

        <!-- CONFIGURACIÓN -->
        <div class="sidebar-section">
            <i class="bi bi-gear"></i> CONFIGURACIÓN
        </div>
        <a href="configuracion_stock.php" class="<?php echo ($current_page == 'configuracion_stock.php') ? 'active' : ''; ?>">
            <i class="bi bi-sliders"></i> Stock Mínimo
        </a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="content">
        <!-- El contenido específico de cada página irá aquí -->