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

// Consultar pedidos pendientes
$sql_pedidos = "SELECT p.*, c.empresa, c.contacto 
                FROM pedidos p 
                JOIN clientes c ON p.id_cliente = c.id_cliente 
                WHERE p.estado = 'Pendiente' 
                ORDER BY p.fecha_entrega ASC";
$pedidos_result = $conn->query($sql_pedidos);

// Consultar pedidos en preparación
$sql_preparacion = "SELECT p.*, c.empresa, c.contacto 
                    FROM pedidos p 
                    JOIN clientes c ON p.id_cliente = c.id_cliente 
                    WHERE p.estado = 'En Preparación' 
                    ORDER BY p.fecha_entrega ASC";
$preparacion_result = $conn->query($sql_preparacion);
?>

<div class="container-fluid">
    <h2><i class="bi bi-clipboard-check"></i> Preparar Pedidos</h2>
    <p class="text-muted">Gestiona los pedidos pendientes y en preparación.</p>

    <!-- Mensajes -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
            switch($success) {
                case '1': echo "Pedido marcado como 'En Preparación' correctamente"; break;
                case '2': echo "Pedido completado correctamente"; break;
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
                case '1': echo "Error al actualizar el estado del pedido"; break;
                case '2': echo "Error: Stock insuficiente para completar el pedido"; break;
                case '3': echo "Error: Pedido no encontrado"; break;
                default: echo "Error en la operación";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Pedidos Pendientes -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-clock"></i> Pedidos Pendientes
                </div>
                <div class="card-body">
                    <?php if ($pedidos_result && $pedidos_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Cliente</th>
                                        <th>Fecha Entrega</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pedido = $pedidos_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $pedido['id_pedido']; ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong><?php echo htmlspecialchars($pedido['empresa']); ?></strong><br>
                                                    <span class="text-muted"><?php echo htmlspecialchars($pedido['contacto']); ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Botón Ver Detalles -->
                                                    <button type="button" class="btn btn-outline-info" 
                                                            data-bs-toggle="modal" data-bs-target="#modalDetalles"
                                                            onclick="cargarDetallesPedido(<?php echo $pedido['id_pedido']; ?>)">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </button>
                                                    <!-- Botón Preparar -->
                                                    <a href="funcionalidad_pedidos/preparar_pedido.php?id_pedido=<?php echo $pedido['id_pedido']; ?>" 
                                                       class="btn btn-outline-warning"
                                                       onclick="return confirm('¿Estás seguro de que quieres comenzar a preparar este pedido?')">
                                                        <i class="bi bi-play-circle"></i> Preparar
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle display-4"></i>
                            <p class="mt-2">No hay pedidos pendientes.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pedidos en Preparación -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-gear"></i> Pedidos en Preparación
                </div>
                <div class="card-body">
                    <?php if ($preparacion_result && $preparacion_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Cliente</th>
                                        <th>Fecha Entrega</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pedido = $preparacion_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $pedido['id_pedido']; ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong><?php echo htmlspecialchars($pedido['empresa']); ?></strong><br>
                                                    <span class="text-muted"><?php echo htmlspecialchars($pedido['contacto']); ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Botón Ver Detalles -->
                                                    <button type="button" class="btn btn-outline-info" 
                                                            data-bs-toggle="modal" data-bs-target="#modalDetalles"
                                                            onclick="cargarDetallesPedido(<?php echo $pedido['id_pedido']; ?>)">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </button>
                                                    <!-- Botón Completar -->
                                                    <a href="funcionalidad_pedidos/completar_pedido.php?id_pedido=<?php echo $pedido['id_pedido']; ?>" 
                                                       class="btn btn-outline-success"
                                                       onclick="return confirm('¿Estás seguro de que has completado la preparación de este pedido?')">
                                                        <i class="bi bi-check-circle"></i> Completar
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">No hay pedidos en preparación.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del pedido -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetallesLabel">
                    <i class="bi bi-clipboard-data"></i> Detalles del Pedido
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
                    <i class="bi bi-exclamation-triangle"></i> Error al cargar los detalles del pedido.
                </div>
            `;
        });
}
</script>

<?php 
include '../footer.php';
$conn->close();
?>