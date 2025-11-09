<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';
include '../header_operario.php';

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;

// Filtros
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$producto_id = isset($_GET['producto_id']) ? $_GET['producto_id'] : '';
$motivo = isset($_GET['motivo']) ? $_GET['motivo'] : '';

// Verificar si hay filtros activos
$filtros_activos = !empty($fecha_inicio) || !empty($fecha_fin) || !empty($producto_id) || !empty($motivo);

// Construir consulta base
$sql = "SELECT e.*, p.nombre as producto_nombre, u.nombre as usuario_nombre 
        FROM entradas e 
        JOIN productos p ON e.id_producto = p.id_producto 
        JOIN usuarios u ON e.usuario_responsable = u.id_usuario 
        WHERE 1=1";

$sql_count = "SELECT COUNT(*) as total 
              FROM entradas e 
              JOIN productos p ON e.id_producto = p.id_producto 
              WHERE 1=1";

// Aplicar filtros
$params = [];
$params_count = [];

if (!empty($fecha_inicio)) {
    $sql .= " AND DATE(e.fecha) >= ?";
    $sql_count .= " AND DATE(e.fecha) >= ?";
    $params[] = $fecha_inicio;
    $params_count[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $sql .= " AND DATE(e.fecha) <= ?";
    $sql_count .= " AND DATE(e.fecha) <= ?";
    $params[] = $fecha_fin;
    $params_count[] = $fecha_fin;
}

if (!empty($producto_id)) {
    $sql .= " AND e.id_producto = ?";
    $sql_count .= " AND e.id_producto = ?";
    $params[] = $producto_id;
    $params_count[] = $producto_id;
}

if (!empty($motivo)) {
    $sql .= " AND e.motivo = ?";
    $sql_count .= " AND e.motivo = ?";
    $params[] = $motivo;
    $params_count[] = $motivo;
}

// Orden y límite
$sql .= " ORDER BY e.fecha DESC LIMIT ? OFFSET ?";
$params[] = $limite;
$params[] = $offset;

// Obtener total de registros
$stmt_count = $conn->prepare($sql_count);
if (!empty($params_count)) {
    $types = str_repeat('s', count($params_count));
    $stmt_count->bind_param($types, ...$params_count);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_registros = $result_count->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $limite);

// Obtener entradas
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params) - 2) . 'ii'; // Los últimos dos parámetros son enteros
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Consultar productos para el filtro
$sql_productos = "SELECT id_producto, nombre FROM productos ORDER BY nombre";
$productos_result = $conn->query($sql_productos);

// Motivos disponibles
$motivos = ['Compra', 'Devolución', 'Ajuste de Inventario', 'Producción', 'Transferencia', 'Otro'];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clock-history"></i> Historial de Entradas</h2>
        <div>
            <!-- Botón Volver a Entradas - CORREGIDO -->
            <a href="<?php echo dirname($_SERVER['PHP_SELF']) . '/../entradas_operario.php'; ?>" class="btn btn-success">
                <i class="bi bi-arrow-left-circle"></i> Volver a Entradas
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-funnel"></i> Filtros
                <?php if ($filtros_activos): ?>
                    <span class="badge bg-warning text-dark ms-2">Filtros activos</span>
                <?php endif; ?>
            </div>
            <?php if ($filtros_activos): ?>
                <!-- Botón Limpiar Filtros - CORREGIDO -->
                <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle"></i> Limpiar Filtros
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Producto:</label>
                        <select class="form-select" name="producto_id">
                            <option value="">Todos los productos</option>
                            <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                                <?php while ($producto = $productos_result->fetch_assoc()): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>" 
                                        <?php echo $producto_id == $producto['id_producto'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Motivo:</label>
                        <select class="form-select" name="motivo">
                            <option value="">Todos los motivos</option>
                            <?php foreach ($motivos as $mot): ?>
                                <option value="<?php echo $mot; ?>" 
                                    <?php echo $motivo == $mot ? 'selected' : ''; ?>>
                                    <?php echo $mot; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <?php if ($filtros_activos): ?>
                                <!-- Botón Limpiar en el formulario - CORREGIDO -->
                                <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <!-- Resumen de resultados -->
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="bi bi-info-circle"></i>
                        Mostrando <?php echo $result->num_rows; ?> de <?php echo $total_registros; ?> entradas
                        <?php if ($filtros_activos): ?>
                            <span class="badge bg-warning ms-2">Resultados filtrados</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($filtros_activos): ?>
                        <!-- Botón Limpiar en resultados - CORREGIDO -->
                        <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Limpiar Filtros
                        </a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Observaciones</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($entrada = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($entrada['producto_nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">+<?php echo $entrada['cantidad']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($entrada['motivo']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($entrada['observaciones']); ?></td>
                                    <td><?php echo htmlspecialchars($entrada['usuario_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>">
                                    <i class="bi bi-chevron-left"></i> Anterior
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagina < $total_paginas): ?>
                            <li class="page-item">  
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>">
                                    Siguiente <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-3">No hay entradas registradas.</p>
                    
                    <?php if ($filtros_activos): ?>
                        <div class="alert alert-warning">
                            <p>No se encontraron resultados con los filtros aplicados.</p>
                            <!-- Botón Limpiar cuando no hay resultados -->
                            <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle"></i> Limpiar Filtros
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Botón Volver cuando no hay entradas -->
                        <a href="<?php echo dirname($_SERVER['PHP_SELF']) . '/../entradas_operario.php'; ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Registrar Primera Entrada
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../../footer.php'; 
?>