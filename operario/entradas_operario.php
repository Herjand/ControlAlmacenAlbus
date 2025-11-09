<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_operario.php';

// Manejar mensajes
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Consultar productos para el select con más detalles
$sql_productos = "SELECT 
                    id_producto, 
                    nombre, 
                    descripcion,
                    stock, 
                    stock_minimo,
                    presentacion, 
                    tamaño_peso, 
                    cantidad_unidad,
                    tipo_especifico
                  FROM productos 
                  ORDER BY nombre";
$productos_result = $conn->query($sql_productos);

// Consultar entradas recientes
$sql_entradas = "SELECT e.*, p.nombre as producto_nombre, u.nombre as usuario_nombre 
                 FROM entradas e 
                 JOIN productos p ON e.id_producto = p.id_producto 
                 JOIN usuarios u ON e.usuario_responsable = u.id_usuario 
                 ORDER BY e.fecha DESC LIMIT 10";
$entradas_result = $conn->query($sql_entradas);
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-down-square"></i> Registrar Entradas de Productos</h2>
    <p class="text-muted">Registra el ingreso de productos al almacén.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> Entrada registrada correctamente.
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

    <div class="row">
        <!-- Formulario de Entrada -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-plus-circle"></i> Nueva Entrada
                </div>
                <div class="card-body">
                    <form action="funcionalidad_entradas/registrar_entrada.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Producto <span class="text-danger">*</span>:</label>
                            <select class="form-select" name="id_producto" required id="selectProducto" onchange="actualizarInfoProducto()">
                                <option value="">Seleccionar producto...</option>
                                <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                                    <?php while ($producto = $productos_result->fetch_assoc()): ?>
                                        <?php
                                        // Construir texto de especificaciones
                                        $especificaciones = [];
                                        if (!empty($producto['tamaño_peso'])) $especificaciones[] = $producto['tamaño_peso'];
                                        if (!empty($producto['cantidad_unidad'])) $especificaciones[] = $producto['cantidad_unidad'];
                                        if (!empty($producto['tipo_especifico'])) $especificaciones[] = $producto['tipo_especifico'];
                                        $espec_text = implode(' • ', $especificaciones);
                                        ?>
                                        <option value="<?php echo $producto['id_producto']; ?>" 
                                                data-stock="<?php echo $producto['stock']; ?>"
                                                data-stock-minimo="<?php echo $producto['stock_minimo']; ?>"
                                                data-presentacion="<?php echo htmlspecialchars($producto['presentacion']); ?>"
                                                data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>"
                                                data-especificaciones="<?php echo htmlspecialchars($espec_text); ?>">
                                            <?php echo htmlspecialchars($producto['nombre']); ?> 
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay productos registrados</option>
                                <?php endif; ?>
                            </select>
                            <div class="mt-2">
                                <div id="infoProducto" class="alert alert-light border">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> Seleccione un producto para ver detalles completos
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span>:</label>
                            <input type="number" class="form-control" name="cantidad" required min="1" placeholder="Cantidad a ingresar" id="inputCantidad">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo <span class="text-danger">*</span>:</label>
                            <select class="form-select" name="motivo" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="Compra">Compra</option>
                                <option value="Devolución">Devolución</option>
                                <option value="Ajuste de Inventario">Ajuste de Inventario</option>
                                <option value="Producción">Producción</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones <span class="text-muted">(Opcional)</span>:</label>
                            <textarea class="form-control" name="observaciones" rows="3" placeholder="Detalles adicionales de la entrada..." maxlength="100"></textarea>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle"></i> 
                                <strong>Nota:</strong> El stock se actualizará automáticamente después de registrar la entrada.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Registrar Entrada
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Entradas Recientes -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-clock-history"></i> Entradas Recientes
                </div>
                <div class="card-body">
                    <?php if ($entradas_result && $entradas_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($entrada = $entradas_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo htmlspecialchars($entrada['producto_nombre']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">+<?php echo $entrada['cantidad']; ?></span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($entrada['motivo']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('H:i', strtotime($entrada['fecha'])); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($entrada['usuario_nombre']); ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">No hay entradas registradas recientemente.</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- BOTÓN ELIMINADO -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarInfoProducto() {
    const select = document.getElementById('selectProducto');
    const infoElement = document.getElementById('infoProducto');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const stock = selectedOption.getAttribute('data-stock');
        const stockMinimo = selectedOption.getAttribute('data-stock-minimo');
        const presentacion = selectedOption.getAttribute('data-presentacion');
        const descripcion = selectedOption.getAttribute('data-descripcion');
        const especificaciones = selectedOption.getAttribute('data-especificaciones');
        
        let infoHTML = `
            <div class="row">
                <div class="col-12">
                    <h6 class="text-success mb-2">
                        <i class="bi bi-box-seam"></i> Información del Producto Seleccionado
                    </h6>
                </div>
                <div class="col-md-6">
                    <strong>Stock Actual:</strong><br>
                    <span class="${stock <= stockMinimo ? 'text-danger' : 'text-success'}">
                        <i class="bi ${stock <= stockMinimo ? 'bi-exclamation-triangle' : 'bi-check-circle'}"></i>
                        ${stock} unidades
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>Stock Mínimo:</strong><br>
                    <span class="text-info">${stockMinimo} unidades</span>
                </div>`;
        
        if (presentacion && presentacion.trim() !== '') {
            infoHTML += `
                <div class="col-12 mt-2">
                    <strong>Presentación:</strong><br>
                    <span class="text-primary">${presentacion}</span>
                </div>`;
        }
        
        if (especificaciones && especificaciones.trim() !== '') {
            infoHTML += `
                <div class="col-12 mt-2">
                    <strong>Especificaciones:</strong><br>
                    <small class="text-muted">${especificaciones}</small>
                </div>`;
        }
        
        if (descripcion && descripcion.trim() !== '') {
            infoHTML += `
                <div class="col-12 mt-2">
                    <strong>Descripción:</strong><br>
                    <small class="text-muted"><em>${descripcion}</em></small>
                </div>`;
        }
        
        infoHTML += `</div>`;
        
        infoElement.innerHTML = infoHTML;
        infoElement.className = 'alert alert-info border';
        
    } else {
        infoElement.innerHTML = `
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> Seleccione un producto para ver detalles completos
            </small>`;
        infoElement.className = 'alert alert-light border';
    }
}

// Inicializar la información al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarInfoProducto();
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>