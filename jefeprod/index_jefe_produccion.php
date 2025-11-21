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

// Estadísticas adicionales para Jefe de Producción
$sql_total_quimicos = "SELECT COUNT(*) as total FROM productos_quimicos";
$sql_total_envases = "SELECT COUNT(*) as total FROM envases";
$sql_quimicos_bajo = "SELECT COUNT(*) as total FROM productos_quimicos WHERE stock <= stock_minimo";
$sql_envases_bajo = "SELECT COUNT(*) as total FROM envases WHERE stock <= stock_minimo";

$total_quimicos = $conn->query($sql_total_quimicos)->fetch_assoc()['total'];
$total_envases = $conn->query($sql_total_envases)->fetch_assoc()['total'];
$quimicos_bajo = $conn->query($sql_quimicos_bajo)->fetch_assoc()['total'];
$envases_bajo = $conn->query($sql_envases_bajo)->fetch_assoc()['total'];

// Últimos movimientos (entradas y salidas)
$sql_ultimos_movimientos = "SELECT 
                            'Entrada' as tipo, 
                            e.fecha, 
                            p.nombre as producto, 
                            e.cantidad,
                            u.nombre as responsable,
                            e.motivo
                            FROM entradas e 
                            JOIN productos p ON e.id_producto = p.id_producto 
                            JOIN usuarios u ON e.usuario_responsable = u.id_usuario
                            UNION ALL
                            SELECT 
                            'Salida' as tipo, 
                            s.fecha, 
                            p.nombre as producto, 
                            s.cantidad,
                            u.nombre as responsable,
                            s.motivo
                            FROM salidas s 
                            JOIN productos p ON s.id_producto = p.id_producto 
                            JOIN usuarios u ON s.usuario_responsable = u.id_usuario
                            ORDER BY fecha DESC 
                            LIMIT 8";
$ultimos_movimientos = $conn->query($sql_ultimos_movimientos);

// Productos con stock crítico
$sql_productos_stock_critico = "SELECT * FROM productos WHERE stock <= stock_minimo ORDER BY stock ASC LIMIT 6";
$productos_stock_critico = $conn->query($sql_productos_stock_critico);

// Movimientos del mes para el gráfico
$sql_movimientos_semanales = "
    SELECT 
        WEEK(fecha, 1) - WEEK(DATE_SUB(fecha, INTERVAL DAYOFMONTH(fecha)-1 DAY), 1) + 1 as semana,
        SUM(CASE WHEN tipo = 'Entrada' THEN cantidad ELSE 0 END) as entradas,
        SUM(CASE WHEN tipo = 'Salida' THEN cantidad ELSE 0 END) as salidas
    FROM (
        SELECT fecha, cantidad, 'Entrada' as tipo FROM entradas 
        WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())
        UNION ALL
        SELECT fecha, cantidad, 'Salida' as tipo FROM salidas 
        WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())
    ) movimientos
    GROUP BY semana
    ORDER BY semana
";
$movimientos_semanales = $conn->query($sql_movimientos_semanales);

// Preparar datos para el gráfico
$semanas = [];
$entradas_data = [];
$salidas_data = [];

while ($semana = $movimientos_semanales->fetch_assoc()) {
    $semanas[] = 'Sem ' . $semana['semana'];
    $entradas_data[] = $semana['entradas'];
    $salidas_data[] = $semana['salidas'];
}
?>

