<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include 'header_admin.php';
?>

<div class="container-fluid">
    <h2><i class="bi bi-info-circle"></i> Acerca de Nosotros</h2>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Información de la Empresa -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Albus S.R.L.</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Misión</h6>
                            <p class="text-muted">
                                Producir y comercializar insumos médicos de alta calidad que contribuyan 
                                al bienestar y cuidado de la salud, manteniendo los más altos estándares 
                                de calidad y servicio a nuestros clientes.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Visión</h6>
                            <p class="text-muted">
                                Ser la empresa líder en la producción de insumos médicos en Bolivia, 
                                reconocida por nuestra calidad, innovación y compromiso con la salud pública.
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="text-primary">Valores Corporativos</h6>
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="border rounded p-3">
                                <i class="bi bi-award text-warning" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Calidad</h6>
                                <small class="text-muted">Productos que superan estándares internacionales</small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="border rounded p-3">
                                <i class="bi bi-heart-pulse text-danger" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Compromiso</h6>
                                <small class="text-muted">Con la salud y bienestar de la comunidad</small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="border rounded p-3">
                                <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Seguridad</h6>
                                <small class="text-muted">Productos seguros y confiables</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos Principales -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Nuestros Productos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-check-circle text-success"></i> Algodón Médico</h6>
                            <ul class="text-muted small">
                                <li>Algodón entero e hidrófilo</li>
                                <li>Algodón laminado</li>
                                <li>Algodón en disco</li>
                                <li>Torundas de algodón</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-check-circle text-success"></i> Gasas y Vendas</h6>
                            <ul class="text-muted small">
                                <li>Gasas estériles y no estériles</li>
                                <li>Vendas de gasa de diferentes medidas</li>
                                <li>Compresas de gasa</li>
                                <li>Torundas de gasa</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-check-circle text-success"></i> Apósitos</h6>
                            <ul class="text-muted small">
                                <li>Apósitos oculares</li>
                                <li>Apósitos estériles</li>
                                <li>Compresas costuradas</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-check-circle text-success"></i> Equipo de Protección</h6>
                            <ul class="text-muted small">
                                <li>Barbijos quirúrgicos</li>
                                <li>Tapa ojos y oídos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Información de Contacto -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-telephone"></i> Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="bi bi-geo-alt text-primary"></i> Dirección</h6>
                        <p class="text-muted small">Av. Vásquez N921 #123, Pura-Pura<br>La Paz, Bolivia</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="bi bi-telephone text-success"></i> Teléfonos</h6>
                        <p class="text-muted small">
                            📞 (+591) 2-2367890<br>
                            📞 (+591) 2-2367891
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="bi bi-envelope text-warning"></i> Email</h6>
                        <p class="text-muted small">
                            ✉️ ventas@albus.com.bo<br>
                            ✉️ info@albus.com.bo
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="bi bi-clock text-secondary"></i> Horario de Atención</h6>
                        <p class="text-muted small">
                            🕗 Lunes a Viernes: 7:00 - 16:30<br>
                            🕗 Sábados: 7:00 - 12:00
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sistema de Gestión -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-laptop"></i> Sistema de Gestión</h5>
                </div>
                <div class="card-body">
                    <h6>Versión del Sistema</h6>
                    <p class="text-muted small">v2.1.0 - Albus Gestión de Almacén</p>
                    
                    <h6>Desarrollado para</h6>
                    <p class="text-muted small">Albus S.A. - Producción de Insumos Médicos</p>
                    
                    <h6>Funcionalidades Principales</h6>
                    <ul class="text-muted small">
                        <li>Gestión de Inventario</li>
                        <li>Control de Pedidos</li>
                        <li>Registro de Movimientos</li>
                        <li>Reportes y Estadísticas</li>
                        <li>Gestión de Clientes</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="bi bi-shield-lock"></i> 
                            <strong>Sistema seguro:</strong> Todas las operaciones están auditadas 
                            y protegidas con autenticación de usuarios.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>