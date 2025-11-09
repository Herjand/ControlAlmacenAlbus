<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_operario.php';

// Consultar stock solo de productos normales
$sql = "SELECT 
            nombre,
            stock,
            stock_minimo,
            (stock - stock_minimo) as diferencia
        FROM productos
        ORDER BY diferencia ASC, nombre ASC";

$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h2><i class="fas fa-chart-bar"></i> Consultar Stock</h2>
    <p class="text-muted">Estado actual del inventario.</p>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5>TOTAL PRODUCTOS</h5>
                    <h3><?php echo $result->num_rows; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5>STOCK ÓPTIMO</h5>
                    <h3>
                        <?php 
                        $sql_optimo = "SELECT COUNT(*) as total FROM productos WHERE stock > stock_minimo";
                        echo $conn->query($sql_optimo)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h5>STOCK BAJO</h5>
                    <h3>
                        <?php 
                        $sql_bajo = "SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo AND stock > 0";
                        echo $conn->query($sql_bajo)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h5>SIN STOCK</h5>
                    <h3>
                        <?php 
                        $sql_sin_stock = "SELECT COUNT(*) as total FROM productos WHERE stock = 0";
                        echo $conn->query($sql_sin_stock)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Stock -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list-ul"></i> Estado del Stock
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Producto</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reiniciar el puntero del resultado
                            $result->data_seek(0);
                            while ($row = $result->fetch_assoc()): 
                                // Determinar clase de estado
                                if ($row['stock'] == 0) {
                                    $estado = "Sin Stock";
                                    $badgeClass = "bg-danger text-white";
                                } elseif ($row['stock'] <= $row['stock_minimo']) {
                                    $estado = "Stock Bajo";
                                    $badgeClass = "bg-warning text-dark";
                                } else {
                                    $estado = "Stock Óptimo";
                                    $badgeClass = "bg-success text-white";
                                }
                                
                                // Color para la diferencia
                                $diferenciaClass = ($row['diferencia'] < 0) ? 'text-danger' : 'text-success';
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                    <td><?php echo $row['stock']; ?></td>
                                    <td><?php echo $row['stock_minimo']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo $estado; ?>
                                        </span>
                                    </td>
                                    <td class="<?php echo $diferenciaClass; ?>">
                                        <strong><?php echo $row['diferencia']; ?></strong>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No hay productos registrados en el sistema.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
$conn->close();
?>