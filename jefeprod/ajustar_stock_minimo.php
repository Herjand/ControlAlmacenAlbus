<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';

// Procesar ajuste de stock mínimo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajustar_stock_minimo'])) {
    $id_producto = $_POST['id_producto'];
    $nuevo_stock_minimo = $_POST['nuevo_stock_minimo'];
    
    // Actualizar stock mínimo
    $sql = "UPDATE productos SET stock_minimo = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nuevo_stock_minimo, $id_producto);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "success=Stock mínimo actualizado correctamente";
    } else {
        $_SESSION['mensaje'] = "error=Error al actualizar el stock mínimo";
    }
    
    header("Location: ajustar_stock_minimo.php");
    exit();
}

// Obtener productos con su stock actual y mínimo
$sql_productos = "SELECT * FROM productos ORDER BY nombre";
$productos = $conn->query($sql_productos);

// Ahora incluimos el header después de procesar todo
include 'header_jefe_produccion.php';

// Recuperar mensaje de sesión si existe
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : '';
if (isset($_SESSION['mensaje'])) {
    unset($_SESSION['mensaje']); // Limpiar el mensaje después de usarlo
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-sliders"></i> Ajustar Stock Mínimo</h2>
            <p class="text-muted mb-0">Configurar niveles mínimos de inventario</p>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (strpos($mensaje, 'success=') === 0): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars(substr($mensaje, 8)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (strpos($mensaje, 'error=') === 0): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars(substr($mensaje, 6)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulario de Ajuste -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Ajustar Stock Mínimo</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="formStockMinimo">
                        <div class="mb-3">
                            <label for="id_producto" class="form-label">Producto *</label>
                            <select class="form-select" id="id_producto" name="id_producto" required>
                                <option value="">Seleccionar producto...</option>
                                <?php while($producto = $productos->fetch_assoc()): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>" 
                                            data-actual="<?php echo $producto['stock_minimo']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?> 
                                        - <?php echo htmlspecialchars($producto['descripcion']); ?>
                                        (Mínimo: <?php echo $producto['stock_minimo']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nuevo_stock_minimo" class="form-label">Nuevo Stock Mínimo *</label>
                            <input type="number" class="form-control" id="nuevo_stock_minimo" 
                                   name="nuevo_stock_minimo" min="0" required>
                            <div class="form-text" id="minimo-actual"></div>
                        </div>
                        
                        <button type="submit" name="ajustar_stock_minimo" class="btn btn-info w-100">
                            <i class="bi bi-save"></i> Actualizar Stock Mínimo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Productos -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul"></i> Productos y Stock Mínimo</h5>
                </div>
                <div class="card-body">
                    <?php 
                    // Resetear el puntero del resultado para volver a iterar
                    $productos->data_seek(0);
                    if ($productos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Descripción</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                        <th>Estado</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($producto = $productos->fetch_assoc()): 
                                        $diferencia = $producto['stock'] - $producto['stock_minimo'];
                                        $estado = $diferencia >= 0 ? 'Óptimo' : 'Crítico';
                                        $badge_class = $diferencia >= 0 ? 'bg-success' : 'bg-danger';
                                        $text_class = $diferencia >= 0 ? 'text-success' : 'text-danger';
                                    ?>
                                        <tr>
                                            <td class="small fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                            <td class="fw-bold"><?php echo $producto['stock']; ?></td>
                                            <td class="fw-bold text-info"><?php echo $producto['stock_minimo']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $estado; ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold <?php echo $text_class; ?>">
                                                <?php echo $diferencia >= 0 ? "+$diferencia" : $diferencia; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay productos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar stock mínimo actual al seleccionar producto
document.getElementById('id_producto').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const minimoActual = selectedOption.getAttribute('data-actual');
    const minimoInfo = document.getElementById('minimo-actual');
    
    if (minimoActual) {
        minimoInfo.textContent = `Stock mínimo actual: ${minimoActual} unidades`;
        document.getElementById('nuevo_stock_minimo').value = minimoActual;
    } else {
        minimoInfo.textContent = '';
        document.getElementById('nuevo_stock_minimo').value = '';
    }
});

// Validación del formulario
document.getElementById('formStockMinimo').addEventListener('submit', function(e) {
    const nuevoMinimo = document.getElementById('nuevo_stock_minimo').value;
    
    if (nuevoMinimo < 0) {
        e.preventDefault();
        alert('El stock mínimo no puede ser negativo');
        return false;
    }
    
    return true;
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>