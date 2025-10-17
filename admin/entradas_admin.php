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

// Consultar entradas recientes
$sql = "SELECT e.*, p.nombre as producto_nombre, u.nombre as usuario_nombre 
        FROM entradas e 
        JOIN productos p ON e.id_producto = p.id_producto 
        JOIN usuarios u ON e.usuario_responsable = u.id_usuario 
        ORDER BY e.fecha DESC 
        LIMIT 50";
$result = $conn->query($sql);

// Consultar productos para el select - INCLUYENDO ESPECIFICACIONES
$sql_productos = "SELECT id_producto, nombre, descripcion, stock, unidad_medida, 
                         tamaño_peso, presentacion, cantidad_unidad, tipo_especifico 
                  FROM productos 
                  ORDER BY nombre, tipo_especifico, tamaño_peso";
$productos_result = $conn->query($sql_productos);
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-down-square"></i> Registro de Entradas</h2>
    <p class="text-muted">Registra ingresos de productos al almacén y actualiza automáticamente el stock.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php echo $success == 1 ? "Entrada registrada correctamente" : "Operación realizada"; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            <?php 
            switch($error) {
                case '1': echo "Error al registrar la entrada"; break;
                case '2': echo "Error: Campos vacíos"; break;
                default: echo "Error en la operación";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de Entrada -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-plus-circle"></i> Nueva Entrada de Productos
        </div>
        <div class="card-body">
            <form action="funcionalidad_entradas/registrar_entrada.php" method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Producto:</label>
                            <select class="form-select" name="id_producto" required id="selectProducto">
                                <option value="">Seleccionar producto...</option>
                                <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                                    <?php while ($producto = $productos_result->fetch_assoc()): 
                                        // Construir descripción detallada
                                        $especificaciones = [];
                                        if ($producto['tamaño_peso']) $especificaciones[] = $producto['tamaño_peso'];
                                        if ($producto['cantidad_unidad']) $especificaciones[] = $producto['cantidad_unidad'];
                                        if ($producto['tipo_especifico']) $especificaciones[] = $producto['tipo_especifico'];
                                        if ($producto['presentacion']) $especificaciones[] = $producto['presentacion'];
                                        
                                        $descripcion_detallada = !empty($especificaciones) ? ' - ' . implode(' • ', $especificaciones) : '';
                                        if ($producto['descripcion']) {
                                            $descripcion_detallada = ' - ' . $producto['descripcion'] . $descripcion_detallada;
                                        }
                                    ?>
                                        <option value="<?php echo $producto['id_producto']; ?>" 
                                                data-stock="<?php echo $producto['stock']; ?>"
                                                data-unidad="<?php echo $producto['unidad_medida']; ?>"
                                                data-especificaciones="<?php echo htmlspecialchars(implode(' • ', $especificaciones)); ?>">
                                            <?php echo htmlspecialchars($producto['nombre'] . $descripcion_detallada); ?> 
                                            (Stock: <?php echo $producto['stock']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay productos registrados</option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted" id="infoEspecificaciones"></small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Cantidad a sumar:</label>
                            <input type="number" class="form-control" name="cantidad" required min="1" value="1" id="inputCantidad">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Motivo:</label>
                            <select class="form-select" name="motivo" required>
                                <option value="Compra">Compra</option>
                                <option value="Producción">Producción</option>
                                <option value="Devolución">Devolución</option>
                                <option value="Ajuste">Ajuste de inventario</option>
                                <option value="Donación">Donación</option>
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
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Registrar Entrada
                        </button>
                        <small class="text-muted ms-3" id="infoStock"></small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Historial de Entradas -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-clock-history"></i> Historial de Entradas Recientes
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
                                        <span class="badge bg-success fs-6">
                                            +<?php echo $row['cantidad']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($row['motivo']); ?></span>
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
                    <i class="bi bi-info-circle"></i> No hay registros de entradas.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectProducto = document.getElementById('selectProducto');
    const infoStock = document.getElementById('infoStock');
    const infoEspecificaciones = document.getElementById('infoEspecificaciones');
    
    // Mostrar información del stock y especificaciones cuando seleccionan producto
    selectProducto.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stockActual = selectedOption.getAttribute('data-stock');
        const unidad = selectedOption.getAttribute('data-unidad');
        const especificaciones = selectedOption.getAttribute('data-especificaciones');
        
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
            infoStock.className = 'text-info ms-3 fw-bold';
            
            // Mostrar especificaciones
            if (especificaciones && especificaciones.trim() !== '') {
                infoEspecificaciones.textContent = especificaciones;
                infoEspecificaciones.className = 'text-success fw-bold';
            } else {
                infoEspecificaciones.textContent = 'Sin especificaciones adicionales';
                infoEspecificaciones.className = 'text-muted';
            }
        } else {
            infoStock.textContent = '';
            infoEspecificaciones.textContent = '';
        }
    });
    
    // Mostrar información inicial si hay un producto seleccionado
    if (selectProducto.value) {
        selectProducto.dispatchEvent(new Event('change'));
    }
});
</script>

<?php 
include '../footer.php'; 
$conn->close();
?>