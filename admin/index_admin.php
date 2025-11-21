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

// Datos para el gráfico - Movimientos de las últimas 4 semanas
$datos_grafico = array();
$etiquetas_grafico = array();

for ($i = 3; $i >= 0; $i--) {
    $fecha_inicio = date('Y-m-d', strtotime("-$i weeks"));
    $fecha_fin = date('Y-m-d', strtotime("-$i weeks +6 days"));
    
    // Entradas de la semana
    $sql_entradas = "SELECT COUNT(*) as total FROM entradas 
                    WHERE DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $entradas = $conn->query($sql_entradas)->fetch_assoc()['total'];
    
    // Salidas de la semana
    $sql_salidas = "SELECT COUNT(*) as total FROM salidas 
                   WHERE DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $salidas = $conn->query($sql_salidas)->fetch_assoc()['total'];
    
    $datos_grafico['entradas'][] = $entradas;
    $datos_grafico['salidas'][] = $salidas;
    $etiquetas_grafico[] = "Sem " . (4 - $i);
}

// Convertir datos a JSON para JavaScript
$datos_entradas_json = json_encode($datos_grafico['entradas']);
$datos_salidas_json = json_encode($datos_grafico['salidas']);
$etiquetas_json = json_encode($etiquetas_grafico);

// Estadísticas adicionales para el gráfico
$sql_total_entradas_mes = "SELECT COUNT(*) as total FROM entradas 
                          WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                          AND YEAR(fecha) = YEAR(CURRENT_DATE())";
$sql_total_salidas_mes = "SELECT COUNT(*) as total FROM salidas 
                         WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                         AND YEAR(fecha) = YEAR(CURRENT_DATE())";

$total_entradas_mes = $conn->query($sql_total_entradas_mes)->fetch_assoc()['total'];
$total_salidas_mes = $conn->query($sql_total_salidas_mes)->fetch_assoc()['total'];

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

    <!-- Gráfico y Estadísticas -->
    <div class="row">
        <!-- Gráfico de Movimientos -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Movimientos de las Últimas 4 Semanas</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Opciones del Gráfico:</div>
                            <a class="dropdown-item" onclick="actualizarGrafico('line')">Gráfico de Líneas</a>
                            <a class="dropdown-item" onclick="actualizarGrafico('bar')">Gráfico de Barras</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="movimientosChart" height="200"></canvas>
                    </div>
                    <div class="mt-3 text-center small">
                        <span class="mr-3">
                            <i class="bi bi-circle-fill text-primary"></i> Entradas: <?php echo $total_entradas_mes; ?> este mes
                        </span>
                        <span>
                            <i class="bi bi-circle-fill text-success"></i> Salidas: <?php echo $total_salidas_mes; ?> este mes
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Movimientos</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Este Mes</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="h4 text-primary mb-0"><?php echo $total_entradas_mes; ?></div>
                                    <small class="text-muted">Entradas</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-success mb-0"><?php echo $total_salidas_mes; ?></div>
                                <small class="text-muted">Salidas</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success">Acciones Rápidas</h6>
                        <div class="d-grid gap-2">
                            <a href="productos_admin.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Nuevo Producto
                            </a>
                            <a href="pedidos_admin.php" class="btn btn-success btn-sm">
                                <i class="bi bi-cart-plus"></i> Nuevo Pedido
                            </a>
                            <a href="entradas_admin.php" class="btn btn-info btn-sm">
                                <i class="bi bi-arrow-down-square"></i> Registrar Entrada
                            </a>
                            <a href="despachos_admin.php" class="btn btn-warning btn-sm">
                                <i class="bi bi-truck"></i> Gestionar Despachos
                            </a>
                        </div>
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
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos desde PHP
const etiquetas = <?php echo $etiquetas_json; ?>;
const datosEntradas = <?php echo $datos_entradas_json; ?>;
const datosSalidas = <?php echo $datos_salidas_json; ?>;

let movimientosChart;

function inicializarGrafico(tipo = 'line') {
    const ctx = document.getElementById('movimientosChart').getContext('2d');
    
    if (movimientosChart) {
        movimientosChart.destroy();
    }
    
    movimientosChart = new Chart(ctx, {
        type: tipo,
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Entradas',
                data: datosEntradas,
                borderColor: '#0d6efd',
                backgroundColor: tipo === 'line' ? 'rgba(13, 110, 253, 0.1)' : 'rgba(13, 110, 253, 0.8)',
                borderWidth: 2,
                tension: 0.4,
                fill: tipo === 'line'
            }, {
                label: 'Salidas',
                data: datosSalidas,
                borderColor: '#198754',
                backgroundColor: tipo === 'line' ? 'rgba(25, 135, 84, 0.1)' : 'rgba(25, 135, 84, 0.8)',
                borderWidth: 2,
                tension: 0.4,
                fill: tipo === 'line'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de Movimientos'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Semanas'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'nearest'
            }
        }
    });
}

function actualizarGrafico(tipo) {
    inicializarGrafico(tipo);
}

// Inicializar el gráfico cuando la página cargue
document.addEventListener('DOMContentLoaded', function() {
    inicializarGrafico('line');
});

// Redimensionar gráfico cuando cambie el tamaño de la ventana
window.addEventListener('resize', function() {
    if (movimientosChart) {
        movimientosChart.resize();
    }
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>