<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    // Si ya está logueado, redirigir según su rol
    if ($_SESSION['usuario_rol'] == 'Administrador') {
        header("Location: admin/index_admin.php");
    } else {
        header("Location: operario/index_operario.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Albus S.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        .card-hover {
            transition: transform 0.3s ease;
            border: 1px solid #e9ecef;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: bold;
            color: #2c3e50 !important;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            display: inline-block;
        }
        .role-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        .logo {
            height: 40px;
            width: auto;
        }
        .hero-logo {
            height: 80px;
            width: auto;
            margin-bottom: 2rem;
        }
        .map-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .contact-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="imagenes/logo_albus.png" alt="Albus S.A." class="logo me-2">
                Albus S.A.
            </a>
            <div class="navbar-nav ms-auto">
                <a href="login.php" class="btn btn-outline-primary me-2">Iniciar Sesión</a>
                <a href="registro.php" class="btn btn-primary">Registrar Usuario</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-4">
                        Sistema de Gestión Interna
                    </h1>
                    <p class="lead mb-4">
                        Plataforma integral para la gestión de almacén, control de inventario 
                        y administración de procesos operativos de Albus S.A.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="login.php" class="btn btn-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acceder al Sistema
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-info-circle me-2"></i>Conocer el Sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title fw-bold mb-4">Sobre Nuestro Sistema</h2>
                    <p class="lead">
                        El Sistema de Gestión de Albus S.A. es una plataforma desarrollada internamente 
                        para optimizar y controlar todos los procesos operativos de la empresa.
                    </p>
                    <p>
                        Diseñado específicamente para nuestras necesidades, integra la gestión de 
                        inventarios, control de pedidos, seguimiento de producción y reportes 
                        operativos en una sola plataforma unificada.
                    </p>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="imagenes/logo_albus.png" alt="Albus S.A." class="img-fluid" style="max-height: 200px; opacity: 0.8;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title fw-bold mb-4">Características del Sistema</h2>
                    <p class="text-muted">Funcionalidades desarrolladas para optimizar nuestras operaciones</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 card-hover p-4 text-center">
                        <i class="bi bi-clipboard-data feature-icon"></i>
                        <h5 class="fw-bold">Gestión de Inventarios</h5>
                        <p class="text-muted">
                            Control completo de stock, entradas, salidas y alertas automáticas 
                            de productos con stock bajo.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-primary role-badge">Operarios</span>
                            <span class="badge bg-success role-badge">Jefes</span>
                            <span class="badge bg-info role-badge">Admin</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 card-hover p-4 text-center">
                        <i class="bi bi-truck feature-icon"></i>
                        <h5 class="fw-bold">Control de Pedidos</h5>
                        <p class="text-muted">
                            Seguimiento integral de pedidos desde la recepción hasta la entrega, 
                            con gestión de estados y prioridades.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-primary role-badge">Operarios</span>
                            <span class="badge bg-success role-badge">Jefes</span>
                            <span class="badge bg-info role-badge">Admin</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 card-hover p-4 text-center">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h5 class="fw-bold">Reportes Operativos</h5>
                        <p class="text-muted">
                            Generación de reportes detallados y dashboards para análisis 
                            de rendimiento y toma de decisiones.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-success role-badge">Jefes</span>
                            <span class="badge bg-info role-badge">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title fw-bold mb-4">Módulos Principales</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card card-hover p-4">
                        <div class="card-body">
                            <h5 class="fw-bold">
                                <i class="bi bi-box-arrow-in-down text-primary me-2"></i>
                                Gestión de Entradas
                            </h5>
                            <p class="text-muted mb-0">
                                Registro y control de todas las entradas de productos al almacén 
                                con validación y documentación.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-hover p-4">
                        <div class="card-body">
                            <h5 class="fw-bold">
                                <i class="bi bi-box-arrow-up text-warning me-2"></i>
                                Control de Salidas
                            </h5>
                            <p class="text-muted mb-0">
                                Gestión de salidas con verificación de stock, autorizaciones 
                                y generación de documentación.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-hover p-4">
                        <div class="card-body">
                            <h5 class="fw-bold">
                                <i class="bi bi-cart-check text-success me-2"></i>
                                Preparación de Pedidos
                            </h5>
                            <p class="text-muted mb-0">
                                Proceso optimizado para la preparación y verificación de pedidos 
                                antes del despacho.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-hover p-4">
                        <div class="card-body">
                            <h5 class="fw-bold">
                                <i class="bi bi-clipboard-check text-info me-2"></i>
                                Seguimiento de Producción
                            </h5>
                            <p class="text-muted mb-0">
                                Monitoreo y control de los procesos productivos y su relación 
                                con el inventario.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title fw-bold mb-4">Tipos de Usuario</h2>
                    <p class="text-muted">Niveles de acceso según las responsabilidades en Albus S.A.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 card-hover text-center p-4">
                        <i class="bi bi-person-gear display-6 text-info mb-3"></i>
                        <h5 class="fw-bold">Administrador</h5>
                        <p class="text-muted">
                            Acceso completo al sistema, gestión de usuarios, configuración 
                            global y reportes ejecutivos.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-info">Acceso Total</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 card-hover text-center p-4">
                        <i class="bi bi-person-check display-6 text-success mb-3"></i>
                        <h5 class="fw-bold">Jefe de Producción</h5>
                        <p class="text-muted">
                            Supervisión de operaciones, gestión de pedidos, reportes 
                            operativos y control de procesos.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-success">Supervisión</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 card-hover text-center p-4">
                        <i class="bi bi-person display-6 text-primary mb-3"></i>
                        <h5 class="fw-bold">Operario</h5>
                        <p class="text-muted">
                            Gestión diaria de inventario, registro de movimientos, 
                            preparación de pedidos y operaciones básicas.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-primary">Operaciones</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title fw-bold mb-4">Nuestra Ubicación</h2>
                    <p class="text-muted">Visítanos en nuestras instalaciones principales</p>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2700.0640910206052!2d-68.15322092485535!3d-16.479527984260947!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x915edffa4a1cbd99%3A0x4b891cbf194b557b!2sAlbus%20SA!5e1!3m2!1ses-419!2sbo!4v1762381775500!5m2!1ses-419!2sbo" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact-info h-100">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            Albus S.A.
                        </h4>
                        <div class="mb-3">
                            <strong><i class="bi bi-geo-fill me-2"></i>Dirección:</strong>
                            <p class="mb-1">Pura Pura Av. Vasquez</p>
                            <p class="text-muted">La Paz, Bolivia</p>
                        </div>
                        <div class="mb-3">
                            <strong><i class="bi bi-telephone me-2"></i>Teléfono:</strong>
                            <p class="text-muted">+22222222</p>
                        </div>
                        <div class="mb-3">
                            <strong><i class="bi bi-envelope me-2"></i>Email:</strong>
                            <p class="text-muted">info@albus.com.bo</p>
                        </div>
                        <div class="mb-3">
                            <strong><i class="bi bi-clock me-2"></i>Horario de Atención:</strong>
                            <p class="text-muted">Lunes a Viernes: 8:00 - 18:00</p>
                            <p class="text-muted">Sábados: 8:00 - 12:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Access Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-8 mx-auto">
                    <img src="imagenes/logo_albus.png" alt="Albus S.A." class="mb-4" style="height: 60px;">
                    <h2 class="section-title fw-bold mb-4">Acceso al Sistema</h2>
                    <p class="lead mb-4">
                        Para acceder al sistema de gestión interno de Albus S.A., utiliza tus credenciales corporativas.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </a>
                        <a href="registro.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Registrar Usuario
                        </a>
                    </div>
                    <div class="mt-4">
                        <small class="text-muted">
                            Si tienes problemas para acceder, contacta al departamento de Sistemas.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5>
                        <img src="imagenes/logo_albus.png" alt="Albus S.A." class="logo me-2">
                        Albus S.A.
                    </h5>
                    <p class="text-muted mb-0">Sistema Interno de Gestión</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; 2024 Albus S.A. - Uso exclusivo interno
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>