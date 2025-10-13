<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Consultas para las estadísticas
$sql_total_productos = "SELECT COUNT(*) as total FROM productos";
$sql_total_pedidos = "SELECT COUNT(*) as total FROM pedidos";
$sql_pedidos_pendientes = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'";
$sql_stock_bajo = "SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo";

$total_productos = $conn->query($sql_total_productos)->fetch_assoc()['total'];
$total_pedidos = $conn->query($sql_total_pedidos)->fetch_assoc()['total'];
$pedidos_pendientes = $conn->query($sql_pedidos_pendientes)->fetch_assoc()['total'];
$stock_bajo = $conn->query($sql_stock_bajo)->fetch_assoc()['total'];

// Últimos pedidos
$sql_ultimos_pedidos = "SELECT * FROM pedidos ORDER BY created_at DESC LIMIT 5";
$ultimos_pedidos = $conn->query($sql_ultimos_pedidos);

// Productos con stock bajo
$sql_productos_bajo_stock = "SELECT * FROM productos WHERE stock <= stock_minimo ORDER BY stock ASC LIMIT 5";
$productos_bajo_stock = $conn->query($sql_productos_bajo_stock);
?>

<div class="container-fluid">
    <!-- Header del Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-speedometer2"></i> Panel de Administración</h2>
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
                                Total Pedidos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pedidos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-check fa-2x text-gray-300"></i>
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
                                Pedidos Pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pedidos_pendientes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
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
                                Stock Bajo</div>
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
                        <a href="productos_admin.php" class="btn btn-primary btn-block">
                            <i class="bi bi-plus-circle"></i> Nuevo Producto
                        </a>
                        <a href="pedidos_admin.php" class="btn btn-success btn-block">
                            <i class="bi bi-cart-plus"></i> Nuevo Pedido
                        </a>
                        <a href="entradas_admin.php" class="btn btn-info btn-block">
                            <i class="bi bi-arrow-down-square"></i> Registrar Entrada
                        </a>
                        <a href="despachos_admin.php" class="btn btn-warning btn-block">
                            <i class="bi bi-truck"></i> Gestionar Despachos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Información -->
    <div class="row">
        <!-- Últimos Pedidos -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Pedidos</h6>
                </div>
                <div class="card-body">
                    <?php if ($ultimos_pedidos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Empresa</th>
                                        <th>Contacto</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pedido = $ultimos_pedidos->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pedido['empresa_cliente']); ?></td>
                                            <td><?php echo htmlspecialchars($pedido['persona_contacto']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        if ($pedido['estado'] == 'Completado') echo 'bg-success';
                                                        elseif ($pedido['estado'] == 'En progreso') echo 'bg-warning';
                                                        else echo 'bg-secondary';
                                                    ?>">
                                                    <?php echo $pedido['estado']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m', strtotime($pedido['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="pedidos_admin.php" class="btn btn-outline-primary btn-sm">Ver todos los pedidos</a>
                    <?php else: ?>
                        <p class="text-muted">No hay pedidos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Productos con Stock Bajo -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Productos con Stock Bajo</h6>
                </div>
                <div class="card-body">
                    <?php if ($productos_bajo_stock->num_rows > 0): ?>
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
                                    <?php while ($producto = $productos_bajo_stock->fetch_assoc()): 
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
                        <a href="productos_admin.php" class="btn btn-outline-danger btn-sm">Gestionar inventario</a>
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
                                <h5 class="text-success">Versión</h5>
                                <p class="mb-0 small">v2.0</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-info">Usuario</h5>
                                <p class="mb-0 small"><?php echo $_SESSION['usuario_rol']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-warning">Sesión</h5>
                            <p class="mb-0 small">Activa</p>
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