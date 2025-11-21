<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';

// Procesar nueva salida
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_salida'])) {
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];
    $motivo = $_POST['motivo'];
    $observaciones = $_POST['observaciones'];
    $usuario_responsable = $_SESSION['usuario_id'];
    
    // Verificar stock disponible
    $check_sql = "SELECT stock FROM productos WHERE id_producto = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id_producto);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $producto = $check_result->fetch_assoc();
    
    if ($producto['stock'] >= $cantidad) {
        // Insertar salida
        $sql = "INSERT INTO salidas (id_producto, cantidad, usuario_responsable, motivo, observaciones) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $id_producto, $cantidad, $usuario_responsable, $motivo, $observaciones);
        
        if ($stmt->execute()) {
            // Actualizar stock del producto
            $update_sql = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $cantidad, $id_producto);
            $update_stmt->execute();
            
            $_SESSION['mensaje'] = "success=Salida registrada correctamente";
        } else {
            $_SESSION['mensaje'] = "error=Error al registrar la salida";
        }
    } else {
        $_SESSION['mensaje'] = "error=Stock insuficiente para realizar la salida";
    }
    
    header("Location: gestion_salidas.php");
    exit();
}

// Obtener productos
$sql_productos = "SELECT * FROM productos ORDER BY nombre";
$productos = $conn->query($sql_productos);

// Obtener salidas recientes
$sql_salidas = "SELECT s.*, p.nombre as producto, u.nombre as responsable 
                FROM salidas s 
                JOIN productos p ON s.id_producto = p.id_producto 
                JOIN usuarios u ON s.usuario_responsable = u.id_usuario 
                ORDER BY s.fecha DESC 
                LIMIT 20";
$salidas = $conn->query($sql_salidas);

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
            <h2 class="mb-1"><i class="bi bi-arrow-up-square"></i> Gestionar Salidas</h2>
            <p class="text-muted mb-0">Registro de salidas del inventario</p>
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
        <!-- Formulario de Salida -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="bi bi-dash-circle"></i> Nueva Salida</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="formSalida">
                        <div class="mb-3">
                            <label for="id_producto" class="form-label">Producto *</label>
                            <select class="form-select" id="id_producto" name="id_producto" required>
                                <option value="">Seleccionar producto...</option>
                                <?php while($producto = $productos->fetch_assoc()): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>" 
                                            data-stock="<?php echo $producto['stock']; ?>">
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
                            <div class="form-text" id="stock-info"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo *</label>
                            <select class="form-select" id="motivo" name="motivo" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="Venta">Venta</option>
                                <option value="Producción">Producción</option>
                                <option value="Muestra">Muestra</option>
                                <option value="Ajuste">Ajuste de inventario</option>
                                <option value="Daño">Daño/Pérdida</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" 
                                      rows="3" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                        
                        <button type="submit" name="registrar_salida" class="btn btn-warning w-100">
                            <i class="bi bi-save"></i> Registrar Salida
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historial de Salidas -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Historial Reciente</h5>
                    <span class="badge bg-light text-dark">Últimas 20 salidas</span>
                </div>
                <div class="card-body">
                    <?php if ($salidas->num_rows > 0): ?>
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
                                    <?php while($salida = $salidas->fetch_assoc()): ?>
                                        <tr>
                                            <td class="small">
                                                <?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($salida['producto']); ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">-<?php echo $salida['cantidad']; ?></span>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($salida['motivo']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($salida['responsable']); ?></td>
                                            <td class="small text-muted">
                                                <?php echo $salida['observaciones'] ? htmlspecialchars($salida['observaciones']) : '-'; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay salidas registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar información del stock
document.getElementById('id_producto').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const stock = selectedOption.getAttribute('data-stock');
    const stockInfo = document.getElementById('stock-info');
    
    if (stock) {
        stockInfo.textContent = `Stock disponible: ${stock} unidades`;
        stockInfo.className = 'form-text';
        
        if (stock < 10) {
            stockInfo.className += ' text-danger fw-bold';
        } else if (stock < 20) {
            stockInfo.className += ' text-warning';
        }
    } else {
        stockInfo.textContent = '';
    }
});

// Validación del formulario
document.getElementById('formSalida').addEventListener('submit', function(e) {
    const cantidad = parseInt(document.getElementById('cantidad').value);
    const selectedOption = document.getElementById('id_producto').options[document.getElementById('id_producto').selectedIndex];
    const stock = parseInt(selectedOption.getAttribute('data-stock'));
    
    if (cantidad <= 0) {
        e.preventDefault();
        alert('La cantidad debe ser mayor a 0');
        return false;
    }
    
    if (cantidad > stock) {
        e.preventDefault();
        alert(`Stock insuficiente. Solo hay ${stock} unidades disponibles.`);
        return false;
    }
    
    return true;
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>