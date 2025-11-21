<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Manejar mensajes de éxito/error
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Consultar productos químicos
$sql = "SELECT * FROM productos_quimicos ORDER BY nombre ASC";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h2><i class="bi bi-droplet"></i> Gestión de Productos Químicos</h2>
    <p class="text-muted">Administra el inventario de productos químicos para la producción.</p>

    <!-- Mostrar mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
            switch($success) {
                case '1': echo "Producto químico registrado correctamente"; break;
                case '2': echo "Producto químico actualizado correctamente"; break;
                case '3': echo "Producto químico eliminado correctamente"; break;
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
                case '1': echo "Error al registrar el producto químico"; break;
                case '2': echo "Error: Campos vacíos o inválidos"; break;
                case '3': echo "Error al eliminar el producto químico"; break;
                case '4': echo "No se puede eliminar: Producto tiene movimientos registrados"; break;
                case '5': echo "Error: Ya existe un producto químico con ese nombre"; break;
                case '6': echo "Error: Stock inicial excede el límite máximo (10,000 unidades)"; break;
                case '7': echo "Error: Stock mínimo excede el límite máximo (10,000 unidades)"; break;
                case '8': echo "Error: Stock mínimo no puede ser mayor que stock inicial"; break;
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
                    <h5>TOTAL QUÍMICOS</h5>
                    <h3><?php echo $result->num_rows; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5>STOCK ÓPTIMO</h5>
                    <h3>
                        <?php 
                        $sql_optimo = "SELECT COUNT(*) as total FROM productos_quimicos WHERE stock > stock_minimo";
                        echo $conn->query($sql_optimo)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h5>STOCK BAJO</h5>
                    <h3>
                        <?php 
                        $sql_bajo = "SELECT COUNT(*) as total FROM productos_quimicos WHERE stock <= stock_minimo AND stock > 0";
                        echo $conn->query($sql_bajo)->fetch_assoc()['total'];
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h5>SIN STOCK</h5>
                    <h3>
                        <?php 
                        $sql_sin_stock = "SELECT COUNT(*) as total FROM productos_quimicos WHERE stock = 0";
                        echo $conn->query($sql_sin_stock)->fetch_assoc()['total'];
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoQuimicoModal">
                        <i class="bi bi-plus-circle"></i> Nuevo Producto Químico
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar producto químico..." id="buscarQuimico">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos Químicos -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul"></i> Inventario de Productos Químicos
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Producto Químico</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                $clase_stock = '';
                                $texto_estado = '';

                                if ($row['stock'] == 0) {
                                    $clase_stock = 'bg-danger text-white';
                                    $texto_estado = 'Sin Stock';
                                } elseif ($row['stock'] <= $row['stock_minimo']) {
                                    $clase_stock = 'bg-warning text-dark';
                                    $texto_estado = 'Stock Bajo';
                                } else {
                                    $clase_stock = 'bg-success text-white';
                                    $texto_estado = 'Óptimo';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $clase_stock; ?> fs-6">
                                            <?php echo number_format($row['stock']); ?> unidades
                                        </span>
                                    </td>
                                    <td><?php echo number_format($row['stock_minimo']); ?> unidades</td>
                                    <td>
                                        <span class="badge <?php echo $clase_stock; ?>">
                                            <?php echo $texto_estado; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($row['updated_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editarQuimicoModal"
                                                data-id="<?php echo $row['id_quimico']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                data-stock="<?php echo $row['stock']; ?>"
                                                data-stockmin="<?php echo $row['stock_minimo']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="funcionalidad_quimicos/eliminar_quimico.php?id=<?php echo $row['id_quimico']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('⚠️ ¿ESTÁ SEGURO que desea eliminar este producto químico?\n\nSe eliminarán TODOS los movimientos relacionados.\n\nEsta acción NO se puede deshacer.')">
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
                    <i class="bi bi-info-circle"></i> No hay productos químicos registrados en el sistema.
                    <br><small>Haz clic en "Nuevo Producto Químico" para agregar el primero.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL NUEVO PRODUCTO QUÍMICO -->
<div class="modal fade" id="nuevoQuimicoModal" tabindex="-1" aria-labelledby="nuevoQuimicoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="funcionalidad_quimicos/registrar_quimico.php" method="POST" onsubmit="return validarStockQuimico()">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="nuevoQuimicoLabel"><i class="bi bi-plus-circle"></i> Nuevo Producto Químico</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre del Producto Químico:</label>
            <select class="form-select" name="nombre" id="selectQuimico" required onchange="actualizarCamposQuimico()">
              <option value="">Seleccionar tipo de químico...</option>
              <option value="SODA CAUSTICA">SODA CAUSTICA</option>
              <option value="CARBONATO DE SODIO">CARBONATO DE SODIO</option>
              <option value="BLANQUEADOR OPTICO">BLANQUEADOR OPTICO</option>
              <option value="AGUA OXIGENADA">AGUA OXIGENADA</option>
              <option value="ACIDO FORMICO">ACIDO FORMICO</option>
              <option value="SECUESTRANTE">SECUESTRANTE</option>
              <option value="COAGULANTE">COAGULANTE</option>
              <option value="INCASOF">INCASOF</option>
              <option value="TANAZIM">TANAZIM</option>
              <option value="ALCALIFONO">ALCALIFONO</option>
              <option value="otros">Otros (especificar)</option>
            </select>
            <input type="text" class="form-control mt-2 d-none" name="nombre_personalizado" id="nombrePersonalizado" 
                   placeholder="Especificar nombre del producto químico" maxlength="100">
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Inicial:</label>
                <input type="number" class="form-control" name="stock" id="stockInicial" value="0" min="0" max="10000" required>
                <small class="text-muted">Cantidad en unidades en inventario (Máximo: 10,000)</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Mínimo:</label>
                <input type="number" class="form-control" name="stock_minimo" id="stockMinimo" value="10" min="1" max="10000" required>
                <small class="text-muted">Alerta cuando el stock sea menor (Máximo: 10,000)</small>
              </div>
            </div>
          </div>

          <div class="alert alert-info">
            <small>
              <strong>📝 Límites establecidos:</strong><br>
              • <strong>Stock Inicial:</strong> Máximo 10,000 unidades<br>
              • <strong>Stock Mínimo:</strong> Máximo 10,000 unidades<br>
              • <strong>Relación:</strong> Stock mínimo debe ser menor o igual al stock inicial
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Guardar Producto Químico
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR PRODUCTO QUÍMICO -->
<div class="modal fade" id="editarQuimicoModal" tabindex="-1" aria-labelledby="editarQuimicoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="funcionalidad_quimicos/editar_quimico.php" method="POST" onsubmit="return validarStockEdicionQuimico()">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editarQuimicoLabel"><i class="bi bi-pencil-square"></i> Editar Producto Químico</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_quimico" id="edit_id_quimico">
          <div class="mb-3">
            <label class="form-label">Nombre del Producto Químico:</label>
            <input type="text" class="form-control" name="nombre" id="edit_nombre" required maxlength="100">
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Actual:</label>
                <input type="number" class="form-control" name="stock" id="edit_stock" min="0" max="10000" required>
                <small class="text-muted">Máximo: 10,000 unidades</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Mínimo:</label>
                <input type="number" class="form-control" name="stock_minimo" id="edit_stockmin" min="1" max="10000" required>
                <small class="text-muted">Máximo: 10,000 unidades</small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Actualizar Producto Químico
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Script para cargar datos en el modal de edición
document.addEventListener('DOMContentLoaded', function() {
    const editarModal = document.getElementById('editarQuimicoModal');
    
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const stock = button.getAttribute('data-stock');
        const stockmin = button.getAttribute('data-stockmin');
        
        document.getElementById('edit_id_quimico').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_stockmin').value = stockmin;
    });

    // Búsqueda en tiempo real
    const buscarInput = document.getElementById('buscarQuimico');
    buscarInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});

// Función para mostrar/ocultar campo de nombre personalizado
function actualizarCamposQuimico() {
    const select = document.getElementById('selectQuimico');
    const nombrePersonalizado = document.getElementById('nombrePersonalizado');
    
    if (select.value === 'otros') {
        nombrePersonalizado.classList.remove('d-none');
        nombrePersonalizado.required = true;
    } else {
        nombrePersonalizado.classList.add('d-none');
        nombrePersonalizado.required = false;
        nombrePersonalizado.value = '';
    }
}

// Validaciones de stock para productos químicos
function validarStockQuimico() {
    const stockInicial = parseInt(document.getElementById('stockInicial').value) || 0;
    const stockMinimo = parseInt(document.getElementById('stockMinimo').value) || 0;
    
    if (stockInicial > 10000) {
        alert('Error: El stock inicial no puede exceder 10,000 unidades');
        return false;
    }
    
    if (stockMinimo > 10000) {
        alert('Error: El stock mínimo no puede exceder 10,000 unidades');
        return false;
    }
    
    if (stockMinimo > stockInicial) {
        alert('Error: El stock mínimo no puede ser mayor que el stock inicial');
        return false;
    }
    
    return true;
}

function validarStockEdicionQuimico() {
    const stockActual = parseInt(document.getElementById('edit_stock').value) || 0;
    const stockMinimo = parseInt(document.getElementById('edit_stockmin').value) || 0;
    
    if (stockActual > 10000) {
        alert('Error: El stock actual no puede exceder 10,000 unidades');
        return false;
    }
    
    if (stockMinimo > 10000) {
        alert('Error: El stock mínimo no puede exceder 10,000 unidades');
        return false;
    }
    
    if (stockMinimo > stockActual) {
        alert('Error: El stock mínimo no puede ser mayor que el stock actual');
        return false;
    }
    
    return true;
}
</script>

<?php 
include '../footer.php';
$conn->close();
?>