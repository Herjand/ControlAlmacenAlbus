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
$error_message = isset($_GET['message']) ? $_GET['message'] : '';

// Consultar pedidos con información de clientes
$sql = "SELECT p.*, c.contacto, c.telefono, c.email,
               (SELECT COUNT(*) FROM detalle_pedidos dp WHERE dp.id_pedido = p.id_pedido) as total_productos,
               (SELECT SUM(dp.cantidad) FROM detalle_pedidos dp WHERE dp.id_pedido = p.id_pedido) as total_items
        FROM pedidos p 
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        ORDER BY p.fecha_entrega ASC, p.created_at DESC";
$result = $conn->query($sql);

// Consultar clientes para el select
$sql_clientes = "SELECT id_cliente, empresa, contacto FROM clientes ORDER BY empresa";
$clientes_result = $conn->query($sql_clientes);

// Consultar productos para el select
$sql_productos = "SELECT id_producto, nombre, stock, tamaño_peso, presentacion, cantidad_unidad, tipo_especifico 
                  FROM productos ORDER BY nombre";
$productos_result = $conn->query($sql_productos);
?>

<div class="container-fluid">
    <h2><i class="bi bi-cart-check"></i> Gestión de Pedidos</h2>
    <p class="text-muted">Administra los pedidos de clientes y su estado de entrega.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
            switch($success) {
                case '1': echo "Pedido registrado correctamente"; break;
                case '2': echo "Pedido actualizado correctamente"; break;
                case '3': echo "Pedido eliminado correctamente"; break;
                default: echo "Operación realizada correctamente";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            <?php 
            switch($error) {
                case '1': echo "Error al registrar el pedido"; break;
                case '2': echo "Error: Campos vacíos"; break;
                case '3': echo "Error al eliminar el pedido"; break;
                default: echo "Error en la operación";
            }
            ?>
            <?php if ($error_message): ?>
                <br><small><?php echo htmlspecialchars($error_message); ?></small>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5>TOTAL PEDIDOS</h5>
                    <h3><?php echo $result->num_rows; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h5>PENDIENTES</h5>
                    <h3>
                        <?php 
                        $sql_pendientes = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'";
                        echo $conn->query($sql_pendientes)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5>COMPLETADOS</h5>
                    <h3>
                        <?php 
                        $sql_completados = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Completado'";
                        echo $conn->query($sql_completados)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h5>CANCELADOS</h5>
                    <h3>
                        <?php 
                        $sql_cancelados = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Cancelado'";
                        echo $conn->query($sql_cancelados)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Herramientas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoPedidoModal">
                        <i class="bi bi-plus-circle"></i> Nuevo Pedido
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar pedido..." id="buscarPedido">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul"></i> Lista de Pedidos
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID/Remisión</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Fecha Entrega</th>
                                <th>Lugar Entrega</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                // Color según estado
                                $estado_clase = '';
                                switch($row['estado']) {
                                    case 'Pendiente': $estado_clase = 'bg-warning text-dark'; break;
                                    case 'Completado': $estado_clase = 'bg-success text-white'; break;
                                    case 'Cancelado': $estado_clase = 'bg-secondary text-white'; break;
                                    default: $estado_clase = 'bg-info text-white';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($row['id_pedido'], 4, '0', STR_PAD_LEFT); ?></strong>
                                        <?php if ($row['nota_remision']): ?>
                                            <br><small class="text-primary">Rem: <?php echo htmlspecialchars($row['nota_remision']); ?></small>
                                        <?php endif; ?>
                                        <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['empresa_cliente']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['persona_contacto']); ?></small>
                                        <?php if ($row['telefono']): ?>
                                            <br><small class="text-info">📞 <?php echo htmlspecialchars($row['telefono']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $row['total_productos']; ?> productos</span>
                                        <br><small class="text-muted"><?php echo $row['total_items']; ?> items</small>
                                    </td>
                                    <td>
                                        <?php 
                                        $fecha_entrega = new DateTime($row['fecha_entrega']);
                                        $hoy = new DateTime();
                                        $diferencia = $hoy->diff($fecha_entrega)->days;
                                        
                                        if ($fecha_entrega < $hoy && $row['estado'] == 'Pendiente') {
                                            echo '<span class="text-danger"><strong>' . date('d/m/Y', strtotime($row['fecha_entrega'])) . '</strong></span>';
                                            echo '<br><small class="text-danger">⚠️ Vencido</small>';
                                        } elseif ($diferencia <= 2 && $row['estado'] == 'Pendiente') {
                                            echo '<span class="text-warning"><strong>' . date('d/m/Y', strtotime($row['fecha_entrega'])) . '</strong></span>';
                                            echo '<br><small class="text-warning">🔔 Próximo</small>';
                                        } else {
                                            echo date('d/m/Y', strtotime($row['fecha_entrega']));
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['lugar_entrega']): ?>
                                            <small><?php echo htmlspecialchars($row['lugar_entrega']); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">- Sin especificar -</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $estado_clase; ?>">
                                            <?php echo $row['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#verPedidoModal"
                                                data-id="<?php echo $row['id_pedido']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editarPedidoModal"
                                                data-id="<?php echo $row['id_pedido']; ?>"
                                                data-cliente="<?php echo $row['id_cliente']; ?>"
                                                data-empresa="<?php echo htmlspecialchars($row['empresa_cliente']); ?>"
                                                data-contacto="<?php echo htmlspecialchars($row['persona_contacto']); ?>"
                                                data-fecha="<?php echo $row['fecha_entrega']; ?>"
                                                data-estado="<?php echo $row['estado']; ?>"
                                                data-remision="<?php echo htmlspecialchars($row['nota_remision']); ?>"
                                                data-lugar="<?php echo htmlspecialchars($row['lugar_entrega']); ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="funcionalidad_pedidos/eliminar_pedido.php?id=<?php echo $row['id_pedido']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Está seguro que desea eliminar este pedido?\n\nSe eliminarán todos los productos relacionados.\n\nEsta acción no se puede deshacer.')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No hay pedidos registrados en el sistema.
                    <br><small>Haz clic en "Nuevo Pedido" para agregar el primero.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL NUEVO PEDIDO -->
<div class="modal fade" id="nuevoPedidoModal" tabindex="-1" aria-labelledby="nuevoPedidoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_pedidos/registrar_pedido.php" method="POST" id="formNuevoPedido">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="nuevoPedidoLabel"><i class="bi bi-plus-circle"></i> Nuevo Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Cliente <span class="text-danger">*</span>:</label>
                <select class="form-select" name="id_cliente" required id="selectCliente" onchange="actualizarDatosCliente()">
                  <option value="">Seleccionar cliente...</option>
                  <?php if ($clientes_result && $clientes_result->num_rows > 0): ?>
                    <?php while ($cliente = $clientes_result->fetch_assoc()): ?>
                      <option value="<?php echo $cliente['id_cliente']; ?>" 
                              data-empresa="<?php echo htmlspecialchars($cliente['empresa']); ?>"
                              data-contacto="<?php echo htmlspecialchars($cliente['contacto']); ?>">
                        <?php echo htmlspecialchars($cliente['empresa']); ?>
                      </option>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <option value="" disabled>No hay clientes registrados</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Persona de Contacto <span class="text-danger">*</span>:</label>
                <input type="text" class="form-control" name="persona_contacto" id="persona_contacto" required maxlength="50" placeholder="Seleccione un cliente">
              </div>
            </div>
          </div>

          <!-- Campo oculto para empresa_cliente -->
          <input type="hidden" name="empresa_cliente" id="empresa_cliente">

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nota de Remisión <span class="text-muted">(Opcional)</span>:</label>
                <input type="text" class="form-control" name="nota_remision" maxlength="15" placeholder="Ej: 12345">
                <small class="text-muted">Número de remisión (opcional)</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Fecha de Entrega <span class="text-danger">*</span>:</label>
                <input type="date" class="form-control" name="fecha_entrega" required min="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Lugar de Entrega <span class="text-danger">*</span>:</label>
            <input type="text" class="form-control" name="lugar_entrega" maxlength="100" placeholder="Dirección específica de entrega" required>
          </div>

          <!-- Productos del Pedido -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-box-seam"></i> Productos del Pedido <span class="text-danger">*</span></h6>
              <small class="text-muted">Debe agregar al menos un producto</small>
            </div>
            <div class="card-body">
              <div id="productosContainer">
                <!-- Los productos se agregarán dinámicamente aquí -->
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="agregarProducto">
                <i class="bi bi-plus"></i> Agregar Producto
              </button>
            </div>
          </div>
          
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Los pedidos se registran sin validar stock. 
            El sistema solo mostrará información sobre la disponibilidad para planificación.
          </div>

          <!-- Indicación de campos obligatorios -->
          <div class="alert alert-warning">
            <small>
              <strong>ℹ️ Campos obligatorios:</strong> Cliente, Persona de Contacto, Fecha de Entrega, Lugar de Entrega, Productos<br>
              <strong>📝 Campos opcionales:</strong> Nota de Remisión
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Crear Pedido
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR PEDIDO -->
<div class="modal fade" id="editarPedidoModal" tabindex="-1" aria-labelledby="editarPedidoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_pedidos/editar_pedido.php" method="POST">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editarPedidoLabel"><i class="bi bi-pencil-square"></i> Editar Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_pedido" id="edit_id_pedido">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Cliente <span class="text-danger">*</span>:</label>
                <select class="form-select" name="id_cliente" id="edit_id_cliente" required>
                  <option value="">Seleccionar cliente...</option>
                  <?php 
                  if ($clientes_result && $clientes_result->num_rows > 0): 
                    $clientes_result->data_seek(0); // Reset pointer
                    while ($cliente = $clientes_result->fetch_assoc()): 
                  ?>
                    <option value="<?php echo $cliente['id_cliente']; ?>">
                      <?php echo htmlspecialchars($cliente['empresa']); ?>
                    </option>
                  <?php endwhile; endif; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Persona de Contacto <span class="text-danger">*</span>:</label>
                <input type="text" class="form-control" name="persona_contacto" id="edit_contacto" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nota de Remisión <span class="text-muted">(Opcional)</span>:</label>
                <input type="text" class="form-control" name="nota_remision" id="edit_remision" maxlength="15">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Fecha de Entrega <span class="text-danger">*</span>:</label>
                <input type="date" class="form-control" name="fecha_entrega" id="edit_fecha" required>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Lugar de Entrega <span class="text-danger">*</span>:</label>
            <input type="text" class="form-control" name="lugar_entrega" id="edit_lugar" maxlength="100" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Estado <span class="text-danger">*</span>:</label>
            <select class="form-select" name="estado" id="edit_estado" required>
              <option value="Pendiente">Pendiente</option>
              <option value="Completado">Completado</option>
              <option value="Cancelado">Cancelado</option>
            </select>
          </div>

          <!-- Indicación de campos obligatorios -->
          <div class="alert alert-warning">
            <small>
              <strong>ℹ️ Campos obligatorios:</strong> Cliente, Persona de Contacto, Fecha de Entrega, Lugar de Entrega, Estado<br>
              <strong>📝 Campos opcionales:</strong> Nota de Remisión
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Actualizar Pedido
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- MODAL VER PEDIDO -->
<div class="modal fade" id="verPedidoModal" tabindex="-1" aria-labelledby="verPedidoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="verPedidoLabel"><i class="bi bi-eye"></i> Detalles del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="detallesPedido">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del pedido...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Gestión de productos en nuevo pedido
let productoCount = 0;

function agregarProducto() {
    productoCount++;
    const productosContainer = document.getElementById('productosContainer');
    
    const productoHTML = `
        <div class="row producto-item mb-2" id="producto-${productoCount}">
            <div class="col-md-6">
                <select class="form-select" name="productos[${productoCount}][id_producto]" required onchange="actualizarInfoProducto(this)">
                    <option value="">Seleccionar producto...</option>
                    <?php 
                    if ($productos_result && $productos_result->num_rows > 0): 
                        $productos_result->data_seek(0);
                        while ($producto = $productos_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $producto['id_producto']; ?>" 
                                data-stock="<?php echo $producto['stock']; ?>"
                                data-especificaciones="<?php echo htmlspecialchars($producto['tamaño_peso'] . ' ' . $producto['cantidad_unidad'] . ' ' . $producto['tipo_especifico']); ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?> (Stock: <?php echo $producto['stock']; ?>)
                        </option>
                    <?php endwhile; endif; ?>
                </select>
                <small class="text-muted info-producto" id="info-producto-${productoCount}"></small>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="productos[${productoCount}][cantidad]" required min="1" value="1" placeholder="Cantidad">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${productoCount})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    productosContainer.insertAdjacentHTML('beforeend', productoHTML);
}

// Función para actualizar datos del cliente
function actualizarDatosCliente() {
    const select = document.getElementById('selectCliente');
    const contactoInput = document.getElementById('persona_contacto');
    const empresaInput = document.getElementById('empresa_cliente');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        contactoInput.value = selectedOption.getAttribute('data-contacto');
        empresaInput.value = selectedOption.getAttribute('data-empresa');
    } else {
        contactoInput.value = '';
        empresaInput.value = '';
    }
}

// Función para actualizar información del producto seleccionado
function actualizarInfoProducto(select) {
    const selectedOption = select.options[select.selectedIndex];
    const productoId = select.name.match(/\[(\d+)\]/)[1];
    const infoElement = document.getElementById(`info-producto-${productoId}`);
    
    if (selectedOption && selectedOption.value) {
        const especificaciones = selectedOption.getAttribute('data-especificaciones');
        const stock = selectedOption.getAttribute('data-stock');
        infoElement.textContent = especificaciones ? especificaciones.trim() : 'Sin especificaciones';
        infoElement.className = 'text-info info-producto';
        
        // Actualizar el campo de cantidad máxima basado en el stock
        const cantidadInput = select.closest('.row').querySelector('input[name*="cantidad"]');
        if (cantidadInput) {
            cantidadInput.setAttribute('max', stock);
        }
    } else {
        infoElement.textContent = '';
    }
}

// Función global para eliminar productos
function eliminarProducto(id) {
    const elemento = document.getElementById(`producto-${id}`);
    if (elemento) {
        elemento.remove();
    }
}

// Validación del formulario antes de enviar
function validarFormularioPedido(e) {
    // Verificar que haya al menos un producto válido
    const productosSelects = document.querySelectorAll('select[name*="id_producto"]');
    let productosValidos = 0;
    
    productosSelects.forEach(select => {
        if (select.value && select.value !== '') {
            productosValidos++;
        }
    });
    
    if (productosValidos === 0) {
        e.preventDefault();
        alert('Error: Debe agregar al menos un producto válido al pedido.');
        return false;
    }
    
    // Verificar que todas las cantidades sean válidas
    const cantidadInputs = document.querySelectorAll('input[name*="cantidad"]');
    let cantidadesValidas = true;
    
    cantidadInputs.forEach(input => {
        const cantidad = parseInt(input.value);
        if (cantidad <= 0 || isNaN(cantidad)) {
            cantidadesValidas = false;
            input.focus();
        }
    });
    
    if (!cantidadesValidas) {
        e.preventDefault();
        alert('Error: Todas las cantidades deben ser números mayores a 0.');
        return false;
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Agregar producto inicial
    agregarProducto();

    // Configurar botón agregar producto
    const agregarProductoBtn = document.getElementById('agregarProducto');
    if (agregarProductoBtn) {
        agregarProductoBtn.addEventListener('click', agregarProducto);
    }

    // Configurar validación del formulario
    const formPedido = document.getElementById('formNuevoPedido');
    if (formPedido) {
        formPedido.addEventListener('submit', validarFormularioPedido);
    }

    // Script para cargar datos en el modal de edición
    const editarModal = document.getElementById('editarPedidoModal');
    
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        document.getElementById('edit_id_pedido').value = button.getAttribute('data-id');
        document.getElementById('edit_id_cliente').value = button.getAttribute('data-cliente');
        document.getElementById('edit_contacto').value = button.getAttribute('data-contacto');
        document.getElementById('edit_fecha').value = button.getAttribute('data-fecha');
        document.getElementById('edit_estado').value = button.getAttribute('data-estado');
        document.getElementById('edit_remision').value = button.getAttribute('data-remision');
        document.getElementById('edit_lugar').value = button.getAttribute('data-lugar');
    });

    // Cargar detalles del pedido en el modal VER
    const verPedidoModal = document.getElementById('verPedidoModal');
    
    verPedidoModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const idPedido = button.getAttribute('data-id');
        const detallesContainer = document.getElementById('detallesPedido');
        
        // Mostrar spinner de carga
        detallesContainer.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando detalles del pedido...</p>
            </div>
        `;
        
        // Cargar detalles via AJAX
        fetch('funcionalidad_pedidos/obtener_detalles_pedido.php?id=' + idPedido)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
                return response.text();
            })
            .then(data => {
                detallesContainer.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                detallesContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Error al cargar los detalles del pedido.
                        <br><small>${error.message}</small>
                    </div>
                `;
            });
    });

    // Búsqueda en tiempo real
    const buscarInput = document.getElementById('buscarPedido');
    if (buscarInput) {
        buscarInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Limpiar formulario cuando se cierra el modal
    const nuevoPedidoModal = document.getElementById('nuevoPedidoModal');
    if (nuevoPedidoModal) {
        nuevoPedidoModal.addEventListener('hidden.bs.modal', function() {
            // Resetear contador de productos
            productoCount = 0;
            // Limpiar contenedor de productos
            document.getElementById('productosContainer').innerHTML = '';
            // Agregar producto inicial
            agregarProducto();
            // Resetear formulario
            document.getElementById('formNuevoPedido').reset();
        });
    }
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>