<?php
// header.php
session_start();
$usuario = "Andre Fernandez"; // Simulación temporal del usuario logueado
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
        }
        .navbar {
            background-color: #212529;
            z-index: 1030; /* Para que esté sobre el contenido */
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
            background-color: #343a40;
            padding-top: 20px;
            position: fixed;
            width: 230px;
            top: 56px; /* Altura del navbar */
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: #fff;
        }
        .content {
            margin-left: 230px; /* Para no chocar con sidebar */
            padding: 20px;
            padding-top: 76px; /* Para no chocar con navbar */
        }
    </style>
</head>
<body>
    <!-- NAV SUPERIOR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-box-seam"></i> Albus S.R.L.
            </a>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo $usuario; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Editar perfil</a></li>
                        <li><a class="dropdown-item" href="#">Cerrar sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <a href="index_admin.php"><i class="bi bi-house-door"></i> Inicio</a>
        <a href="productos_admin.php"><i class="bi bi-box"></i> Productos</a>
        <a href="pedidos_admin.php"><i class="bi bi-cart-check"></i> Pedidos</a>
        <a href="entradas_admin.php"><i class="bi bi-arrow-down-square"></i> Entradas</a>
        <a href="salidas.php"><i class="bi bi-arrow-up-square"></i> Salidas</a>
        <a href="despachos.php"><i class="bi bi-truck"></i> Despachos</a>
        <a href="logs.php"><i class="bi bi-activity"></i> Logs</a>
        <hr class="text-secondary">
        <a href="acerca.php"><i class="bi bi-info-circle"></i> Acerca de nosotros</a>
        <a href="uso_loteadora.php"><i class="bi bi-gear"></i> Uso correcto de loteadora</a>
        <a href="croquis.php"><i class="bi bi-map"></i> Croquis del almacén</a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="content">
