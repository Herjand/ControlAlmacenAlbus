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
  $sql = "SELECT * FROM productos ORDER BY nombre ASC";
  $result = $conn->query($sql);
  ?>

  <div class="container-fluid">
      <h2><i class="bi bi-box"></i> Gestión de Productos Médicos</h2>
      <p class="text-muted">Administra el inventario de productos médicos.</p>

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
                  case '2': echo "Error: Campos obligatorios vacíos"; break;
                  case '3': echo "Error al eliminar el producto"; break;
                  case '4': echo "No se puede eliminar: Producto tiene movimientos registrados"; break;
                  case '5': echo "Error: Stock inicial excede el límite máximo (10,000 unidades)"; break;
                  case '6': echo "Error: Stock mínimo excede el límite máximo (1,000 unidades)"; break;
                  case '7': echo "Error: Stock mínimo no puede ser mayor que stock inicial"; break;
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
                                  <th>Producto</th>
                                  <th>Especificaciones</th>
                                  <th>Presentación</th>
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
                                          <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                          <?php if ($row['descripcion']): ?>
                                              <br><small class="text-muted"><?php echo htmlspecialchars($row['descripcion']); ?></small>
                                          <?php endif; ?>
                                      </td>
                                      <td>
                                          <?php 
                                          $especificaciones = [];
                                          if ($row['tamaño_peso']) {
                                              $especificaciones[] = '<strong>' . htmlspecialchars($row['tamaño_peso']) . '</strong>';
                                          }
                                          if ($row['cantidad_unidad']) {
                                              $especificaciones[] = htmlspecialchars($row['cantidad_unidad']);
                                          }
                                          if ($row['tipo_especifico']) {
                                              $especificaciones[] = htmlspecialchars($row['tipo_especifico']);
                                          }
                                          
                                          if (!empty($especificaciones)) {
                                              echo implode(' • ', $especificaciones);
                                          } else {
                                              echo '<small class="text-muted">- Sin especificaciones -</small>';
                                          }
                                          ?>
                                      </td>
                                      <td>
                                          <?php if ($row['presentacion']): ?>
                                              <span class="badge bg-info"><?php echo htmlspecialchars($row['presentacion']); ?></span>
                                          <?php else: ?>
                                              <small class="text-muted">-</small>
                                          <?php endif; ?>
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
                                                  data-stock="<?php echo $row['stock']; ?>"
                                                  data-stockmin="<?php echo $row['stock_minimo']; ?>"
                                                  data-presentacion="<?php echo htmlspecialchars($row['presentacion']); ?>"
                                                  data-tamaño="<?php echo htmlspecialchars($row['tamaño_peso']); ?>"
                                                  data-cantidad="<?php echo htmlspecialchars($row['cantidad_unidad']); ?>"
                                                  data-tipo="<?php echo htmlspecialchars($row['tipo_especifico']); ?>">
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
        <form action="funcionalidad_productos/registrar_producto.php" method="POST" onsubmit="return validarStock()">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="nuevoProductoLabel"><i class="bi bi-plus-circle"></i> Nuevo Producto Médico</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Nombre del Producto <span class="text-danger">*</span>:</label>
                  <select class="form-select" name="nombre" id="selectProducto" required onchange="actualizarCamposProducto()">
                    <option value="">Seleccionar tipo de producto...</option>
                    <option value="Compresas">Compresas</option>
                    <option value="Vendas">Vendas</option>
                    <option value="Gasa">Gasa</option>
                    <option value="Algodón">Algodón</option>
                    <option value="Apósitos">Apósitos</option>
                    <option value="Algodón laminado">Algodón laminado</option>
                    <option value="Torundas de gasa">Torundas de gasa</option>
                    <option value="Torundas de algodón">Torundas de algodón</option>
                    <option value="Barbijos quirurgicos">Barbijos quirurgicos</option>
                    <option value="Apósito ocular">Apósito ocular</option>
                    <option value="Tapa ojos">Tapa ojos</option>
                    <option value="Tapa oídos">Tapa oídos</option>
                    <option value="Algodón en disco">Algodón en disco</option>
                    <option value="Compresas costuradas">Compresas costuradas</option>
                    <option value="Gasa cortada">Gasa cortada</option>
                    <option value="otros">Otros (especificar)</option>
                  </select>
                  <input type="text" class="form-control mt-2 d-none" name="nombre_personalizado" id="nombrePersonalizado" 
                        placeholder="Especificar nombre del producto" maxlength="50">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Presentación <span class="text-danger">*</span>:</label>
                  <select class="form-select" name="presentacion" required>
                    <option value="">Seleccionar...</option>
                    <option value="Unidades">Unidades</option>
                    <option value="Cajas">Cajas</option>
                    <option value="Bolsas">Bolsas</option>
                    <option value="Paquetes">Paquetes</option>
                    <option value="Rollos">Rollos</option>
                    <option value="Pares">Pares</option>
                    <option value="Yardas">Yardas</option>
                    <option value="Metros">Metros</option>
                    <option value="Gramos">Gramos</option>
                    <option value="Kilogramos">Kilogramos</option>
                  </select>
                  <small class="text-muted">Unidad base para el control de inventario</small>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Descripción <span class="text-muted">(Opcional)</span>:</label>
              <textarea class="form-control" name="descripcion" rows="2" maxlength="100" 
                        placeholder="Características adicionales del producto"></textarea>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Tamaño/Peso <span class="text-muted">(Opcional)</span>:</label>
                  <input type="text" class="form-control" name="tamaño_peso" maxlength="50" 
                        placeholder="Ej: 5x5cm, 100gr, 100yds, 22.5cm x 100yds">
                  <small class="text-muted">Dimensiones o peso del producto</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Cantidad por Unidad <span class="text-muted">(Opcional)</span>:</label>
                  <input type="text" class="form-control" name="cantidad_unidad" maxlength="50" 
                        placeholder="Ej: 40 unidades, 50 unidades, 650 unidades">
                  <small class="text-muted">Contenido de cada unidad</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Tipo Específico <span class="text-muted">(Opcional)</span>:</label>
                  <input type="text" class="form-control" name="tipo_especifico" maxlength="50" 
                        placeholder="Ej: Venda, Gasa, Normal, Policotton, Blanca, 1 hrp, 2 hrp, Entero, Laminado">
                  <small class="text-muted">Variante o tipo específico del producto</small>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Stock Inicial <span class="text-danger">*</span>:</label>
                  <input type="number" class="form-control" name="stock" id="stockInicial" value="0" min="0" max="10000" required>
                  <small class="text-muted">Cantidad de unidades en inventario (Máximo: 10,000)</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Stock Mínimo <span class="text-danger">*</span>:</label>
                  <input type="number" class="form-control" name="stock_minimo" id="stockMinimo" value="10" min="1" max="1000" required>
                  <small class="text-muted">Alerta cuando el stock sea menor (Máximo: 1,000)</small>
                </div>
              </div>
            </div>

            <div class="alert alert-info">
              <small>
                <strong>📝 Ejemplos según tu inventario:</strong><br>
                • <strong>Compresas:</strong> Tamaño: 5x5cm | Cantidad: 40 unidades | Presentación: Caja | Tipo: Venda<br>
                • <strong>Vendas:</strong> Tamaño: 5cm | Presentación: Bolsa | Tipo: Normal<br>
                • <strong>Algodón:</strong> Tamaño: 100gr | Presentación: Paquete | Tipo: Entero<br>
                • <strong>Compresas:</strong> Tamaño: 10x10cm | Cantidad: 650 unidades | Presentación: Bolsa | Tipo: Blancas
              </small>
            </div>

            <div class="alert alert-warning">
              <small>
                <strong>ℹ️ Campos obligatorios:</strong> Nombre, Presentación, Stock Inicial, Stock Mínimo<br>
                <strong>📦 Campos opcionales:</strong> Descripción, Tamaño/Peso, Cantidad por Unidad, Tipo Específico
              </small>
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
        <form action="funcionalidad_productos/editar_producto.php" method="POST" onsubmit="return validarStockEdicion()">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="editarProductoLabel"><i class="bi bi-pencil-square"></i> Editar Producto Médico</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id_producto" id="edit_id_producto">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Nombre del Producto <span class="text-danger">*</span>:</label>
                  <input type="text" class="form-control" name="nombre" id="edit_nombre" required maxlength="50">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Presentación <span class="text-danger">*</span>:</label>
                  <select class="form-select" name="presentacion" id="edit_presentacion" required>
                    <option value="Unidades">Unidades</option>
                    <option value="Cajas">Cajas</option>
                    <option value="Bolsas">Bolsas</option>
                    <option value="Paquetes">Paquetes</option>
                    <option value="Rollos">Rollos</option>
                    <option value="Pares">Pares</option>
                    <option value="Yardas">Yardas</option>
                    <option value="Metros">Metros</option>
                    <option value="Gramos">Gramos</option>
                    <option value="Kilogramos">Kilogramos</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Descripción <span class="text-muted">(Opcional)</span>:</label>
              <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="2" maxlength="100"></textarea>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Tamaño/Peso <span class="text-muted">(Opcional)</span>:</label>
                  <input type="text" class="form-control" name="tamaño_peso" id="edit_tamaño" maxlength="50">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Cantidad por Unidad <span class="text-muted">(Opcional)</span>:</label>
                  <input type="text" class="form-control" name="cantidad_unidad" id="edit_cantidad" maxlength="50">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Tipo Específico <span class="text-muted">(Opcional)</span>:</label>
                  <input type="text" class="form-control" name="tipo_especifico" id="edit_tipo" maxlength="50">
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Stock Actual <span class="text-danger">*</span>:</label>
                  <input type="number" class="form-control" name="stock" id="edit_stock" min="0" max="10000" required>
                  <small class="text-muted">Máximo: 10,000 unidades</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Stock Mínimo <span class="text-danger">*</span>:</label>
                  <input type="number" class="form-control" name="stock_minimo" id="edit_stockmin" min="1" max="1000" required>
                  <small class="text-muted">Máximo: 1,000 unidades</small>
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

  <script>
  // Script para cargar datos en el modal de edición
  document.addEventListener('DOMContentLoaded', function() {
      const editarModal = document.getElementById('editarProductoModal');
      
      editarModal.addEventListener('show.bs.modal', function(event) {
          const button = event.relatedTarget;
          
          const id = button.getAttribute('data-id');
          const nombre = button.getAttribute('data-nombre');
          const descripcion = button.getAttribute('data-descripcion');
          const stock = button.getAttribute('data-stock');
          const stockmin = button.getAttribute('data-stockmin');
          const presentacion = button.getAttribute('data-presentacion') || '';
          const tamaño = button.getAttribute('data-tamaño') || '';
          const cantidad = button.getAttribute('data-cantidad') || '';
          const tipo = button.getAttribute('data-tipo') || '';
          
          document.getElementById('edit_id_producto').value = id;
          document.getElementById('edit_nombre').value = nombre;
          document.getElementById('edit_descripcion').value = descripcion;
          document.getElementById('edit_presentacion').value = presentacion;
          document.getElementById('edit_stock').value = stock;
          document.getElementById('edit_stockmin').value = stockmin;
          document.getElementById('edit_tamaño').value = tamaño;
          document.getElementById('edit_cantidad').value = cantidad;
          document.getElementById('edit_tipo').value = tipo;
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

  // Validaciones de stock
  function validarStock() {
      const stockInicial = parseInt(document.getElementById('stockInicial').value) || 0;
      const stockMinimo = parseInt(document.getElementById('stockMinimo').value) || 0;
      
      if (stockInicial > 10000) {
          alert('Error: El stock inicial no puede exceder 10,000 unidades');
          return false;
      }
      
      if (stockMinimo > 1000) {
          alert('Error: El stock mínimo no puede exceder 1,000 unidades');
          return false;
      }
      
      if (stockMinimo > stockInicial) {
          alert('Error: El stock mínimo no puede ser mayor que el stock inicial');
          return false;
      }
      
      return true;
  }

  function validarStockEdicion() {
      const stockActual = parseInt(document.getElementById('edit_stock').value) || 0;
      const stockMinimo = parseInt(document.getElementById('edit_stockmin').value) || 0;
      
      if (stockActual > 10000) {
          alert('Error: El stock actual no puede exceder 10,000 unidades');
          return false;
      }
      
      if (stockMinimo > 1000) {
          alert('Error: El stock mínimo no puede exceder 1,000 unidades');
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