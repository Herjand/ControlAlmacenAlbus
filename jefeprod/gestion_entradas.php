<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';

// Procesar nueva entrada
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_entrada'])) {
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];
    $motivo = $_POST['motivo'];
    $observaciones = $_POST['observaciones'];
    $usuario_responsable = $_SESSION['usuario_id'];
    
    // Insertar entrada
    $sql = "INSERT INTO entradas (id_producto, cantidad, usuario_responsable, motivo, observaciones) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $id_producto, $cantidad, $usuario_responsable, $motivo, $observaciones);
    
    if ($stmt->execute()) {
        // Actualizar stock del producto
        $update_sql = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $cantidad, $id_producto);
        $update_stmt->execute();
        
        $_SESSION['mensaje'] = "success=Entrada registrada correctamente";
    } else {
        $_SESSION['mensaje'] = "error=Error al registrar la entrada";
    }
    
    header("Location: gestion_entradas.php");
    exit();
}

// Obtener productos
$sql_productos = "SELECT * FROM productos ORDER BY nombre";
$productos = $conn->query($sql_productos);

// Obtener entradas recientes
$sql_entradas = "SELECT e.*, p.nombre as producto, u.nombre as responsable 
                 FROM entradas e 
                 JOIN productos p ON e.id_producto = p.id_producto 
                 JOIN usuarios u ON e.usuario_responsable = u.id_usuario 
                 ORDER BY e.fecha DESC 
                 LIMIT 20";
$entradas = $conn->query($sql_entradas);

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
            <h2 class="mb-1"><i class="bi bi-arrow-down-square"></i> Gestionar Entradas</h2>
            <p class="text-muted mb-0">Registro de nuevas entradas al inventario</p>
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
        <!-- Formulario de Entrada -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-plus-circle"></i> Nueva Entrada</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="formEntrada">
                        <div class="mb-3">
                            <label for="id_producto" class="form-label">Producto *</label>
                            <select class="form-select" id="id_producto" name="id_producto" required>
                                <option value="">Seleccionar producto...</option>
                                <?php while($producto = $productos->fetch_assoc()): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>" 
                                            data-stock="<?php echo $producto['stock']; ?>"
                                            data-minimo="<?php echo $producto['stock_minimo']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?> 
                                        - <?php echo htmlspecialchars($producto['descripcion']); ?>
                                        (Stock: <?php echo $producto['stock']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                   min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo *</label>
                            <select class="form-select" id="motivo" name="motivo" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="Compra">Compra</option>
                                <option value="Producción">Producción</option>
                                <option value="Devolución">Devolución</option>
                                <option value="Ajuste">Ajuste de inventario</option>
                                <option value="Transferencia">Transferencia interna</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" 
                                      rows="3" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                        
                        <button type="submit" name="registrar_entrada" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Registrar Entrada
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historial de Entradas -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Historial Reciente</h5>
                    <span class="badge bg-light text-dark">Últimas 20 entradas</span>
                </div>
                <div class="card-body">
                    <?php if ($entradas->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Responsable</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($entrada = $entradas->fetch_assoc()): ?>
                                        <tr>
                                            <td class="small">
                                                <?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($entrada['producto']); ?></td>
                                            <td>
                                                <span class="badge bg-success">+<?php echo $entrada['cantidad']; ?></span>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($entrada['motivo']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($entrada['responsable']); ?></td>
                                            <td class="small text-muted">
                                                <?php echo $entrada['observaciones'] ? htmlspecialchars($entrada['observaciones']) : '-'; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay entradas registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formEntrada').addEventListener('submit', function(e) {
    const cantidad = document.getElementById('cantidad').value;
    if (cantidad <= 0) {
        e.preventDefault();
        alert('La cantidad debe ser mayor a 0');
        return false;
    }
    return true;
});

// Mostrar información del producto seleccionado
document.getElementById('id_producto').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const stock = selectedOption.getAttribute('data-stock');
    const minimo = selectedOption.getAttribute('data-minimo');
    
    if (stock && minimo) {
        console.log(`Stock actual: ${stock}, Mínimo: ${minimo}`);
    }
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>