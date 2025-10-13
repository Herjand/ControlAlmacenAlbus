<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Manejar mensajes
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Consultar salidas recientes
$sql = "SELECT s.*, p.nombre as producto_nombre, u.nombre as usuario_nombre 
        FROM salidas s 
        JOIN productos p ON s.id_producto = p.id_producto 
        JOIN usuarios u ON s.usuario_responsable = u.id_usuario 
        ORDER BY s.fecha DESC 
        LIMIT 50";
$result = $conn->query($sql);

// Consultar productos para el select
$sql_productos = "SELECT id_producto, nombre, stock, unidad_medida FROM productos ORDER BY nombre";
$productos_result = $conn->query($sql_productos);
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-up-square"></i> Registro de Salidas</h2>
    <p class="text-muted">Registra salidas de productos del almacén y actualiza automáticamente el stock.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php echo $success == 1 ? "Salida registrada correctamente" : "Operación realizada"; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            <?php 
            switch($error) {
                case '1': echo "Error al registrar la salida"; break;
                case '2': echo "Error: Campos vacíos"; break;
                case '3': echo "Error: Stock insuficiente"; break;
                default: echo "Error en la operación";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de Salida -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <i class="bi bi-dash-circle"></i> Nueva Salida de Productos
        </div>
        <div class="card-body">
            <form action="funcionalidad_salidas/registrar_salida.php" method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Producto:</label>
                            <select class="form-select" name="id_producto" required id="selectProducto">
                                <option value="">Seleccionar producto...</option>
                                <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                                    <?php while ($producto = $productos_result->fetch_assoc()): ?>
                                        <option value="<?php echo $producto['id_producto']; ?>" 
                                                data-stock="<?php echo $producto['stock']; ?>"
                                                data-unidad="<?php echo $producto['unidad_medida']; ?>">
                                            <?php echo htmlspecialchars($producto['nombre']); ?> 
                                            (Stock: <?php echo $producto['stock']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay productos registrados</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Cantidad a retirar:</label>
                            <input type="number" class="form-control" name="cantidad" required min="1" value="1" id="inputCantidad">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Motivo:</label>
                            <select class="form-select" name="motivo" required>
                                <option value="Venta">Venta/Despacho</option>
                                <option value="Donación">Donación</option>
                                <option value="Ajuste">Ajuste de inventario</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Observaciones:</label>
                            <input type="text" class="form-control" name="observaciones" placeholder="Opcional" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Registrar Salida
                        </button>
                        <small class="text-muted ms-3" id="infoStock"></small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Historial de Salidas -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-clock-history"></i> Historial de Salidas Recientes
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
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
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['producto_nombre']); ?></td>
                                    <td>
                                        <span class="badge bg-danger fs-6">
                                            -<?php echo $row['cantidad']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($row['motivo']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['usuario_nombre']); ?></td>
                                    <td><?php echo $row['observaciones'] ? htmlspecialchars($row['observaciones']) : '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No hay registros de salidas.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Mostrar información del stock cuando seleccionan producto
document.getElementById('selectProducto').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const stockActual = selectedOption.getAttribute('data-stock');
    const unidad = selectedOption.getAttribute('data-unidad');
    const infoStock = document.getElementById('infoStock');
    
    if (stockActual !== null && selectedOption.value !== "") {
        // Mapeo de unidades amigables
        const unidades = {
            'unidad': 'unidades',
            'caja': 'cajas', 
            'pack': 'packs',
            'rollo': 'rollos',
            'par': 'pares',
            'gramo': 'gramos',
            'kilogramo': 'kilogramos',
            'metro': 'metros',
            'centimetro': 'centímetros'
        };
        
        const unidadDisplay = unidades[unidad] || unidad;
        infoStock.textContent = `Stock actual: ${stockActual} ${unidadDisplay}`;
        
        // Cambiar color según stock
        if (stockActual < 10) {
            infoStock.className = 'text-danger ms-3 fw-bold';
        } else if (stockActual < 20) {
            infoStock.className = 'text-warning ms-3 fw-bold';
        } else {
            infoStock.className = 'text-success ms-3 fw-bold';
        }
    } else {
        infoStock.textContent = '';
    }
});

// Validar que no se retire más del stock disponible
document.querySelector('form').addEventListener('submit', function(e) {
    const selectedOption = document.getElementById('selectProducto').options[document.getElementById('selectProducto').selectedIndex];
    const stockActual = parseInt(selectedOption.getAttribute('data-stock'));
    const cantidad = parseInt(document.getElementById('inputCantidad').value);
    
    if (cantidad > stockActual) {
        e.preventDefault();
        alert(`❌ Error: No hay suficiente stock.\nStock disponible: ${stockActual}\nCantidad a retirar: ${cantidad}`);
    }
});
</script>

<?php 
include '../footer.php'; 
$conn->close();
?>