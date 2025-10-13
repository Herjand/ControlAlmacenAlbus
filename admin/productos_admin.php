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

// Consultar productos
$sql = "SELECT * FROM productos ORDER BY categoria, nombre ASC";
$result = $conn->query($sql);

// Obtener categorías únicas para filtros
$sql_categorias = "SELECT DISTINCT categoria FROM productos ORDER BY categoria";
$categorias = $conn->query($sql_categorias);
?>

<div class="container-fluid">
    <h2><i class="bi bi-box"></i> Gestión de Productos Médicos</h2>
    <p class="text-muted">Administra el inventario de productos médicos con sus dimensiones y especificaciones.</p>

    <!-- Mostrar mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
            switch($success) {
                case '1': echo "Producto registrado correctamente"; break;
                case '2': echo "Producto actualizado correctamente"; break;
                case '3': echo "Producto eliminado correctamente"; break;
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
                case '1': echo "Error al registrar el producto"; break;
                case '2': echo "Error: Campos vacíos"; break;
                case '3': echo "Error al eliminar el producto"; break;
                case '4': echo "No se puede eliminar: Producto tiene movimientos registrados"; break;
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
                    <h5>TOTAL PRODUCTOS</h5>
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
                        $sql_optimo = "SELECT COUNT(*) as total FROM productos WHERE stock > stock_minimo";
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
                        $sql_bajo = "SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo AND stock > 0";
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
                        $sql_sin_stock = "SELECT COUNT(*) as total FROM productos WHERE stock = 0";
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoProductoModal">
                        <i class="bi bi-plus-circle"></i> Nuevo Producto Médico
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar producto médico..." id="buscarProducto">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <i class="bi bi-list-ul"></i> Inventario de Productos Médicos
    </div>
    <div class="card-body">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Producto y Especificaciones</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Mínimo</th>
                            <th>Estado</th>
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
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                        <?php 
                                        // Mostrar dimensiones, cantidad y especificaciones
                                        $especificaciones = [];
                                        
                                        if ($row['ancho'] > 0 && $row['largo'] > 0) {
                                            $especificaciones[] = $row['ancho'] . ' cm x ' . $row['largo'] . ' cm';
                                        } elseif ($row['ancho'] > 0) {
                                            $especificaciones[] = $row['ancho'] . ' cm';
                                        }
                                        
                                        if ($row['cantidad_unidad'] > 0) {
                                            $especificaciones[] = $row['cantidad_unidad'];
                                        }
                                        
                                        if ($row['tipo_especifico']) {
                                            $especificaciones[] = $row['tipo_especifico'];
                                        }
                                        
                                        if (!empty($especificaciones)) {
                                            echo '<br><small class="text-primary"><strong>' . implode(' • ', $especificaciones) . '</strong></small>';
                                        }
                                        ?>
                                        
                                        <?php if ($row['descripcion']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($row['descripcion']); ?></small>
                                        <?php endif; ?>
                                        
                                        <?php if ($row['presentacion']): ?>
                                            <br><small class="text-info">📦 <?php echo htmlspecialchars($row['presentacion']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['categoria']); ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $clase_stock; ?> fs-6">
                                        <?php echo $row['stock']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['stock_minimo']; ?></td>
                                <td>
                                    <span class="badge <?php echo $clase_stock; ?>">
                                        <?php echo $texto_estado; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editarProductoModal"
                                            data-id="<?php echo $row['id_producto']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                            data-descripcion="<?php echo htmlspecialchars($row['descripcion']); ?>"
                                            data-categoria="<?php echo htmlspecialchars($row['categoria']); ?>"
                                            data-stock="<?php echo $row['stock']; ?>"
                                            data-stockmin="<?php echo $row['stock_minimo']; ?>"
                                            data-ancho="<?php echo $row['ancho']; ?>"
                                            data-largo="<?php echo $row['largo']; ?>"
                                            data-tipo="<?php echo htmlspecialchars($row['tipo_especifico']); ?>"
                                            data-presentacion="<?php echo htmlspecialchars($row['presentacion']); ?>"
                                            data-cantidad="<?php echo $row['cantidad_unidad']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="funcionalidad_productos/eliminar_producto.php?id=<?php echo $row['id_producto']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('⚠️ ¿ESTÁ SEGURO que desea eliminar este producto?\n\nSe eliminarán TODOS los movimientos relacionados (entradas, salidas, pedidos).\n\nEsta acción NO se puede deshacer.')">
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
                <i class="bi bi-info-circle"></i> No hay productos médicos registrados en el sistema.
                <br><small>Haz clic en "Nuevo Producto Médico" para agregar el primero.</small>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>


<!-- MODAL NUEVO PRODUCTO MÉDICO -->
<div class="modal fade" id="nuevoProductoModal" tabindex="-1" aria-labelledby="nuevoProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_productos/registrar_producto.php" method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="nuevoProductoLabel"><i class="bi bi-plus-circle"></i> Nuevo Producto Médico</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nombre del Producto:</label>
                <select class="form-select" name="nombre" id="selectProducto" required onchange="actualizarCamposProducto()">
                  <option value="">Seleccionar tipo de producto...</option>
                  <option value="Compresas">Compresas</option>
                  <option value="Vendas">Vendas</option>
                  <option value="Gasa">Gasa</option>
                  <option value="Algodón">Algodón</option>
                  <option value="Apósitos">Apósitos</option>
                  <option value="Algodón laminado">Algodón laminado</option>
                  <option value="Torundas">Torundas</option>
                  <option value="Barbijos">Barbijos</option>
                  <option value="Apósito ocular">Apósito ocular</option>
                  <option value="Tapa ojos">Tapa ojos</option>
                  <option value="otros">Otros (especificar)</option>
                </select>
                <input type="text" class="form-control mt-2 d-none" name="nombre_personalizado" id="nombrePersonalizado" 
                       placeholder="Especificar nombre del producto" maxlength="50">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Categoría:</label>
                <select class="form-select" name="categoria" required>
                  <option value="Material de Curación">Material de Curación</option>
                  <option value="Vendajes">Vendajes</option>
                  <option value="Gasas y Apósitos">Gasas y Apósitos</option>
                  <option value="Algodón y Torundas">Algodón y Torundas</option>
                  <option value="Protección Ocular">Protección Ocular</option>
                  <option value="Protección Personal">Protección Personal</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Descripción:</label>
            <textarea class="form-control" name="descripcion" rows="2" maxlength="100" 
                      placeholder="Características adicionales del producto (ej: con hilo radiopaco, sin hilos, blanca, etc.)"></textarea>
          </div>

          <!-- SECCIÓN DE DIMENSIONES Y ESPECIFICACIONES -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-rulers"></i> Especificaciones del Producto</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Ancho:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" name="ancho" step="0.1" min="0" placeholder="0" value="0">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Largo:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" name="largo" step="0.1" min="0" placeholder="0" value="0">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Cantidad/Peso:</label>
                    <input type="number" class="form-control" name="cantidad_unidad" step="1" min="0" value="0" 
                           placeholder="Ej: 100, 200, 500">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Unidad de Medida:</label>
                    <select class="form-select" name="unidad_medida" required>
                      <option value="">Seleccionar...</option>
                      <option value="unidad">Unidades</option>
                      <option value="caja">Cajas</option>
                      <option value="pack">Packs</option>
                      <option value="rollo">Rollos</option>
                      <option value="par">Pares</option>
                      <option value="gramo">Gramos (g)</option>
                      <option value="kilogramo">Kilogramos (kg)</option>
                      <option value="metro">Metros (m)</option>
                      <option value="centimetro">Centímetros (cm)</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Tipo Específico:</label>
                    <input type="text" class="form-control" name="tipo_especifico" maxlength="50" 
                           placeholder="Ej: Blanca, Hilo radiopaco, Con hilo, Sin hilo">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Presentación:</label>
                    <input type="text" class="form-control" name="presentacion" maxlength="50" 
                           placeholder="Ej: En zigzag, En bolitas, Individual">
                  </div>
                </div>
              </div>
              
              <small class="text-muted">
                <strong>Ejemplos de uso:</strong><br>
                • <strong>Algodón:</strong> Cantidad/Peso: 100 + Unidad: Gramos → Resultado: 100 g<br>
                • <strong>Vendas:</strong> Ancho: 10 + Unidad: Rollos → Resultado: 10 cm (rollos)<br>
                • <strong>Compresas:</strong> Ancho: 10 + Largo: 10 + Unidad: Unidades → Resultado: 10x10 cm
              </small>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Inicial:</label>
                <input type="number" class="form-control" name="stock" value="0" min="0" required>
                <small class="text-muted">Cantidad de unidades en inventario</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Mínimo:</label>
                <input type="number" class="form-control" name="stock_minimo" value="10" min="1" required>
                <small class="text-muted">Alerta cuando el stock sea menor</small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Guardar Producto
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR PRODUCTO MÉDICO -->
<div class="modal fade" id="editarProductoModal" tabindex="-1" aria-labelledby="editarProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_productos/editar_producto.php" method="POST">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editarProductoLabel"><i class="bi bi-pencil-square"></i> Editar Producto Médico</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_producto" id="edit_id_producto">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nombre del Producto:</label>
                <input type="text" class="form-control" name="nombre" id="edit_nombre" required maxlength="50">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Categoría:</label>
                <select class="form-select" name="categoria" id="edit_categoria" required>
                  <option value="Material de Curación">Material de Curación</option>
                  <option value="Vendajes">Vendajes</option>
                  <option value="Gasas y Apósitos">Gasas y Apósitos</option>
                  <option value="Algodón y Torundas">Algodón y Torundas</option>
                  <option value="Protección Ocular">Protección Ocular</option>
                  <option value="Protección Personal">Protección Personal</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Descripción:</label>
            <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="2" maxlength="100"></textarea>
          </div>

          <!-- SECCIÓN DE DIMENSIONES Y ESPECIFICACIONES -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-rulers"></i> Especificaciones del Producto</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Ancho:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" name="ancho" id="edit_ancho" step="0.1" min="0" value="0">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Largo:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" name="largo" id="edit_largo" step="0.1" min="0" value="0">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Cantidad/Peso:</label>
                    <input type="number" class="form-control" name="cantidad_unidad" id="edit_cantidad" step="1" min="0" value="0">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">Unidad de Medida:</label>
                    <select class="form-select" name="unidad_medida" id="edit_unidad" required>
                      <option value="unidad">Unidades</option>
                      <option value="caja">Cajas</option>
                      <option value="pack">Packs</option>
                      <option value="rollo">Rollos</option>
                      <option value="par">Pares</option>
                      <option value="gramo">Gramos (g)</option>
                      <option value="kilogramo">Kilogramos (kg)</option>
                      <option value="metro">Metros (m)</option>
                      <option value="centimetro">Centímetros (cm)</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Tipo Específico:</label>
                    <input type="text" class="form-control" name="tipo_especifico" id="edit_tipo" maxlength="50">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Presentación:</label>
                    <input type="text" class="form-control" name="presentacion" id="edit_presentacion" maxlength="50">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Actual:</label>
                <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Stock Mínimo:</label>
                <input type="number" class="form-control" name="stock_minimo" id="edit_stockmin" min="1" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Actualizar Producto
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Script para cargar datos en el modal de edición
document.addEventListener('DOMContentLoaded', function() {
    const editarModal = document.getElementById('editarProductoModal');
    
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const descripcion = button.getAttribute('data-descripcion');
        const categoria = button.getAttribute('data-categoria');
        const unidad = button.getAttribute('data-unidad');
        const stock = button.getAttribute('data-stock');
        const stockmin = button.getAttribute('data-stockmin');
        const ancho = button.getAttribute('data-ancho') || '0';
        const largo = button.getAttribute('data-largo') || '0';
        const tipo = button.getAttribute('data-tipo') || '';
        const presentacion = button.getAttribute('data-presentacion') || '';
        const cantidad = button.getAttribute('data-cantidad') || '0';
        
        document.getElementById('edit_id_producto').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('edit_categoria').value = categoria;
        document.getElementById('edit_unidad').value = unidad;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_stockmin').value = stockmin;
        document.getElementById('edit_ancho').value = ancho;
        document.getElementById('edit_largo').value = largo;
        document.getElementById('edit_tipo').value = tipo;
        document.getElementById('edit_presentacion').value = presentacion;
        document.getElementById('edit_cantidad').value = cantidad;
    });

    // Búsqueda en tiempo real
    const buscarInput = document.getElementById('buscarProducto');
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
function actualizarCamposProducto() {
    const select = document.getElementById('selectProducto');
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
</script>

<?php 
include '../footer.php';
$conn->close();
?>