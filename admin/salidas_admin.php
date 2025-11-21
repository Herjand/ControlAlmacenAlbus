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

// Consultar productos para el select - INCLUYENDO ESPECIFICACIONES
$sql_productos = "SELECT id_producto, nombre, descripcion, stock, 
                         tamaño_peso, presentacion, cantidad_unidad, tipo_especifico 
                  FROM productos 
                  ORDER BY nombre, tipo_especifico, tamaño_peso";
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
                case '4': echo "Error: La cantidad no puede ser mayor a 1000 unidades"; break;
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
            <form action="funcionalidad_salidas/registrar_salida.php" method="POST" id="formSalida">
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
                            <label class="form-label">Cantidad a retirar:</label>
                            <input type="number" class="form-control" name="cantidad" required min="1" max="1000" value="1" id="inputCantidad">
                            <small class="text-muted">Máximo 1000 unidades por salida</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Motivo:</label>
                            <select class="form-select" name="motivo" required>
                                <option value="Venta">Venta/Despacho</option>
                                <option value="Donación">Donación</option>
                                <option value="Ajuste">Ajuste de inventario</option>
                                <option value="Uso interno">Uso interno</option>
                                <option value="Merma">Merma/Pérdida</option>
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
document.addEventListener('DOMContentLoaded', function() {
    const selectProducto = document.getElementById('selectProducto');
    const inputCantidad = document.getElementById('inputCantidad');
    const infoStock = document.getElementById('infoStock');
    const infoEspecificaciones = document.getElementById('infoEspecificaciones');
    const formSalida = document.getElementById('formSalida');
    
    // Mostrar información del stock y especificaciones cuando seleccionan producto
    selectProducto.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stockActual = parseInt(selectedOption.getAttribute('data-stock'));
        const especificaciones = selectedOption.getAttribute('data-especificaciones');
        
        if (stockActual !== null && selectedOption.value !== "") {
            infoStock.textContent = `Stock actual: ${stockActual} unidades`;
            
            // Cambiar color según stock
            if (stockActual === 0) {
                infoStock.className = 'text-danger ms-3 fw-bold';
                infoStock.textContent += ' ⚠️ SIN STOCK';
            } else if (stockActual < 10) {
                infoStock.className = 'text-danger ms-3 fw-bold';
                infoStock.textContent += ' ⚠️ STOCK BAJO';
            } else if (stockActual < 20) {
                infoStock.className = 'text-warning ms-3 fw-bold';
            } else {
                infoStock.className = 'text-success ms-3 fw-bold';
            }
            
            // Mostrar especificaciones
            if (especificaciones && especificaciones.trim() !== '') {
                infoEspecificaciones.textContent = especificaciones;
                infoEspecificaciones.className = 'text-info fw-bold';
            } else {
                infoEspecificaciones.textContent = 'Sin especificaciones adicionales';
                infoEspecificaciones.className = 'text-muted';
            }
            
            // Actualizar cantidad máxima (el menor entre stock actual y 1000)
            const maxPermitido = Math.min(stockActual, 1000);
            inputCantidad.setAttribute('max', maxPermitido);
            
        } else {
            infoStock.textContent = '';
            infoEspecificaciones.textContent = '';
            inputCantidad.removeAttribute('max');
        }
    });
    
    // Validar cantidad en tiempo real
    inputCantidad.addEventListener('input', function() {
        const cantidad = parseInt(this.value);
        
        // Validar límite de 1000
        if (cantidad > 1000) {
            this.value = 1000;
            mostrarAlerta('La cantidad no puede ser mayor a 1000 unidades por salida', 'warning');
            return;
        }
        
        // Validar stock disponible
        const selectedOption = selectProducto.options[selectProducto.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const stockActual = parseInt(selectedOption.getAttribute('data-stock'));
            
            if (cantidad > stockActual) {
                this.classList.add('is-invalid');
                infoStock.textContent += ` ❌ Supera stock (${stockActual})`;
                infoStock.className = 'text-danger ms-3 fw-bold';
            } else {
                this.classList.remove('is-invalid');
                // Restaurar color original del stock
                selectProducto.dispatchEvent(new Event('change'));
            }
        }
    });
    
    // Validar formulario antes de enviar
    formSalida.addEventListener('submit', function(e) {
        const cantidad = parseInt(inputCantidad.value);
        const selectedOption = selectProducto.options[selectProducto.selectedIndex];
        
        // Validar límite de 1000
        if (cantidad > 1000) {
            e.preventDefault();
            mostrarAlerta('Error: La cantidad no puede ser mayor a 1000 unidades por salida', 'danger');
            inputCantidad.focus();
            inputCantidad.select();
            return false;
        }
        
        // Validar cantidad mínima
        if (cantidad < 1) {
            e.preventDefault();
            mostrarAlerta('Error: La cantidad debe ser al menos 1 unidad', 'danger');
            inputCantidad.focus();
            return false;
        }
        
        // Validar stock disponible
        if (selectedOption && selectedOption.value) {
            const stockActual = parseInt(selectedOption.getAttribute('data-stock'));
            
            if (cantidad > stockActual) {
                e.preventDefault();
                mostrarAlerta(`Error: No hay suficiente stock.\n\nStock disponible: ${stockActual}\nCantidad a retirar: ${cantidad}\n\nPor favor, ajuste la cantidad.`, 'danger');
                inputCantidad.focus();
                inputCantidad.select();
                return false;
            }
        }
        
        return true;
    });
    
    // Función para mostrar alertas temporales
    function mostrarAlerta(mensaje, tipo) {
        // Remover alertas existentes
        const alertasExistentes = document.querySelectorAll('.alert-temporario');
        alertasExistentes.forEach(alerta => alerta.remove());
        
        // Crear nueva alerta
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show alert-temporario`;
        alerta.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill"></i> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insertar después del título
        const titulo = document.querySelector('h2');
        titulo.parentNode.insertBefore(alerta, titulo.nextSibling);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 5000);
    }
    
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