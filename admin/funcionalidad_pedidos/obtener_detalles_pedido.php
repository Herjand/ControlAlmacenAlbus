<?php
session_start();
require_once '../../connect.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Acceso denegado</div>";
    exit();
}

// Verificar que el ID del pedido esté presente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> ID de pedido no especificado</div>";
    exit();
}

// Validar y sanitizar el ID
$id_pedido = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($id_pedido === false || $id_pedido <= 0) {
    echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> ID de pedido inválido</div>";
    exit();
}

try {
    // Consultar información del pedido
    $sql_pedido = "SELECT p.*, c.nit, c.telefono, c.email, c.direccion, c.ciudad 
                   FROM pedidos p 
                   LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                   WHERE p.id_pedido = ?";
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
        echo "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle'></i> Pedido #" . $id_pedido . " no encontrado</div>";
        exit();
    }

    $pedido = $result_pedido->fetch_assoc();

    // Consultar productos del pedido
    $sql_productos = "SELECT dp.*, p.nombre, p.descripcion, p.unidad_medida, p.stock,
                             p.tamaño_peso, p.presentacion, p.cantidad_unidad, p.tipo_especifico
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
    <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información del Pedido #<?php echo str_pad($pedido['id_pedido'], 4, '0', STR_PAD_LEFT); ?></h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['empresa_cliente']); ?></p>
                <?php if ($pedido['nit']): ?>
                    <p><strong>NIT:</strong> <?php echo htmlspecialchars($pedido['nit']); ?></p>
                <?php endif; ?>
                <p><strong>Contacto:</strong> <?php echo htmlspecialchars($pedido['persona_contacto']); ?></p>
                <?php if ($pedido['telefono']): ?>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                <?php endif; ?>
                <?php if ($pedido['email']): ?>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <p><strong>Fecha Entrega:</strong> <?php echo date('d/m/Y', strtotime($pedido['fecha_entrega'])); ?></p>
                <?php if ($pedido['nota_remision']): ?>
                    <p><strong>Nota de Remisión:</strong> <?php echo htmlspecialchars($pedido['nota_remision']); ?></p>
                <?php endif; ?>
                <?php if ($pedido['lugar_entrega']): ?>
                    <p><strong>Lugar de Entrega:</strong> <?php echo htmlspecialchars($pedido['lugar_entrega']); ?></p>
                <?php endif; ?>
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

<!-- Productos del Pedido -->
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
                            <th>Especificaciones</th>
                            <th>Cantidad Solicitada</th>
                            <th>Stock Disponible</th>
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
                            
                            // Verificar disponibilidad
                            $stock_suficiente = $producto['stock'] >= $producto['cantidad'];
                            $clase_stock = $stock_suficiente ? 'text-success' : 'text-danger';
                            $icono_stock = $stock_suficiente ? '✅' : '❌';
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    <?php if ($producto['descripcion']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php
                                        $especificaciones = [];
                                        if ($producto['tamaño_peso']) $especificaciones[] = $producto['tamaño_peso'];
                                        if ($producto['cantidad_unidad']) $especificaciones[] = $producto['cantidad_unidad'];
                                        if ($producto['tipo_especifico']) $especificaciones[] = $producto['tipo_especifico'];
                                        if ($producto['presentacion']) $especificaciones[] = $producto['presentacion'];
                                        
                                        echo !empty($especificaciones) ? implode(' • ', $especificaciones) : '- Sin especificaciones -';
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $producto['cantidad']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?php echo $clase_stock; ?>">
                                        <?php echo $icono_stock; ?> <?php echo $producto['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($producto['unidad_medida']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong><?php echo $total_items; ?> items</strong></td>
                            <td><strong><?php echo $total_productos; ?> productos</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Alerta de stock -->
            <?php
            // Verificar si hay productos con stock insuficiente
            $productos->data_seek(0);
            $stock_insuficiente = false;
            while ($producto = $productos->fetch_assoc()) {
                if ($producto['stock'] < $producto['cantidad']) {
                    $stock_insuficiente = true;
                    break;
                }
            }
            ?>
            
            <?php if ($stock_insuficiente): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Atención:</strong> Algunos productos no tienen stock suficiente para completar este pedido.
                </div>
            <?php endif; ?>
            
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
    echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
}
?>