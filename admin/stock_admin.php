<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Consultas para el dashboard de stock
$sql_stock_bajo = "SELECT * FROM productos WHERE stock <= stock_minimo ORDER BY stock ASC";
$stock_bajo = $conn->query($sql_stock_bajo);

$sql_sin_stock = "SELECT * FROM productos WHERE stock = 0 ORDER BY nombre ASC";
$sin_stock = $conn->query($sql_sin_stock);

$sql_stock_optimo = "SELECT * FROM productos WHERE stock > stock_minimo ORDER BY nombre ASC";
$stock_optimo = $conn->query($sql_stock_optimo);

// Estadísticas generales
$sql_total_productos = "SELECT COUNT(*) as total FROM productos";
$total_productos = $conn->query($sql_total_productos)->fetch_assoc()['total'];

$sql_total_valor_inventario = "SELECT SUM(stock) as total_unidades FROM productos";
$total_unidades = $conn->query($sql_total_valor_inventario)->fetch_assoc()['total_unidades'];

// Movimientos recientes (combinando entradas y salidas)
$sql_movimientos = "
    (SELECT 'entrada' as tipo, fecha, p.nombre as producto, e.cantidad, e.motivo, u.nombre as responsable 
     FROM entradas e 
     JOIN productos p ON e.id_producto = p.id_producto 
     JOIN usuarios u ON e.usuario_responsable = u.id_usuario 
     ORDER BY fecha DESC LIMIT 10)
    UNION ALL
    (SELECT 'salida' as tipo, fecha, p.nombre as producto, s.cantidad, s.motivo, u.nombre as responsable 
     FROM salidas s 
     JOIN productos p ON s.id_producto = p.id_producto 
     JOIN usuarios u ON s.usuario_responsable = u.id_usuario 
     ORDER BY fecha DESC LIMIT 10)
    ORDER BY fecha DESC LIMIT 15
";
$movimientos = $conn->query($sql_movimientos);
?>

