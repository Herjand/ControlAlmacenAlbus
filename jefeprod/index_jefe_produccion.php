<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_jefe_produccion.php';

// Consultas para las estadísticas específicas del Jefe de Producción
$sql_total_productos = "SELECT COUNT(*) as total FROM productos";
$sql_entradas_mes = "SELECT COUNT(*) as total FROM entradas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())";
$sql_salidas_mes = "SELECT COUNT(*) as total FROM salidas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())";
$sql_stock_bajo = "SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo";

$total_productos = $conn->query($sql_total_productos)->fetch_assoc()['total'];
$entradas_mes = $conn->query($sql_entradas_mes)->fetch_assoc()['total'];
$salidas_mes = $conn->query($sql_salidas_mes)->fetch_assoc()['total'];
$stock_bajo = $conn->query($sql_stock_bajo)->fetch_assoc()['total'];

// Últimos movimientos (entradas y salidas)
$sql_ultimos_movimientos = "SELECT 
                            'Entrada' as tipo, 
                            e.fecha, 
                            p.nombre as producto, 
                            e.cantidad,
                            u.nombre as responsable
                            FROM entradas e 
                            JOIN productos p ON e.id_producto = p.id_producto 
                            JOIN usuarios u ON e.usuario_responsable = u.id_usuario
                            UNION ALL
                            SELECT 
                            'Salida' as tipo, 
                            s.fecha, 
                            p.nombre as producto, 
                            s.cantidad,
                            u.nombre as responsable
                            FROM salidas s 
                            JOIN productos p ON s.id_producto = p.id_producto 
                            JOIN usuarios u ON s.usuario_responsable = u.id_usuario
                            ORDER BY fecha DESC 
                            LIMIT 5";
$ultimos_movimientos = $conn->query($sql_ultimos_movimientos);

// Productos con stock crítico
$sql_productos_stock_critico = "SELECT * FROM productos WHERE stock <= stock_minimo ORDER BY stock ASC LIMIT 5";
$productos_stock_critico = $conn->query($sql_productos_stock_critico);
?>

<div class="container-fluid">
    <!-- Header del Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-clipboard-data"></i> Panel de Producción</h2>
            <p class="text-muted mb-0">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
        </div>
        <div class="text-end">
            <small class="text-muted">Último acceso: <?php echo date('d/m/Y H:i'); ?></small>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
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
                                Entradas Mes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $entradas_mes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-down-square fa-2x text-gray-300"></i>
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
                                Salidas Mes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $salidas_mes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-up-square fa-2x text-gray-300"></i>
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
                                Stock Crítico</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stock_bajo; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico y Acciones Rápidas -->
    <div class="row">
        <!-- Gráfico de Movimientos -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Movimientos del Mes</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="movimientosChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="entradas_jefe.php" class="btn btn-primary btn-block">
                            <i class="bi bi-arrow-down-square"></i> Control Entradas
                        </a>
                        <a href="salidas_jefe.php" class="btn btn-success btn-block">
                            <i class="bi bi-arrow-up-square"></i> Control Salidas
                        </a>
                        <a href="stock_seguridad.php" class="btn btn-info btn-block">
                            <i class="bi bi-shield-check"></i> Stock Seguro
                        </a>
                        <a href="alertas_stock.php" class="btn btn-warning btn-block">
                            <i class="bi bi-exclamation-triangle"></i> Alertas Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Información -->
    <div class="row">
        <!-- Últimos Movimientos -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Movimientos</h6>
                </div>
                <div class="card-body">
                    <?php if ($ultimos_movimientos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Responsable</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($movimiento = $ultimos_movimientos->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php echo $movimiento['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo $movimiento['tipo']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($movimiento['producto']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $movimiento['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo $movimiento['tipo'] == 'Entrada' ? '+' : '-'; ?><?php echo $movimiento['cantidad']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($movimiento['responsable']); ?></td>
                                            <td><?php echo date('H:i', strtotime($movimiento['fecha'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="movimientos_jefe.php" class="btn btn-outline-primary btn-sm">Ver todos los movimientos</a>
                    <?php else: ?>
                        <p class="text-muted">No hay movimientos recientes.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Productos con Stock Crítico -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Productos con Stock Crítico</h6>
                </div>
                <div class="card-body">
                    <?php if ($productos_stock_critico->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock Actual</th>
                                        <th>Mínimo</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($producto = $productos_stock_critico->fetch_assoc()): 
                                        $diferencia = $producto['stock_minimo'] - $producto['stock'];
                                        $clase = $diferencia > 0 ? 'text-danger' : 'text-warning';
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                            <td class="text-danger fw-bold"><?php echo $producto['stock']; ?></td>
                                            <td><?php echo $producto['stock_minimo']; ?></td>
                                            <td class="<?php echo $clase; ?> fw-bold">
                                                <?php echo $diferencia > 0 ? "-$diferencia" : "Crítico"; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="stock_seguridad.php" class="btn btn-outline-danger btn-sm">Gestionar stock seguro</a>
                    <?php else: ?>
                        <p class="text-success">✅ Todo el stock está en niveles óptimos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-primary">Sistema</h5>
                                <p class="mb-0 small">Albus S.R.L.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-success">Rol</h5>
                                <p class="mb-0 small">Jefe de Producción</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-info">Usuario</h5>
                                <p class="mb-0 small"><?php echo $_SESSION['usuario_nombre']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-warning">Estado</h5>
                            <p class="mb-0 small"><?php echo ($stock_bajo == 0) ? '✅ Estable' : '⚠️ Revisar Stock'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de movimientos
const ctx = document.getElementById('movimientosChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
        datasets: [{
            label: 'Entradas',
            data: [12, 19, 8, 15],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Salidas',
            data: [8, 12, 6, 10],
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Movimientos Semanales'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>