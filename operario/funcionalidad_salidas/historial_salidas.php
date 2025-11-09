<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';
include '../header_operario.php';

// ... (el resto del código PHP igual) ...

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clock-history"></i> Historial de Salidas</h2>
        <div>
            <!-- Botón Volver a Salidas - FUNCIONA BIEN -->
            <a href="../salidas_operario.php" class="btn btn-danger">
                <i class="bi bi-arrow-left-circle"></i> Volver a Salidas
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-funnel"></i> Filtros
                <?php if ($filtros_activos): ?>
                    <span class="badge bg-warning text-dark ms-2">Filtros activos</span>
                <?php endif; ?>
            </div>
            <?php if ($filtros_activos): ?>
                <!-- Botón Limpiar Filtros - CORREGIDO -->
                <a href="historial_salidas.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle"></i> Limpiar Filtros
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Producto:</label>
                        <select class="form-select" name="producto_id">
                            <option value="">Todos los productos</option>
                            <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                                <?php while ($producto = $productos_result->fetch_assoc()): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>" 
                                        <?php echo $producto_id == $producto['id_producto'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Motivo:</label>
                        <select class="form-select" name="motivo">
                            <option value="">Todos los motivos</option>
                            <?php foreach ($motivos as $mot): ?>
                                <option value="<?php echo $mot; ?>" 
                                    <?php echo $motivo == $mot ? 'selected' : ''; ?>>
                                    <?php echo $mot; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <?php if ($filtros_activos): ?>
                                <!-- Botón Limpiar en el formulario - CORREGIDO -->
                                <a href="historial_salidas.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <!-- Resumen de resultados -->
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="bi bi-info-circle"></i>
                        Mostrando <?php echo $result->num_rows; ?> de <?php echo $total_registros; ?> salidas
                        <?php if ($filtros_activos): ?>
                            <span class="badge bg-warning ms-2">Resultados filtrados</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($filtros_activos): ?>
                        <!-- Botón Limpiar en resultados - CORREGIDO -->
                        <a href="historial_salidas.php" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Limpiar Filtros
                        </a>
                    <?php endif; ?>
                </div>

                <!-- ... (tabla y paginación igual) ... -->

            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-box-arrow-up display-4"></i>
                    <p class="mt-3">No hay salidas registradas.</p>
                    
                    <?php if ($filtros_activos): ?>
                        <div class="alert alert-warning">
                            <p>No se encontraron resultados con los filtros aplicados.</p>
                            <!-- Botón Limpiar cuando no hay resultados - CORREGIDO -->
                            <a href="historial_salidas.php" class="btn btn-warning">
                                <i class="bi bi-x-circle"></i> Limpiar Filtros y Ver Todos
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Botón Volver cuando no hay salidas - CORREGIDO -->
                        <a href="../salidas_operario.php" class="btn btn-danger">
                            <i class="bi bi-plus-circle"></i> Registrar Primera Salida
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../../footer.php'; 
?>