<div class="container-fluid">
    <h2><i class="bi bi-graph-up"></i> Dashboard de Stock</h2>
    <p class="text-muted">Monitoreo completo del inventario y movimientos del almacén.</p>

    <!-- Tarjetas de Resumen General -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Productos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_productos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Stock Óptimo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stock_optimo->num_rows; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Stock Bajo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stock_bajo->num_rows; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Sin Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $sin_stock->num_rows; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Alertas de Stock -->
        <div class="col-lg-6">
            <!-- Stock Bajo (Alerta Amarilla) -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-exclamation-triangle"></i> Productos con Stock Bajo
                    </h6>
                    <span class="badge bg-warning"><?php echo $stock_bajo->num_rows; ?></span>
                </div>
                <div class="card-body">
                    <?php if ($stock_bajo->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock Actual</th>
                                        <th>Mínimo</th>
                                        <th>Diferencia</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($producto = $stock_bajo->fetch_assoc()): 
                                        $diferencia = $producto['stock_minimo'] - $producto['stock'];
                                        $porcentaje = ($producto['stock'] / $producto['stock_minimo']) * 100;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                                <?php if ($producto['ancho'] > 0 || $producto['largo'] > 0): ?>
                                                    <br><small class="text-muted">
                                                        <?php 
                                                        $dimensiones = [];
                                                        if ($producto['ancho'] > 0) $dimensiones[] = $producto['ancho'] . ' cm';
                                                        if ($producto['largo'] > 0) $dimensiones[] = $producto['largo'] . ' cm';
                                                        echo implode(' x ', $dimensiones);
                                                        ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark fs-6">
                                                    <?php echo $producto['stock']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $producto['stock_minimo']; ?></td>
                                            <td>
                                                <span class="text-danger fw-bold">-<?php echo $diferencia; ?></span>
                                            </td>
                                            <td>
                                                <a href="entradas_admin.php" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-plus-circle"></i> Reponer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-success py-3">
                            <i class="bi bi-check-circle-fill fs-1"></i>
                            <p class="mt-2 mb-0">¡Todo el stock está en niveles óptimos!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sin Stock (Alerta Roja) -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="bi bi-x-circle"></i> Productos Sin Stock
                    </h6>
                    <span class="badge bg-danger"><?php echo $sin_stock->num_rows; ?></span>
                </div>
                <div class="card-body">
                    <?php if ($sin_stock->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock Mínimo</th>
                                        <th>Última Actualización</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($producto = $sin_stock->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                                <?php if ($producto['ancho'] > 0 || $producto['largo'] > 0): ?>
                                                    <br><small class="text-muted">
                                                        <?php 
                                                        $dimensiones = [];
                                                        if ($producto['ancho'] > 0) $dimensiones[] = $producto['ancho'] . ' cm';
                                                        if ($producto['largo'] > 0) $dimensiones[] = $producto['largo'] . ' cm';
                                                        echo implode(' x ', $dimensiones);
                                                        ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $producto['stock_minimo']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($producto['updated_at'])); ?></td>
                                            <td>
                                                <a href="entradas_admin.php" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-arrow-down-circle"></i> Reponer Urgente
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-success py-3">
                            <i class="bi bi-check-circle-fill fs-1"></i>
                            <p class="mt-2 mb-0">No hay productos sin stock</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Movimientos y Stock Óptimo -->
        <div class="col-lg-6">
            <!-- Movimientos Recientes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-activity"></i> Movimientos Recientes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($movimientos->num_rows > 0): ?>
                        <div class="timeline">
                            <?php while ($movimiento = $movimientos->fetch_assoc()): 
                                $icono = $movimiento['tipo'] == 'entrada' ? 'bi-arrow-down-circle text-success' : 'bi-arrow-up-circle text-danger';
                                $badge = $movimiento['tipo'] == 'entrada' ? 'bg-success' : 'bg-danger';
                                $signo = $movimiento['tipo'] == 'entrada' ? '+' : '-';
                            ?>
                                <div class="timeline-item mb-3">
                                    <div class="timeline-marker <?php echo $badge; ?>">
                                        <i class="bi <?php echo $icono; ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($movimiento['producto']); ?></strong>
                                            <span class="badge <?php echo $badge; ?>">
                                                <?php echo $signo . $movimiento['cantidad']; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $movimiento['motivo']; ?> • 
                                            <?php echo htmlspecialchars($movimiento['responsable']); ?> • 
                                            <?php echo date('H:i', strtotime($movimiento['fecha'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2 mb-0">No hay movimientos recientes</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stock Óptimo -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-check-circle"></i> Stock en Nivel Óptimo
                    </h6>
                    <span class="badge bg-success"><?php echo $stock_optimo->num_rows; ?></span>
                </div>
                <div class="card-body">
                    <?php if ($stock_optimo->num_rows > 0): ?>
                        <div class="row">
                            <?php 
                            $contador = 0;
                            while ($producto = $stock_optimo->fetch_assoc()): 
                                if ($contador < 6): // Mostrar solo 6 productos
                            ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                        <div>
                                            <small class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></small>
                                            <br>
                                            <span class="badge bg-success"><?php echo $producto['stock']; ?></span>
                                            <small class="text-muted">/<?php echo $producto['stock_minimo']; ?></small>
                                        </div>
                                        <i class="bi bi-check-circle text-success"></i>
                                    </div>
                                </div>
                            <?php 
                                endif;
                                $contador++;
                            endwhile; 
                            
                            if ($contador > 6): ?>
                                <div class="col-12 text-center mt-2">
                                    <small class="text-muted">
                                        y <?php echo ($stock_optimo->num_rows - 6); ?> productos más con stock óptimo
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle fs-1"></i>
                            <p class="mt-2 mb-0">No hay productos con stock óptimo</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.timeline-content {
    padding-bottom: 10px;
    border-bottom: 1px solid #e3e6f0;
}
.timeline-item:last-child .timeline-content {
    border-bottom: none;
}
</style>

<?php include '../footer.php'; $conn->close(); ?>