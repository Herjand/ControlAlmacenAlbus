<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_operario.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// Consultar movimientos del operario
// Entradas realizadas por el operario
$sql_entradas = "SELECT e.*, p.nombre as producto_nombre 
                 FROM entradas e 
                 JOIN productos p ON e.id_producto = p.id_producto 
                 WHERE e.usuario_responsable = ? 
                 ORDER BY e.fecha DESC 
                 LIMIT 10";
$stmt_entradas = $conn->prepare($sql_entradas);
$stmt_entradas->bind_param("i", $usuario_id);
$stmt_entradas->execute();
$entradas_result = $stmt_entradas->get_result();

// Salidas realizadas por el operario
$sql_salidas = "SELECT s.*, p.nombre as producto_nombre 
                FROM salidas s 
                JOIN productos p ON s.id_producto = p.id_producto 
                WHERE s.usuario_responsable = ? 
                ORDER BY s.fecha DESC 
                LIMIT 10";
$stmt_salidas = $conn->prepare($sql_salidas);
$stmt_salidas->bind_param("i", $usuario_id);
$stmt_salidas->execute();
$salidas_result = $stmt_salidas->get_result();

// Pedidos completados recientemente
$sql_pedidos = "SELECT p.*, c.empresa as empresa_cliente, c.contacto as persona_contacto 
                FROM pedidos p 
                JOIN clientes c ON p.id_cliente = c.id_cliente 
                WHERE p.estado = 'Completado' 
                ORDER BY p.updated_at DESC 
                LIMIT 10";
$pedidos_result = $conn->query($sql_pedidos);

// Estadísticas resumen
$sql_resumen = "SELECT 
                (SELECT COUNT(*) FROM entradas WHERE usuario_responsable = ?) as total_entradas,
                (SELECT COUNT(*) FROM salidas WHERE usuario_responsable = ?) as total_salidas,
                (SELECT COUNT(*) FROM pedidos WHERE estado = 'Completado') as total_pedidos_completados";
$stmt_resumen = $conn->prepare($sql_resumen);
$stmt_resumen->bind_param("ii", $usuario_id, $usuario_id);
$stmt_resumen->execute();
$resumen = $stmt_resumen->get_result()->fetch_assoc();
?>

<div class="container-fluid">
    <h2><i class="fas fa-chart-line"></i> Mis Movimientos</h2>
    <p class="text-muted">Resumen de mis actividades en el sistema.</p>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5><i class="fas fa-sign-in-alt"></i></h5>
                    <h5>ENTRADAS REALIZADAS</h5>
                    <h3><?php echo $resumen['total_entradas']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h5><i class="fas fa-sign-out-alt"></i></h5>
                    <h5>SALIDAS REALIZADAS</h5>
                    <h3><?php echo $resumen['total_salidas']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5><i class="fas fa-check-circle"></i></h5>
                    <h5>PEDIDOS COMPLETADOS</h5>
                    <h3><?php echo $resumen['total_pedidos_completados']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5><i class="fas fa-user"></i></h5>
                    <h5>OPERARIO</h5>
                    <h5><?php echo htmlspecialchars($usuario_nombre); ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Últimas Entradas -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-sign-in-alt"></i> Mis Últimas Entradas
                </div>
                <div class="card-body">
                    <?php if ($entradas_result && $entradas_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($entrada = $entradas_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small><strong><?php echo htmlspecialchars($entrada['producto_nombre']); ?></strong></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">+<?php echo $entrada['cantidad']; ?></span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($entrada['motivo']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox display-4"></i>
                            <p class="mt-2">No has realizado entradas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Últimas Salidas -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-sign-out-alt"></i> Mis Últimas Salidas
                </div>
                <div class="card-body">
                    <?php if ($salidas_result && $salidas_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($salida = $salidas_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small><strong><?php echo htmlspecialchars($salida['producto_nombre']); ?></strong></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">-<?php echo $salida['cantidad']; ?></span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($salida['motivo']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-outbox display-4"></i>
                            <p class="mt-2">No has realizado salidas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pedidos Completados Recientes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-check-circle"></i> Pedidos Completados Recientemente
                </div>
                <div class="card-body">
                    <?php if ($pedidos_result && $pedidos_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Contacto</th>
                                        <th>Fecha Entrega</th>
                                        <th>Nota Remisión</th>
                                        <th>Completado el</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pedido = $pedidos_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small><strong><?php echo htmlspecialchars($pedido['empresa_cliente']); ?></strong></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($pedido['persona_contacto']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($pedido['nota_remision']): ?>
                                                    <span class="badge bg-dark"><?php echo htmlspecialchars($pedido['nota_remision']); ?></span>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y H:i', strtotime($pedido['updated_at'])); ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-clipboard-check display-4"></i>
                            <p class="mt-2">No hay pedidos completados recientemente.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Día -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-calendar-day"></i> Resumen de Hoy
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <?php
                            $hoy = date('Y-m-d');
                            $sql_hoy_entradas = "SELECT COUNT(*) as total FROM entradas WHERE usuario_responsable = ? AND DATE(fecha) = ?";
                            $stmt_hoy_entradas = $conn->prepare($sql_hoy_entradas);
                            $stmt_hoy_entradas->bind_param("is", $usuario_id, $hoy);
                            $stmt_hoy_entradas->execute();
                            $hoy_entradas = $stmt_hoy_entradas->get_result()->fetch_assoc();
                            ?>
                            <h4 class="text-success"><?php echo $hoy_entradas['total']; ?></h4>
                            <p class="text-muted">Entradas Hoy</p>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $sql_hoy_salidas = "SELECT COUNT(*) as total FROM salidas WHERE usuario_responsable = ? AND DATE(fecha) = ?";
                            $stmt_hoy_salidas = $conn->prepare($sql_hoy_salidas);
                            $stmt_hoy_salidas->bind_param("is", $usuario_id, $hoy);
                            $stmt_hoy_salidas->execute();
                            $hoy_salidas = $stmt_hoy_salidas->get_result()->fetch_assoc();
                            ?>
                            <h4 class="text-warning"><?php echo $hoy_salidas['total']; ?></h4>
                            <p class="text-muted">Salidas Hoy</p>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $sql_hoy_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Completado' AND DATE(updated_at) = ?";
                            $stmt_hoy_pedidos = $conn->prepare($sql_hoy_pedidos);
                            $stmt_hoy_pedidos->bind_param("s", $hoy);
                            $stmt_hoy_pedidos->execute();
                            $hoy_pedidos = $stmt_hoy_pedidos->get_result()->fetch_assoc();
                            ?>
                            <h4 class="text-info"><?php echo $hoy_pedidos['total']; ?></h4>
                            <p class="text-muted">Pedidos Completados Hoy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Cerrar statements
$stmt_entradas->close();
$stmt_salidas->close();
$stmt_resumen->close();
$stmt_hoy_entradas->close();
$stmt_hoy_salidas->close();
$stmt_hoy_pedidos->close();

include '../footer.php';
$conn->close();
?>