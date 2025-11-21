<?php
// header_jefe_produccion.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$usuario = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];

if ($rol !== 'Jefe de Producción') {
    header("Location: ../acceso_denegado.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Calcular URL base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'ControlAlmacenAlbus';
$base_url = $protocol . "://" . $host . "/" . $project_folder . "/jefeprod/";
$navbar_url = $base_url . "navbar_usuarios/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Jefe de Producción | Albus S.A.</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #2c3e50;
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
            background-color: #34495e;
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
            background-color: #3498db;
            border-left: 4px solid #1abc9c;
        }
        .sidebar a.active {
            background-color: #3498db;
            border-left: 4px solid #1abc9c;
            font-weight: 500;
        }
        .sidebar-section {
            color: #bdc3c7;
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
            background: #7f8c8d;
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #95a5a6;
        }
        .badge-jefe {
            background-color: #e67e22;
            font-size: 0.7em;
        }
        .jefe-highlight {
            border-left: 4px solid #e67e22 !important;
        }
    </style>
</head>
<body>
<!-- NAV SUPERIOR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #2c3e50;">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $base_url; ?>index_jefe_produccion.php">
            <i class="bi bi-gear-fill"></i> Albus S.A. - Jefe de Producción
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUserMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarUserMenu">
            <ul class="navbar-nav ms-auto">
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
                                        <i class="bi bi-person-gear fs-4 text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($usuario); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($rol); ?></small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- PERFIL COMPLETO COMO ESTABA -->
                        <li>
                            <a class="dropdown-item" href="<?php echo $navbar_url; ?>mi_perfil.php">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $navbar_url; ?>editar_perfil.php">
                                <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $navbar_url; ?>cambiar_password.php">
                                <i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña
                            </a>
                        </li>
                                            
                        <!-- Cerrar sesión -->
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo $navbar_url; ?>procesar_logout.php" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <!-- SIDEBAR SIMPLIFICADO SOLO PARA ENTRADAS, SALIDAS Y STOCK MÍNIMO -->
    <div class="sidebar">
        <!-- ENLACE INICIO -->
        <a href="<?php echo $base_url; ?>index_jefe_produccion.php" class="<?php echo ($current_page == 'index_jefe_produccion.php') ? 'active jefe-highlight' : ''; ?>">
            <i class="bi bi-house-door"></i> Inicio
        </a>

        <!-- GESTIÓN BÁSICA -->
        <div class="sidebar-section">
            <i class="bi bi-clipboard-data"></i> GESTIÓN BÁSICA
        </div>
        <a href="<?php echo $base_url; ?>gestion_entradas.php" class="<?php echo ($current_page == 'gestion_entradas.php') ? 'active jefe-highlight' : ''; ?>">
            <i class="bi bi-arrow-down-square"></i> Gestionar Entradas
        </a>
        <a href="<?php echo $base_url; ?>gestion_salidas.php" class="<?php echo ($current_page == 'gestion_salidas.php') ? 'active jefe-highlight' : ''; ?>">
            <i class="bi bi-arrow-up-square"></i> Gestionar Salidas
        </a>
        <a href="<?php echo $base_url; ?>ajustar_stock_minimo.php" class="<?php echo ($current_page == 'ajustar_stock_minimo.php') ? 'active jefe-highlight' : ''; ?>">
            <i class="bi bi-sliders"></i> Ajustar Stock Mínimo
        </a>

        <!-- CONSULTAS BÁSICAS -->
        <div class="sidebar-section">
            <i class="bi bi-search"></i> CONSULTAS
        </div>
        <a href="<?php echo $base_url; ?>stock_completo.php" class="<?php echo ($current_page == 'stock_completo.php') ? 'active jefe-highlight' : ''; ?>">
            <i class="bi bi-boxes"></i> Ver Stock
        </a>
        <a href="<?php echo $base_url; ?>movimientos_totales.php" class="<?php echo ($current_page == 'movimientos_totales.php') ? 'active jefe-highlight' : ''; ?>">
            <i class="bi bi-list-check"></i> Ver Movimientos
        </a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="content">
        <!-- El contenido específico de cada página irá aquí -->