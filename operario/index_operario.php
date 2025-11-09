<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_operario.php';

// Obtener estadísticas para el dashboard
$sql_total_productos = "SELECT COUNT(*) as total FROM productos";
$total_productos = $conn->query($sql_total_productos)->fetch_assoc()['total'];

$sql_stock_bajo = "SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo AND stock > 0";
$stock_bajo = $conn->query($sql_stock_bajo)->fetch_assoc()['total'];

$sql_sin_stock = "SELECT COUNT(*) as total FROM productos WHERE stock = 0";
$sin_stock = $conn->query($sql_sin_stock)->fetch_assoc()['total'];

$sql_pedidos_pendientes = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'";
$pedidos_pendientes = $conn->query($sql_pedidos_pendientes)->fetch_assoc()['total'];

// Movimientos del día actual
$hoy = date('Y-m-d');
$sql_movimientos_hoy = "SELECT COUNT(*) as total FROM (
    SELECT id_entrada as id, fecha FROM entradas WHERE DATE(fecha) = '$hoy'
    UNION ALL 
    SELECT id_salida as id, fecha FROM salidas WHERE DATE(fecha) = '$hoy'
) as movimientos";
$movimientos_hoy = $conn->query($sql_movimientos_hoy)->fetch_assoc()['total'];

// Mis movimientos del mes
$mes_actual = date('Y-m');
$id_usuario = $_SESSION['usuario_id'];
$sql_mis_movimientos = "SELECT COUNT(*) as total FROM (
    SELECT id_entrada as id, fecha FROM entradas WHERE usuario_responsable = $id_usuario AND DATE_FORMAT(fecha, '%Y-%m') = '$mes_actual'
    UNION ALL 
    SELECT id_salida as id, fecha FROM salidas WHERE usuario_responsable = $id_usuario AND DATE_FORMAT(fecha, '%Y-%m') = '$mes_actual'
) as movimientos";
$mis_movimientos = $conn->query($sql_mis_movimientos)->fetch_assoc()['total'];
?>

<div class="container-fluid">
    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-1">
                                <i class="bi bi-person-badge"></i> 
                                Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            </h2>
                            <p class="card-text mb-0">
                                Panel de Control - Operario de Almacén<br>
                                <small>Fecha: <?php echo date('d/m/Y'); ?> | Hora: <?php echo date('H:i'); ?></small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="bg-white bg-opacity-25 p-3 rounded">
                                <h4 class="mb-0"><?php echo $movimientos_hoy; ?></h4>
                                <small>Movimientos Hoy</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam display-6 mb-2"></i>
                    <h3><?php echo $total_productos; ?></h3>
                    <h6>Total Productos</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle display-6 mb-2"></i>
                    <h3><?php echo $stock_bajo; ?></h3>
                    <h6>Stock Bajo</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle display-6 mb-2"></i>
                    <h3><?php echo $sin_stock; ?></h3>
                    <h6>Sin Stock</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart display-6 mb-2"></i>
                    <h3><?php echo $pedidos_pendientes; ?></h3>
                    <h6>Pedidos Pendientes</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Acciones Rápidas -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-lightning-charge"></i> Acciones Rápidas
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="entradas_operario.php" class="btn btn-success w-100 h-100 py-3">
                                <i class="bi bi-arrow-down-square display-6 mb-2"></i><br>
                                Registrar Entrada
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="salidas_operario.php" class="btn btn-danger w-100 h-100 py-3">
                                <i class="bi bi-arrow-up-square display-6 mb-2"></i><br>
                                Registrar Salida
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="preparar_pedidos.php" class="btn btn-primary w-100 h-100 py-3">
                                <i class="bi bi-cart-check display-6 mb-2"></i><br>
                                Preparar Pedidos
                                <?php if ($pedidos_pendientes > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $pedidos_pendientes; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="productos_operario.php" class="btn btn-info w-100 h-100 py-3">
                                <i class="bi bi-search display-6 mb-2"></i><br>
                                Consultar Productos
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="stock_operario.php" class="btn btn-warning w-100 h-100 py-3">
                                <i class="bi bi-graph-up display-6 mb-2"></i><br>
                                Ver Stock
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="mis_movimientos.php" class="btn btn-secondary w-100 h-100 py-3">
                                <i class="bi bi-list-ul display-6 mb-2"></i><br>
                                Mis Movimientos
                                <small class="d-block">(<?php echo $mis_movimientos; ?> este mes)</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Operario -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-person-badge"></i> Mi Información
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-person-circle display-1 text-primary"></i>
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nombre:</strong></td>
                            <td><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Rol:</strong></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Movimientos Hoy:</strong></td>
                            <td><span class="badge bg-success"><?php echo $movimientos_hoy; ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Movimientos Mes:</strong></td>
                            <td><span class="badge bg-info"><?php echo $mis_movimientos; ?></span></td>
                        </tr>
                    </table>
                    <div class="d-grid gap-2">
                        <a href="navbar_usuarios/mi_perfil.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person"></i> Mi Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Importantes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i> Alertas Importantes
                </div>
                <div class="card-body">
                    <?php if ($stock_bajo > 0): ?>
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>
                                <strong>Stock Bajo:</strong> <?php echo $stock_bajo; ?> producto(s) tienen stock por debajo del mínimo.
                                <a href="stock_operario.php?filtro=bajo" class="alert-link">Ver detalles</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($sin_stock > 0): ?>
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            <div>
                                <strong>Sin Stock:</strong> <?php echo $sin_stock; ?> producto(s) están agotados.
                                <a href="stock_operario.php?filtro=agotado" class="alert-link">Ver detalles</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pedidos_pendientes > 0): ?>
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="bi bi-cart-check me-2"></i>
                            <div>
                                <strong>Pedidos Pendientes:</strong> <?php echo $pedidos_pendientes; ?> pedido(s) esperan preparación.
                                <a href="preparar_pedidos.php" class="alert-link">Preparar ahora</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($stock_bajo == 0 && $sin_stock == 0 && $pedidos_pendientes == 0): ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle-fill"></i> 
                            <strong>¡Todo en orden!</strong> No hay alertas críticas en este momento.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
$conn->close();
?>