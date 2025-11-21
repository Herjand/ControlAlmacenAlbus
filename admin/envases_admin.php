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

// Consultar envases
$sql = "SELECT * FROM envases ORDER BY nombre ASC";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h2><i class="bi bi-box-seam"></i> Gestión de Envases</h2>
    <p class="text-muted">Administra el inventario de envases y empaques.</p>

    <!-- Mostrar mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
            switch($success) {
                case '1': echo "Envase registrado correctamente"; break;
                case '2': echo "Envase actualizado correctamente"; break;
                case '3': echo "Envase eliminado correctamente"; break;
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
                case '1': echo "Error al registrar el envase"; break;
                case '2': echo "Error: Campos vacíos o inválidos"; break;
                case '3': echo "Error al eliminar el envase"; break;
                case '4': echo "No se puede eliminar: Envase tiene movimientos registrados"; break;
                case '5': echo "Error: Ya existe un envase con ese nombre"; break;
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
                    <h5>TOTAL ENVASES</h5>
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
                        $sql_optimo = "SELECT COUNT(*) as total FROM envases WHERE stock > stock_minimo";
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
                        $sql_bajo = "SELECT COUNT(*) as total FROM envases WHERE stock <= stock_minimo AND stock > 0";
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
                        $sql_sin_stock = "SELECT COUNT(*) as total FROM envases WHERE stock = 0";
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoEnvaseModal">
                        <i class="bi bi-plus-circle"></i> Nuevo Envase
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar envase..." id="buscarEnvase">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Envases -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul"></i> Inventario de Envases
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Envase</th>
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
                                                data-bs-target="#editarEnvaseModal"
                                                data-id="<?php echo $row['id_envase']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                data-stock="<?php echo $row['stock']; ?>"
                                                data-stockmin="<?php echo $row['stock_minimo']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="funcionalidad_envases/eliminar_envase.php?id=<?php echo $row['id_envase']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('⚠️ ¿ESTÁ SEGURO que desea eliminar este envase?\n\nSe eliminarán TODOS los movimientos relacionados.\n\nEsta acción NO se puede deshacer.')">
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
                    <i class="bi bi-info-circle"></i> No hay envases registrados en el sistema.
                    <br><small>Haz clic en "Nuevo Envase" para agregar el primero.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL NUEVO ENVASE -->
<div class="modal fade" id="nuevoEnvaseModal" tabindex="-1" aria-labelledby="nuevoEnvaseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_envases/registrar_envase.php" method="POST" onsubmit="return validarStockEnvase()">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="nuevoEnvaseLabel"><i class="bi bi-plus-circle"></i> Nuevo Envase</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre del Envase:</label>
            <select class="form-select" name="nombre" id="selectEnvase" required onchange="actualizarCamposEnvase()">
              <option value="">Seleccionar tipo de envase...</option>
              <option value="ETIQUETAS RECTANGULARES 1000 GRS">ETIQUETAS RECTANGULARES 1000 GRS</option>
              <option value="ETIQUETAS RECTANGULARES 800 GRS">ETIQUETAS RECTANGULARES 800 GRS</option>
              <option value="ETIQUETAS RECTANGULARES 500 GRS">ETIQUETAS RECTANGULARES 500 GRS</option>
              <option value="ETIQUETAS RECTANGULARES 400 GRS">ETIQUETAS RECTANGULARES 400 GRS</option>
              <option value="ETIQUETAS RECTANGULARES 200 GRS">ETIQUETAS RECTANGULARES 200 GRS</option>
              <option value="ETIQUETAS RECTANGULARES 100 GRS">ETIQUETAS RECTANGULARES 100 GRS</option>
              <option value="ETIQUETAS REDONDAS GRANDES">ETIQUETAS REDONDAS GRANDES</option>
              <option value="ETIQUETAS REDONDAS MEDIANAS">ETIQUETAS REDONDAS MEDIANAS</option>
              <option value="ETIQUETAS LAMINADO 10 CM">ETIQUETAS LAMINADO 10 CM</option>
              <option value="ETIQUETAS LAMINADO 15 CM">ETIQUETAS LAMINADO 15 CM</option>
              <option value="ETIQUETAS LAMINADO 20 CM">ETIQUETAS LAMINADO 20 CM</option>
              <option value="BOLSAS DISCO 250 GRS">BOLSAS DISCO 250 GRS</option>
              <option value="BOLSAS DISCO 100 GRS">BOLSAS DISCO 100 GRS</option>
              <option value="BOLSAS DISCO 50 GRS">BOLSAS DISCO 50 GRS</option>
              <option value="BOLSAS 50 GRS">BOLSAS 50 GRS</option>
              <option value="BOLSA 10 GRS">BOLSA 10 GRS</option>
              <option value="BOLSAS DE ZIGZAG">BOLSAS DE ZIGZAG</option>
              <option value="BOLSAS DE BOLITAS">BOLSAS DE BOLITAS</option>
              <option value="BOLSAS DE COMPRESAS 5 X 5">BOLSAS DE COMPRESAS 5 X 5</option>
              <option value="CAJAS DE COMPRESAS 5 X 5">CAJAS DE COMPRESAS 5 X 5</option>
              <option value="BOLSAS DE COMPRESAS 7,5 X 7,5">BOLSAS DE COMPRESAS 7,5 X 7,5</option>
              <option value="CAJAS DE COMPRESA 7,5 X 7,5">CAJAS DE COMPRESA 7,5 X 7,5</option>
              <option value="BOLSAS DE COMPRESA 10 X 10">BOLSAS DE COMPRESA 10 X 10</option>
              <option value="CAJAS DE COMPRESA 10 X 10">CAJAS DE COMPRESA 10 X 10</option>
              <option value="CAJAS DE BARBIJO">CAJAS DE BARBIJO</option>
              <option value="CAJAS DE ESPONJAS DE 2 X 2">CAJAS DE ESPONJAS DE 2 X 2</option>
              <option value="CAJAS DE ESPONJAS DE 4 X 4">CAJAS DE ESPONJAS DE 4 X 4</option>
              <option value="VENDAS DE 5 CM">VENDAS DE 5 CM</option>
              <option value="VENDAS DE 7,5 CM">VENDAS DE 7,5 CM</option>
              <option value="VENDAS DE 10 CM">VENDAS DE 10 CM</option>
              <option value="VENDAS DE 12,5 CM">VENDAS DE 12,5 CM</option>
              <option value="VENDAS DE 15 CM">VENDAS DE 15 CM</option>
              <option value="VENDAS DE 20 CM">VENDAS DE 20 CM</option>
              <option value="ETIQUETAS 150 YDS">ETIQUETAS 150 YDS</option>
              <option value="ETIQUETAS DE 2 YDS">ETIQUETAS DE 2 YDS</option>
              <option value="ETIQUETAS 4,50 MTS">ETIQUETAS 4,50 MTS</option>
              <option value="BOBINA DE 65 CM">BOBINA DE 65 CM</option>
              <option value="BOBINA DE 60 CM">BOBINA DE 60 CM</option>
              <option value="otros">Otros (especificar)</option>
            </select>
            <input type="text" class="form-control mt-2 d-none" name="nombre_personalizado" id="nombrePersonalizado" 
                   placeholder="Especificar nombre del envase" maxlength="100">
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
            <i class="bi bi-check-circle"></i> Guardar Envase
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR ENVASE -->
<div class="modal fade" id="editarEnvaseModal" tabindex="-1" aria-labelledby="editarEnvaseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_envases/editar_envase.php" method="POST" onsubmit="return validarStockEdicionEnvase()">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editarEnvaseLabel"><i class="bi bi-pencil-square"></i> Editar Envase</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_envase" id="edit_id_envase">
          <div class="mb-3">
            <label class="form-label">Nombre del Envase:</label>
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
            <i class="bi bi-check-circle"></i> Actualizar Envase
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
    const editarModal = document.getElementById('editarEnvaseModal');
    
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const stock = button.getAttribute('data-stock');
        const stockmin = button.getAttribute('data-stockmin');
        
        document.getElementById('edit_id_envase').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_stockmin').value = stockmin;
    });

    // Búsqueda en tiempo real
    const buscarInput = document.getElementById('buscarEnvase');
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
function actualizarCamposEnvase() {
    const select = document.getElementById('selectEnvase');
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

// Validaciones de stock para envases
function validarStockEnvase() {
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

function validarStockEdicionEnvase() {
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