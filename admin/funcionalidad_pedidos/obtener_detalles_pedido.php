<?php
session_start();
require_once '../../connect.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    echo "<div class='alert alert-danger'>Acceso denegado</div>";
    exit();
}

// Verificar que el ID del pedido esté presente y sea válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID de pedido no especificado</div>";
    exit();
}

$id_pedido = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($id_pedido === false || $id_pedido <= 0) {
    echo "<div class='alert alert-danger'>ID de pedido inválido</div>";
    exit();
}

try {
    // Consultar información del pedido
    $sql_pedido = "SELECT * FROM pedidos WHERE id_pedido = ?";
    $stmt_pedido = $conn->prepare($sql_pedido);
    
    if (!$stmt_pedido) {
        throw new Exception("Error en la consulta del pedido: " . $conn->error);
    }
    
    $stmt_pedido->bind_param("i", $id_pedido);
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al ejecutar consulta del pedido: " . $stmt_pedido->error);
    }
    
    $result_pedido = $stmt_pedido->get_result();

    if ($result_pedido->num_rows === 0) {
        echo "<div class='alert alert-warning'>Pedido no encontrado</div>";
        exit();
    }

    $pedido = $result_pedido->fetch_assoc();

    // Consultar productos del pedido - SOLO información del pedido
    $sql_productos = "SELECT dp.*, p.nombre, p.unidad_medida 
                      FROM detalle_pedidos dp 
                      JOIN productos p ON dp.id_producto = p.id_producto 
                      WHERE dp.id_pedido = ?";
    $stmt_productos = $conn->prepare($sql_productos);
    
    if (!$stmt_productos) {
        throw new Exception("Error en la consulta de productos: " . $conn->error);
    }
    
    $stmt_productos->bind_param("i", $id_pedido);
    
    if (!$stmt_productos->execute()) {
        throw new Exception("Error al ejecutar consulta de productos: " . $stmt_productos->error);
    }
    
    $productos = $stmt_productos->get_result();
?>

<!-- Información del Pedido -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información del Pedido</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID Pedido:</strong> #<?php echo str_pad($pedido['id_pedido'], 4, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['empresa_cliente']); ?></p>
                <p><strong>Contacto:</strong> <?php echo htmlspecialchars($pedido['persona_contacto']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Fecha Entrega:</strong> <?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?></p>
                <p><strong>Estado:</strong> 
                    <span class="badge 
                        <?php 
                        switch($pedido['estado']) {
                            case 'Pendiente': echo 'bg-warning text-dark'; break;
                            case 'Completado': echo 'bg-success text-white'; break;
                            case 'Cancelado': echo 'bg-secondary text-white'; break;
                            default: echo 'bg-info text-white';
                        }
                        ?>
                    ">
                        <?php echo $pedido['estado']; ?>
                    </span>
                </p>
                <p><strong>Fecha Creación:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Productos del Pedido - SOLO lo que se pidió -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-box-seam"></i> Productos Solicitados</h6>
    </div>
    <div class="card-body">
        <?php if ($productos && $productos->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-secondary">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad Solicitada</th>
                            <th>Unidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_items = 0;
                        $total_productos = 0;
                        while ($producto = $productos->fetch_assoc()): 
                            $total_items += $producto['cantidad'];
                            $total_productos++;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $producto['cantidad']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php 
                                        $unidades = [
                                            'unidad' => 'und',
                                            'caja' => 'caja',
                                            'pack' => 'pack',
                                            'rollo' => 'rollo',
                                            'par' => 'par',
                                            'gramo' => 'g',
                                            'kilogramo' => 'kg',
                                            'metro' => 'm',
                                            'centimetro' => 'cm'
                                        ];
                                        echo $unidades[$producto['unidad_medida']] ?? $producto['unidad_medida'];
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td><strong>Total</strong></td>
                            <td><strong><?php echo $total_items; ?> items</strong></td>
                            <td><strong><?php echo $total_productos; ?> productos</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i> No hay productos en este pedido.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
    $stmt_pedido->close();
    $stmt_productos->close();
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
}
?>