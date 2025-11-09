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

// Consultar salidas recientes
$sql_salidas = "SELECT s.*, p.nombre as producto_nombre, u.nombre as usuario_nombre 
                FROM salidas s 
                JOIN productos p ON s.id_producto = p.id_producto 
                JOIN usuarios u ON s.usuario_responsable = u.id_usuario 
                ORDER BY s.fecha DESC LIMIT 10";
$salidas_result = $conn->query($sql_salidas);
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-up-square"></i> Registrar Salidas de Productos</h2>
    <p class="text-muted">Registra la salida de productos del almacén.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> Salida registrada correctamente.
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

    <div class="row">
        <!-- Formulario de Salida -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-dash-circle"></i> Nueva Salida
                </div>
                <div class="card-body">
                    <form action="funcionalidad_salidas/registrar_salida.php" method="POST" id="formSalida">
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
                            <input type="number" class="form-control" name="cantidad" required min="1" 
                                   placeholder="Cantidad a retirar" id="inputCantidad" onkeyup="validarStock()">
                            <div class="mt-1">
                                <small id="mensajeStock" class="text-muted"></small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo <span class="text-danger">*</span>:</label>
                            <select class="form-select" name="motivo" required>
                                <option value="">Seleccionar motivo...</option>
                                <option value="Venta">Venta</option>
                                <option value="Pedido">Pedido</option>
                                <option value="Consumo Interno">Consumo Interno</option>
                                <option value="Muestra">Muestra</option>
                                <option value="Devolución">Devolución</option>
                                <option value="Ajuste de Inventario">Ajuste de Inventario</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones <span class="text-muted">(Opcional)</span>:</label>
                            <textarea class="form-control" name="observaciones" rows="3" placeholder="Detalles adicionales de la salida..." maxlength="100"></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <small>
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Advertencia:</strong> Verifica que el stock sea suficiente antes de registrar la salida.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-danger w-100" id="btnRegistrar">
                            <i class="bi bi-check-circle"></i> Registrar Salida
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Salidas Recientes -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-clock-history"></i> Salidas Recientes
                </div>
                <div class="card-body">
                    <?php if ($salidas_result && $salidas_result->num_rows > 0): ?>
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
                                    <?php while ($salida = $salidas_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo htmlspecialchars($salida['producto_nombre']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">-<?php echo $salida['cantidad']; ?></span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($salida['motivo']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('H:i', strtotime($salida['fecha'])); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($salida['usuario_nombre']); ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-box-arrow-up display-4"></i>
                            <p class="mt-2">No hay salidas registradas recientemente.</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- BOTÓN ELIMINADO -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let stockActual = 0;

function actualizarInfoProducto() {
    const select = document.getElementById('selectProducto');
    const infoElement = document.getElementById('infoProducto');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        stockActual = parseInt(selectedOption.getAttribute('data-stock'));
        const stockMinimo = selectedOption.getAttribute('data-stock-minimo');
        const presentacion = selectedOption.getAttribute('data-presentacion');
        const descripcion = selectedOption.getAttribute('data-descripcion');
        const especificaciones = selectedOption.getAttribute('data-especificaciones');
        
        let infoHTML = `
            <div class="row">
                <div class="col-12">
                    <h6 class="text-danger mb-2">
                        <i class="bi bi-box-seam"></i> Información del Producto Seleccionado
                    </h6>
                </div>
                <div class="col-md-6">
                    <strong>Stock Actual:</strong><br>
                    <span class="${stockActual == 0 ? 'text-danger' : (stockActual <= stockMinimo ? 'text-warning' : 'text-success')}">
                        <i class="bi ${stockActual == 0 ? 'bi-exclamation-triangle' : (stockActual <= stockMinimo ? 'bi-exclamation-triangle' : 'bi-check-circle')}"></i>
                        ${stockActual} unidades
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
        
        validarStock();
    } else {
        infoElement.innerHTML = `
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> Seleccione un producto para ver detalles completos
            </small>`;
        infoElement.className = 'alert alert-light border';
        stockActual = 0;
    }
}

function validarStock() {
    const cantidadInput = document.getElementById('inputCantidad');
    const mensajeElement = document.getElementById('mensajeStock');
    const btnRegistrar = document.getElementById('btnRegistrar');
    const cantidad = parseInt(cantidadInput.value) || 0;
    
    if (cantidad > stockActual) {
        mensajeElement.textContent = `❌ Stock insuficiente. Disponible: ${stockActual}`;
        mensajeElement.className = 'text-danger';
        btnRegistrar.disabled = true;
        cantidadInput.classList.add('is-invalid');
    } else if (cantidad > 0) {
        mensajeElement.textContent = `✅ Stock disponible: ${stockActual}`;
        mensajeElement.className = 'text-success';
        btnRegistrar.disabled = false;
        cantidadInput.classList.remove('is-invalid');
    } else {
        mensajeElement.textContent = 'Ingrese la cantidad a retirar';
        mensajeElement.className = 'text-muted';
        btnRegistrar.disabled = false;
        cantidadInput.classList.remove('is-invalid');
    }
}

// Inicializar la información al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarInfoProducto();
});

// Validar formulario antes de enviar
document.getElementById('formSalida').addEventListener('submit', function(e) {
    const cantidad = parseInt(document.getElementById('inputCantidad').value) || 0;
    if (cantidad > stockActual) {
        e.preventDefault();
        alert('Error: La cantidad solicitada supera el stock disponible.');
    }
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>