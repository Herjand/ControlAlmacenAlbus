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

// Consultar clientes
$sql = "SELECT * FROM clientes ORDER BY empresa ASC";
$result = $conn->query($sql);

// Departamentos de Bolivia
$departamentos = [
    'La Paz',
    'Cochabamba', 
    'Santa Cruz',
    'Oruro',
    'Potosi',
    'Tarija',
    'Chuquisaca',
    'Beni',
    'Pando'
];
?>
<div class="container-fluid">
    <h2><i class="bi bi-people"></i> Gestión de Clientes</h2>
    <p class="text-muted">Administra la información de empresas clientes.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
            switch($success) {
                case '1': echo "Cliente registrado correctamente"; break;
                case '2': echo "Cliente actualizado correctamente"; break;
                case '3': echo "Cliente eliminado correctamente"; break;
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
                case '1': echo "Error al registrar el cliente"; break;
                case '2': echo "Error: Campos vacíos"; break;
                case '3': echo "Error al eliminar el cliente"; break;
                default: echo "Error en la operación";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjeta de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5>TOTAL CLIENTES</h5>
                    <h3><?php echo $result->num_rows; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Herramientas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
                        <i class="bi bi-person-plus"></i> Nuevo Cliente
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar cliente..." id="buscarCliente">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul"></i> Lista de Clientes
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Empresa</th>
                                <th>Contacto</th>
                                <th>NIT</th>
                                <th>Ubicación</th>
                                <th>Contacto</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['empresa']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['contacto']); ?></td>
                                    <td>
                                        <?php if ($row['nit']): ?>
                                            <span class="badge bg-dark"><?php echo htmlspecialchars($row['nit']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['ciudad']): ?>
                                            <small>
                                                <?php echo htmlspecialchars($row['ciudad']); ?>
                                                <?php if ($row['direccion']): ?>
                                                    <br><span class="text-muted"><?php echo htmlspecialchars($row['direccion']); ?></span>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?php if ($row['telefono']): ?>
                                                <div>📞 <?php echo htmlspecialchars($row['telefono']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($row['email']): ?>
                                                <div>
                                                    <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                        ✉️ <?php echo htmlspecialchars($row['email']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editarClienteModal"
                                                data-id="<?php echo $row['id_cliente']; ?>"
                                                data-empresa="<?php echo htmlspecialchars($row['empresa']); ?>"
                                                data-contacto="<?php echo htmlspecialchars($row['contacto']); ?>"
                                                data-telefono="<?php echo htmlspecialchars($row['telefono']); ?>"
                                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                data-nit="<?php echo htmlspecialchars($row['nit']); ?>"
                                                data-direccion="<?php echo htmlspecialchars($row['direccion']); ?>"
                                                data-ciudad="<?php echo htmlspecialchars($row['ciudad']); ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="funcionalidad_clientes/eliminar_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Está seguro que desea eliminar este cliente?\n\nEsta acción no se puede deshacer.')">
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
                    <i class="bi bi-info-circle"></i> No hay clientes registrados en el sistema.
                    <br><small>Haz clic en "Nuevo Cliente" para agregar el primero.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL NUEVO CLIENTE -->
<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_clientes/registrar_cliente.php" method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="nuevoClienteLabel"><i class="bi bi-person-plus"></i> Nuevo Cliente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Empresa:</label>
                <input type="text" class="form-control" name="empresa" required maxlength="50" placeholder="Nombre de la empresa">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">NIT:</label>
                <input type="text" class="form-control" name="nit" maxlength="20" placeholder="Número de identificación tributaria">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Persona de Contacto:</label>
                <input type="text" class="form-control" name="contacto" required maxlength="50" placeholder="Nombre del contacto">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Teléfono:</label>
                <input type="text" class="form-control" name="telefono" maxlength="15" placeholder="Opcional">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" class="form-control" name="email" maxlength="50" placeholder="Opcional">
          </div>

          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <label class="form-label">Dirección:</label>
                <input type="text" class="form-control" name="direccion" maxlength="100" placeholder="Dirección completa">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Departamento:</label>
                <select class="form-select" name="ciudad">
                  <option value="">Seleccionar departamento...</option>
                  <?php foreach ($departamentos as $depto): ?>
                    <option value="<?php echo $depto; ?>"><?php echo $depto; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Guardar Cliente
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR CLIENTE -->
<div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="funcionalidad_clientes/editar_cliente.php" method="POST">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editarClienteLabel"><i class="bi bi-pencil-square"></i> Editar Cliente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_cliente" id="edit_id_cliente">
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Empresa:</label>
                <input type="text" class="form-control" name="empresa" id="edit_empresa" required maxlength="50">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">NIT:</label>
                <input type="text" class="form-control" name="nit" id="edit_nit" maxlength="20">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Persona de Contacto:</label>
                <input type="text" class="form-control" name="contacto" id="edit_contacto" required maxlength="50">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Teléfono:</label>
                <input type="text" class="form-control" name="telefono" id="edit_telefono" maxlength="15">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" class="form-control" name="email" id="edit_email" maxlength="50">
          </div>

          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <label class="form-label">Dirección:</label>
                <input type="text" class="form-control" name="direccion" id="edit_direccion" maxlength="100">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Departamento:</label>
                <select class="form-select" name="ciudad" id="edit_ciudad">
                  <option value="">Seleccionar departamento...</option>
                  <?php foreach ($departamentos as $depto): ?>
                    <option value="<?php echo $depto; ?>"><?php echo $depto; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Actualizar Cliente
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
    const editarModal = document.getElementById('editarClienteModal');
    
    editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        document.getElementById('edit_id_cliente').value = button.getAttribute('data-id');
        document.getElementById('edit_empresa').value = button.getAttribute('data-empresa');
        document.getElementById('edit_contacto').value = button.getAttribute('data-contacto');
        document.getElementById('edit_telefono').value = button.getAttribute('data-telefono') || '';
        document.getElementById('edit_email').value = button.getAttribute('data-email') || '';
        document.getElementById('edit_nit').value = button.getAttribute('data-nit') || '';
        document.getElementById('edit_direccion').value = button.getAttribute('data-direccion') || '';
        document.getElementById('edit_ciudad').value = button.getAttribute('data-ciudad') || '';
    });

    // Búsqueda en tiempo real
    const buscarInput = document.getElementById('buscarCliente');
    buscarInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
</script>

<?php 
include '../footer.php';
$conn->close();
?>