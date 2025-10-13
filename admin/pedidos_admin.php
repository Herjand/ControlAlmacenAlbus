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

// Consultar pedidos
$sql = "SELECT p.*, 
               (SELECT COUNT(*) FROM detalle_pedidos dp WHERE dp.id_pedido = p.id_pedido) as total_productos,
               (SELECT SUM(dp.cantidad) FROM detalle_pedidos dp WHERE dp.id_pedido = p.id_pedido) as total_items
        FROM pedidos p 
        ORDER BY p.fecha_entrega ASC, p.created_at DESC";
$result = $conn->query($sql);

// Consultar clientes para el select
$sql_clientes = "SELECT id_cliente, empresa FROM clientes ORDER BY empresa";
$clientes_result = $conn->query($sql_clientes);

// Consultar productos para el select
$sql_productos = "SELECT id_producto, nombre, stock, unidad_medida FROM productos ORDER BY nombre";
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
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Fecha Entrega</th>
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
                                        <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['empresa_cliente']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['persona_contacto']); ?></small>
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
                                        <span class="badge <?php echo $estado_clase; ?>">
                                            <?php echo $row['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info ver-pedido" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#verPedidoModal"
                                                data-id="<?php echo $row['id_pedido']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editarPedidoModal"
                                                data-id="<?php echo $row['id_pedido']; ?>"
                                                data-empresa="<?php echo htmlspecialchars($row['empresa_cliente']); ?>"
                                                data-contacto="<?php echo htmlspecialchars($row['persona_contacto']); ?>"
                                                data-fecha="<?php echo $row['fecha_entrega']; ?>"
                                                data-estado="<?php echo $row['estado']; ?>">
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
      <form action="funcionalidad_pedidos/registrar_pedido.php" method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="nuevoPedidoLabel"><i class="bi bi-plus-circle"></i> Nuevo Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Cliente:</label>
                <select class="form-select" name="empresa_cliente" required id="selectCliente">
                  <option value="">Seleccionar cliente...</option>
                  <?php if ($clientes_result && $clientes_result->num_rows > 0): ?>
                    <?php while ($cliente = $clientes_result->fetch_assoc()): ?>
                      <option value="<?php echo htmlspecialchars($cliente['empresa']); ?>">
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
                <label class="form-label">Persona de Contacto:</label>
                <input type="text" class="form-control" name="persona_contacto" required maxlength="50" placeholder="Nombre del contacto">
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Fecha de Entrega:</label>
            <input type="date" class="form-control" name="fecha_entrega" required min="<?php echo date('Y-m-d'); ?>">
          </div>

          <!-- Productos del Pedido -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-box-seam"></i> Productos del Pedido</h6>
            </div>
            <div class="card-body">
              <div id="productosContainer">
                <!-- Los productos se agregarán dinámicamente aquí -->
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="agregarProducto">
                <i class="bi bi-plus"></i> Agregar Producto
              </button>
            </div>
          </div>
          
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Los pedidos se registran sin validar stock. 
            El sistema solo mostrará información sobre la disponibilidad para planificación.
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
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="funcionalidad_pedidos/editar_pedido.php" method="POST">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editarPedidoLabel"><i class="bi bi-pencil-square"></i> Editar Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_pedido" id="edit_id_pedido">
          <div class="mb-3">
            <label class="form-label">Empresa Cliente:</label>
            <input type="text" class="form-control" name="empresa_cliente" id="edit_empresa" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Persona de Contacto:</label>
            <input type="text" class="form-control" name="persona_contacto" id="edit_contacto" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha de Entrega:</label>
            <input type="date" class="form-control" name="fecha_entrega" id="edit_fecha" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Estado:</label>
            <select class="form-select" name="estado" id="edit_estado" required>
              <option value="Pendiente">Pendiente</option>
              <option value="Completado">Completado</option>
              <option value="Cancelado">Cancelado</option>
            </select>
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
document.addEventListener('DOMContentLoaded', function() {
    // Script para cargar datos en el modal de edición
    const editarModal = document.getElementById('editarPedidoModal');
    
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        document.getElementById('edit_id_pedido').value = button.getAttribute('data-id');
        document.getElementById('edit_empresa').value = button.getAttribute('data-empresa');
        document.getElementById('edit_contacto').value = button.getAttribute('data-contacto');
        document.getElementById('edit_fecha').value = button.getAttribute('data-fecha');
        document.getElementById('edit_estado').value = button.getAttribute('data-estado');
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
                        <br><small>Verifique que el archivo obtener_detalles_pedido.php exista en la carpeta funcionalidad_pedidos</small>
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

    // Gestión de productos en nuevo pedido
    let productoCount = 0;
    const agregarProductoBtn = document.getElementById('agregarProducto');
    
    if (agregarProductoBtn) {
        agregarProductoBtn.addEventListener('click', function() {
            productoCount++;
            const productosContainer = document.getElementById('productosContainer');
            
            const productoHTML = `
                <div class="row producto-item mb-2" id="producto-${productoCount}">
                    <div class="col-md-6">
                        <select class="form-select" name="productos[${productoCount}][id_producto]" required>
                            <option value="">Seleccionar producto...</option>
                            <?php 
                            if ($productos_result && $productos_result->num_rows > 0): 
                                $productos_result->data_seek(0); // Reset pointer
                                while ($producto = $productos_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $producto['id_producto']; ?>" data-stock="<?php echo $producto['stock']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?> (Stock: <?php echo $producto['stock']; ?>)
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
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
        });
    }

    // Agregar producto inicial si no hay ninguno
    if (document.getElementById('agregarProducto')) {
        document.getElementById('agregarProducto').click();
    }
});

// Función global para eliminar productos
function eliminarProducto(id) {
    const elemento = document.getElementById(`producto-${id}`);
    if (elemento) {
        elemento.remove();
    }
}
</script>

<?php 
include '../footer.php';
$conn->close();
?>