<div class="container-fluid">
    <!-- Header del Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-clipboard-data"></i> Panel de Control - Producción</h2>
            <p class="text-muted mb-0">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?> - Jefe de Producción</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Último acceso: <?php echo date('d/m/Y H:i'); ?></small>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
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

        <div class="col-xl-2 col-md-4 mb-4">
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

        <div class="col-xl-2 col-md-4 mb-4">
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

        <div class="col-xl-2 col-md-4 mb-4">
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

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Productos Químicos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_quimicos; ?></div>
                            <div class="text-xs text-danger"><?php echo $quimicos_bajo; ?> críticos</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-droplet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Envases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_envases; ?></div>
                            <div class="text-xs text-danger"><?php echo $envases_bajo; ?> críticos</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tags fa-2x text-gray-300"></i>
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
                    <span class="badge bg-primary"><?php echo date('F Y'); ?></span>
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
                        <a href="gestion_entradas.php" class="btn btn-primary btn-block">
                            <i class="bi bi-arrow-down-square"></i> Gestionar Entradas
                        </a>
                        <a href="gestion_salidas.php" class="btn btn-success btn-block">
                            <i class="bi bi-arrow-up-square"></i> Gestionar Salidas
                        </a>
                        <a href="ajustar_stock_minimo.php" class="btn btn-info btn-block">
                            <i class="bi bi-sliders"></i> Ajustar Stock Mínimo
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
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Movimientos</h6>
                    <a href="movimientos_totales.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
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
                                        <th>Motivo</th>
                                        <th>Responsable</th>
                                        <th>Hora</th>
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
                                            <td class="small"><?php echo htmlspecialchars($movimiento['producto']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $movimiento['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo $movimiento['tipo'] == 'Entrada' ? '+' : '-'; ?><?php echo $movimiento['cantidad']; ?>
                                                </span>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($movimiento['motivo']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($movimiento['responsable']); ?></td>
                                            <td class="small text-muted"><?php echo date('H:i', strtotime($movimiento['fecha'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay movimientos recientes.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Productos con Stock Crítico -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">Productos con Stock Crítico</h6>
                    <a href="stock_completo.php" class="btn btn-sm btn-outline-danger">Ver Stock</a>
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
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($producto = $productos_stock_critico->fetch_assoc()): 
                                        $diferencia = $producto['stock_minimo'] - $producto['stock'];
                                        $clase = $diferencia > 0 ? 'text-danger' : 'text-warning';
                                        $estado = $producto['stock'] == 0 ? 'Agotado' : ($diferencia > 0 ? 'Bajo Stock' : 'Crítico');
                                        $badge_class = $producto['stock'] == 0 ? 'bg-danger' : ($diferencia > 0 ? 'bg-warning' : 'bg-danger');
                                    ?>
                                        <tr>
                                            <td class="small"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                            <td class="text-danger fw-bold"><?php echo $producto['stock']; ?></td>
                                            <td class="text-muted"><?php echo $producto['stock_minimo']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $estado; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-success text-center">✅ Todo el stock está en niveles óptimos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del Sistema -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-primary">Sistema</h5>
                                <p class="mb-0 small">Albus S.R.L.</p>
                                <span class="badge bg-primary">Producción</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-success">Rol Activo</h5>
                                <p class="mb-0 small">Jefe de Producción</p>
                                <span class="badge bg-success">Autorizado</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-info">Usuario</h5>
                                <p class="mb-0 small"><?php echo $_SESSION['usuario_nombre']; ?></p>
                                <span class="badge bg-info">Conectado</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="<?php echo ($stock_bajo == 0) ? 'text-success' : 'text-warning'; ?>">Estado Sistema</h5>
                            <p class="mb-0 small"><?php echo ($stock_bajo == 0) ? 'Estable' : 'Requiere Atención'; ?></p>
                            <span class="badge <?php echo ($stock_bajo == 0) ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo ($stock_bajo == 0) ? '✅ Óptimo' : '⚠️ Revisar'; ?>
                            </span>
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
// Gráfico de movimientos con datos reales
const ctx = document.getElementById('movimientosChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($semanas); ?>,
        datasets: [{
            label: 'Entradas',
            data: <?php echo json_encode($entradas_data); ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 2
        }, {
            label: 'Salidas',
            data: <?php echo json_encode($salidas_data); ?>,
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 2
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
                text: 'Movimientos Semanales - <?php echo date('F Y'); ?>'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Cantidad'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Semanas del Mes'
                }
            }
        }
    }
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>