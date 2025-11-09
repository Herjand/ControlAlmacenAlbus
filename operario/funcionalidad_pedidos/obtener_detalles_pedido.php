<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

include '../../connect.php';

if (isset($_GET['id_pedido'])) {
    $id_pedido = $_GET['id_pedido'];

    // Obtener información general del pedido
    $sql_pedido = "SELECT p.*, c.empresa, c.contacto, c.telefono, c.email, c.nit, c.direccion, c.ciudad 
                  FROM pedidos p 
                  JOIN clientes c ON p.id_cliente = c.id_cliente 
                  WHERE p.id_pedido = ?";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("i", $id_pedido);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();

    if ($result_pedido->num_rows === 0) {
        echo '<div class="alert alert-danger">Pedido no encontrado.</div>';
        exit();
    }

    $pedido = $result_pedido->fetch_assoc();

    // Obtener detalles del pedido
    $sql_detalles = "SELECT dp.*, p.nombre, p.presentacion, p.tamaño_peso, p.cantidad_unidad 
                    FROM detalle_pedidos dp 
                    JOIN productos p ON dp.id_producto = p.id_producto 
                    WHERE dp.id_pedido = ?";
    $stmt_detalles = $conn->prepare($sql_detalles);
    $stmt_detalles->bind_param("i", $id_pedido);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();

    ?>
    <div class="row">
        <div class="col-md-6">
            <h6><i class="bi bi-building"></i> Información del Cliente</h6>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Empresa:</th>
                    <td><?php echo htmlspecialchars($pedido['empresa']); ?></td>
                </tr>
                <tr>
                    <th>Contacto:</th>
                    <td><?php echo htmlspecialchars($pedido['contacto']); ?></td>
                </tr>
                <tr>
                    <th>Teléfono:</th>
                    <td><?php echo htmlspecialchars($pedido['telefono']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($pedido['email']); ?></td>
                </tr>
                <tr>
                    <th>NIT:</th>
                    <td><?php echo htmlspecialchars($pedido['nit']); ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6><i class="bi bi-truck"></i> Información de Entrega</h6>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Fecha Entrega:</th>
                    <td><?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?></td>
                </tr>
                <tr>
                    <th>Lugar Entrega:</th>
                    <td><?php echo htmlspecialchars($pedido['lugar_entrega'] ?: 'No especificado'); ?></td>
                </tr>
                <tr>
                    <th>Nota Remisión:</th>
                    <td><?php echo htmlspecialchars($pedido['nota_remision'] ?: 'No asignada'); ?></td>
                </tr>
                <tr>
                    <th>Dirección:</th>
                    <td><?php echo htmlspecialchars($pedido['direccion']); ?></td>
                </tr>
                <tr>
                    <th>Ciudad:</th>
                    <td><?php echo htmlspecialchars($pedido['ciudad']); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <hr>

    <h6><i class="bi bi-list-check"></i> Productos del Pedido</h6>
    <?php if ($result_detalles->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Producto</th>
                        <th>Especificaciones</th>
                        <th>Cantidad</th>
                        <th>Stock Disponible</th>
                        <th>Estado Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_productos = 0;
                    while ($detalle = $result_detalles->fetch_assoc()): 
                        $total_productos += $detalle['cantidad'];
                        
                        // Verificar stock disponible
                        $sql_stock = "SELECT stock FROM productos WHERE id_producto = ?";
                        $stmt_stock = $conn->prepare($sql_stock);
                        $stmt_stock->bind_param("i", $detalle['id_producto']);
                        $stmt_stock->execute();
                        $result_stock = $stmt_stock->get_result();
                        $stock = $result_stock->fetch_assoc()['stock'];
                        
                        $estado_stock = $stock >= $detalle['cantidad'] ? 'success' : 'danger';
                        $icono_stock = $stock >= $detalle['cantidad'] ? 'bi-check-circle' : 'bi-exclamation-triangle';
                        $texto_stock = $stock >= $detalle['cantidad'] ? 'Suficiente' : 'Insuficiente';
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($detalle['nombre']); ?></strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php 
                                    $especificaciones = [];
                                    if (!empty($detalle['presentacion'])) $especificaciones[] = $detalle['presentacion'];
                                    if (!empty($detalle['tamaño_peso'])) $especificaciones[] = $detalle['tamaño_peso'];
                                    if (!empty($detalle['cantidad_unidad'])) $especificaciones[] = $detalle['cantidad_unidad'];
                                    echo implode(' • ', $especificaciones);
                                    ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo $detalle['cantidad']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $stock; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $estado_stock; ?>">
                                    <i class="bi <?php echo $icono_stock; ?>"></i> <?php echo $texto_stock; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <td colspan="2"><strong>Total de productos:</strong></td>
                        <td><strong><?php echo $total_productos; ?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> No se encontraron productos en este pedido.
        </div>
    <?php endif; ?>
    <?php
} else {
    echo '<div class="alert alert-danger">ID de pedido no especificado.</div>';
}

$conn->close();
?>