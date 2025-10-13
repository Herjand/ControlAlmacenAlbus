<?php
// header_admin.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

$usuario = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Albus S.R.L.</title>

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
            height: 100vh;
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
        .sidebar a:hover, .sidebar a.active {
            background-color: #0d6efd;
            border-left: 4px solid #00c3ff;
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
            min-height: 100vh;
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
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index_admin.php">
                <i class="bi bi-box-seam"></i> Albus S.R.L. - Administración
            </a>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo $usuario; ?> (<?php echo $rol; ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR ACTUALIZADO -->
    <div class="sidebar">
        <!-- ENLACE INICIO - PRIMER ELEMENTO -->
        <a href="index_admin.php" class="active">
            <i class="bi bi-house-door"></i> Inicio
        </a>

        <!-- INVENTARIO -->
        <div class="sidebar-section">
            <i class="bi bi-clipboard-data"></i> INVENTARIO
        </div>
        <a href="productos_admin.php" class="active"><i class="bi bi-box"></i> Productos</a>
        <a href="entradas_admin.php"><i class="bi bi-arrow-down-square"></i> Entradas</a>
        <a href="salidas_admin.php"><i class="bi bi-arrow-up-square"></i> Salidas</a>
        <a href="stock_admin.php"><i class="bi bi-graph-up"></i> Stock</a>

        <!-- PEDIDOS -->
        <div class="sidebar-section">
            <i class="bi bi-cart-check"></i> PEDIDOS
        </div>
        <a href="pedidos_admin.php"><i class="bi bi-list-check"></i> Pedidos</a>
        <a href="clientes_admin.php"><i class="bi bi-people"></i> Clientes</a>

        <!-- REPORTES -->
        <div class="sidebar-section">
            <i class="bi bi-bar-chart"></i> REPORTES
        </div>
        <a href="movimientos_admin.php"><i class="bi bi-arrow-left-right"></i> Movimientos</a>
        <a href="stockcritico_admin.php"><i class="bi bi-exclamation-triangle"></i> Stock Crítico</a>
        <a href="auditoria_admin.php"><i class="bi bi-activity"></i> Auditoría</a>

        <div class="sidebar-divider"></div>

        <!-- AYUDA -->
        <div class="sidebar-section">
            <i class="bi bi-question-circle"></i> AYUDA
        </div>
        <a href="acerca_admin.php"><i class="bi bi-info-circle"></i> Acerca de nosotros</a>
        <a href="uso_loteadora_admin.php"><i class="bi bi-gear-fill"></i> Uso correcto de loteadora</a>
        <a href="uso_selladoras_admin.php"><i class="bi bi-tag-fill"></i> Uso correcto de selladoras</a>
        <a href="croquis_admin.php"><i class="bi bi-map"></i> Croquis del almacén</a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="content">