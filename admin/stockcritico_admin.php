<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Consulta de productos con stock crítico
$sql = "SELECT p.*, 
               (p.stock_minimo - p.stock) as faltante,
               ROUND((p.stock / p.stock_minimo) * 100, 2) as porcentaje
        FROM productos p 
        WHERE p.stock <= p.stock_minimo 
        ORDER BY (p.stock_minimo - p.stock) DESC, p.stock ASC";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h2><i class="bi bi-exclamation-triangle"></i> Stock Crítico</h2>
    <p class="text-muted">Productos con niveles de stock por debajo del mínimo establecido.</p>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>PRODUCTOS EN STOCK CRÍTICO</h6>
                    <h4><?php echo $result->num_rows; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>PRODUCTOS SIN STOCK</h6>
                    <h4>
                        <?php
                        $sql_sin_stock = "SELECT COUNT(*) as total FROM productos WHERE stock = 0";
                        echo $conn->query($sql_sin_stock)->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>TOTAL PRODUCTOS</h6>
                    <h4>
                        <?php
                        $sql_total = "SELECT COUNT(*) as total FROM productos";
                        echo $conn->query($sql_total)->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if ($result->num_rows > 0): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            <strong>Alerta:</strong> Existen <?php echo $result->num_rows; ?> productos con stock crítico que requieren atención inmediata.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> 
            <strong>Excelente:</strong> No hay productos con stock crítico en este momento.
        </div>
    <?php endif; ?>

    <!-- Tabla de Stock Crítico -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Productos con Stock Crítico</span>
            <div>
                <a href="entradas_admin.php" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle"></i> Registrar Entrada
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-secondary">
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Faltante</th>
                                <th>Nivel</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                $porcentaje = $row['porcentaje'];
                                if ($row['stock'] == 0) {
                                    $nivel = 'bg-danger';
                                    $estado = 'Sin Stock';
                                } elseif ($porcentaje <= 25) {
                                    $nivel = 'bg-danger';
                                    $estado = 'Muy Crítico';
                                } elseif ($porcentaje <= 50) {
                                    $nivel = 'bg-warning';
                                    $estado = 'Crítico';
                                } else {
                                    $nivel = 'bg-info';
                                    $estado = 'Bajo';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                        <?php if (!empty($row['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($row['descripcion']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $row['stock']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $row['stock_minimo']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger"><?php echo $row['faltante']; ?></span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?php echo $nivel; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min($porcentaje, 100); ?>%"
                                                 aria-valuenow="<?php echo $porcentaje; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php echo $porcentaje; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $nivel; ?>">
                                            <?php echo $estado; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="entradas_admin.php" class="btn btn-sm btn-success">
                                            <i class="bi bi-box-arrow-in-down"></i> Reponer
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-success text-center">
                    <i class="bi bi-check-circle"></i> 
                    <strong>¡Excelente!</strong> Todos los productos tienen stock suficiente.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recomendaciones -->
    <?php if ($result->num_rows > 0): ?>
    <div class="card mt-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Recomendaciones</h6>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Priorice la reposición de productos marcados como "Sin Stock" o "Muy Crítico"</li>
                <li>Revise los niveles de stock mínimo según la demanda real de cada producto</li>
                <li>Considere realizar pedidos de reposición para los productos más críticos</li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php 
$conn->close();
include '../footer.php';
?>