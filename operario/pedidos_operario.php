<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_operario.php';

// Construir consulta con filtros
$whereConditions = [];
$params = [];
$types = '';

$sql = "SELECT p.*, c.empresa, c.contacto, c.telefono, c.email 
        FROM pedidos p 
        INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
        WHERE 1=1";

if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $sql .= " AND (c.empresa LIKE ? OR p.empresa_cliente LIKE ? OR p.persona_contacto LIKE ?)";
    $searchTerm = "%" . $_GET['buscar'] . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if (isset($_GET['estado']) && !empty($_GET['estado'])) {
    $sql .= " AND p.estado = ?";
    $params[] = $_GET['estado'];
    $types .= 's';
}

if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
    $sql .= " AND p.fecha_entrega >= ?";
    $params[] = $_GET['fecha_desde'];
    $types .= 's';
}

if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
    $sql .= " AND p.fecha_entrega <= ?";
    $params[] = $_GET['fecha_hasta'];
    $types .= 's';
}

$sql .= " ORDER BY p.fecha_entrega ASC, p.created_at DESC";

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">
    <h2><i class="fas fa-clipboard-list"></i> Ver Pedidos</h2>
    <p class="text-muted">Consulta y gestiona los pedidos del sistema.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por cliente..." 
                           value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="En Preparación" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'En Preparación') ? 'selected' : ''; ?>>En Preparación</option>
                        <option value="Completado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                        <option value="Cancelado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha_desde" class="form-control" 
                           value="<?php echo isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha_hasta" class="form-control" 
                           value="<?php echo isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : ''; ?>">
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="pedidos_operario.php" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

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
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5>EN PREPARACIÓN</h5>
                    <h3>
                        <?php 
                        $sql_preparacion = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'En Preparación'";
                        echo $conn->query($sql_preparacion)->fetch_assoc()['total'];
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
    </div>

    <!-- Tabla de Pedidos -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list-ul"></i> Lista de Pedidos
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Fecha Entrega</th>
                                <th>Nota Remisión</th>
                                <th>Lugar Entrega</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                // Determinar clase de estado
                                switch ($row['estado']) {
                                    case 'Pendiente':
                                        $badgeClass = 'bg-secondary';
                                        $estadoTexto = 'Pendiente';
                                        $accionBoton = 'Comenzar Preparación';
                                        $accionClase = 'btn-warning';
                                        $accionIcono = 'fas fa-play-circle';
                                        $accionURL = "funcionalidad_pedidos/preparar_pedido.php?id_pedido=" . $row['id_pedido'];
                                        $accionMensaje = "¿Estás seguro de que quieres comenzar a preparar este pedido?";
                                        break;
                                    case 'En Preparación':
                                        $badgeClass = 'bg-warning';
                                        $estadoTexto = 'En Preparación';
                                        $accionBoton = 'Completar Pedido';
                                        $accionClase = 'btn-success';
                                        $accionIcono = 'fas fa-check-circle';
                                        $accionURL = "funcionalidad_pedidos/completar_pedido.php?id_pedido=" . $row['id_pedido'];
                                        $accionMensaje = "¿Estás seguro de que has completado la preparación de este pedido?";
                                        break;
                                    case 'Completado':
                                        $badgeClass = 'bg-success';
                                        $estadoTexto = 'Completado';
                                        $accionBoton = 'Pedido Listo';
                                        $accionClase = 'btn-secondary';
                                        $accionIcono = 'fas fa-check-double';
                                        $accionURL = "#";
                                        $accionMensaje = "";
                                        break;
                                    case 'Cancelado':
                                        $badgeClass = 'bg-danger';
                                        $estadoTexto = 'Cancelado';
                                        $accionBoton = 'Cancelado';
                                        $accionClase = 'btn-danger';
                                        $accionIcono = 'fas fa-times-circle';
                                        $accionURL = "#";
                                        $accionMensaje = "";
                                        break;
                                    default:
                                        $badgeClass = 'bg-info';
                                        $estadoTexto = $row['estado'];
                                        $accionBoton = 'Gestionar';
                                        $accionClase = 'btn-info';
                                        $accionIcono = 'fas fa-cog';
                                        $accionURL = "#";
                                        $accionMensaje = "";
                                }
                                
                                // Verificar si está próximo a vencer
                                $fechaEntrega = new DateTime($row['fecha_entrega']);
                                $hoy = new DateTime();
                                $diferencia = $hoy->diff($fechaEntrega)->days;
                                
                                $alerta = '';
                                $alertaIcono = '';
                                if ($fechaEntrega < $hoy && $row['estado'] != 'Completado') {
                                    $alerta = 'table-danger';
                                    $alertaIcono = '<i class="fas fa-exclamation-triangle text-danger me-1" title="Pedido vencido"></i>';
                                } elseif ($diferencia <= 2 && $row['estado'] == 'Pendiente') {
                                    $alerta = 'table-warning';
                                    $alertaIcono = '<i class="fas fa-clock text-warning me-1" title="Pedido próximo a vencer"></i>';
                                }
                            ?>
                                <tr class="<?php echo $alerta; ?>">
                                    <td>
                                        <?php echo $alertaIcono; ?>
                                        <strong><?php echo htmlspecialchars($row['empresa_cliente']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['persona_contacto']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha_entrega'])); ?></td>
                                    <td>
                                        <?php if ($row['nota_remision']): ?>
                                            <span class="badge bg-dark"><?php echo htmlspecialchars($row['nota_remision']); ?></span>
                                        <?php else: ?>
                                            <small class="text-muted">- Sin nota -</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['lugar_entrega']): ?>
                                            <?php echo htmlspecialchars($row['lugar_entrega']); ?>
                                        <?php else: ?>
                                            <small class="text-muted">- Sin especificar -</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo $estadoTexto; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Botón Ver Detalles -->
                                            <button type="button" class="btn btn-outline-info" 
                                                    data-bs-toggle="modal" data-bs-target="#modalDetalles"
                                                    onclick="cargarDetallesPedido(<?php echo $row['id_pedido']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            
                                            <!-- Botón de Acción Principal -->
                                            <?php if ($row['estado'] == 'Pendiente' || $row['estado'] == 'En Preparación'): ?>
                                                <a href="<?php echo $accionURL; ?>" 
                                                   class="btn <?php echo $accionClase; ?>"
                                                   onclick="<?php echo $accionMensaje ? 'return confirm(\'' . $accionMensaje . '\')' : 'return false'; ?>">
                                                    <i class="<?php echo $accionIcono; ?>"></i> 
                                                    <?php echo $accionBoton; ?>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn <?php echo $accionClase; ?>" disabled>
                                                    <i class="<?php echo $accionIcono; ?>"></i> 
                                                    <?php echo $accionBoton; ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No se encontraron pedidos con los filtros aplicados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del pedido -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetallesLabel">
                    <i class="fas fa-clipboard-list"></i> Detalles del Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contenidoDetalles">
                <!-- Los detalles se cargarán aquí via AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles del pedido...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDetallesPedido(idPedido) {
    // Mostrar spinner de carga
    document.getElementById('contenidoDetalles').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del pedido...</p>
        </div>
    `;

    // Realizar petición AJAX para obtener los detalles
    fetch(`funcionalidad_pedidos/obtener_detalles_pedido.php?id_pedido=${idPedido}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('contenidoDetalles').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('contenidoDetalles').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del pedido.
                </div>
            `;
        });
}
</script>

<?php 
include '../footer.php';
$stmt->close();
$conn->close();
?>