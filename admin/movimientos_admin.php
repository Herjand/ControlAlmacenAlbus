<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Función helper para obtener la URL actual
function getCurrentPageUrl() {
    return basename($_SERVER['PHP_SELF']);
}

$current_page = getCurrentPageUrl();

// Filtros - SIN VALORES PREDEFINIDOS
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$tipo_movimiento = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Construir consulta base
$sql_where = "WHERE 1=1";
$params = [];
$param_types = "";

// Aplicar filtros solo si se han especificado
if ($fecha_inicio) {
    $sql_where .= " AND DATE(m.fecha) >= ?";
    $params[] = $fecha_inicio;
    $param_types .= "s";
}

if ($fecha_fin) {
    $sql_where .= " AND DATE(m.fecha) <= ?";
    $params[] = $fecha_fin;
    $param_types .= "s";
}

if ($tipo_movimiento == 'entrada') {
    $sql_where .= " AND m.tipo = 'Entrada'";
} elseif ($tipo_movimiento == 'salida') {
    $sql_where .= " AND m.tipo = 'Salida'";
}

$sql = "SELECT m.*, p.nombre as producto_nombre, u.nombre as responsable_nombre, u.rol as rol_responsable
        FROM (
            SELECT 
                'Entrada' as tipo,
                id_entrada as id,
                id_producto,
                cantidad,
                fecha,
                usuario_responsable,
                motivo,
                observaciones
            FROM entradas
            UNION ALL
            SELECT 
                'Salida' as tipo,
                id_salida as id,
                id_producto,
                cantidad,
                fecha,
                usuario_responsable,
                motivo,
                observaciones
            FROM salidas
        ) as m
        JOIN productos p ON m.id_producto = p.id_producto
        JOIN usuarios u ON m.usuario_responsable = u.id_usuario
        $sql_where
        ORDER BY m.fecha DESC";

$stmt = $conn->prepare($sql);

// Vincular parámetros solo si existen
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener estadísticas
$total_movimientos = $result->num_rows;

// Contar entradas y salidas
$sql_estadisticas = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN tipo = 'Entrada' THEN 1 ELSE 0 END) as entradas,
    SUM(CASE WHEN tipo = 'Salida' THEN 1 ELSE 0 END) as salidas
    FROM (
        SELECT 'Entrada' as tipo FROM entradas
        UNION ALL
        SELECT 'Salida' as tipo FROM salidas
    ) as movimientos";

$estadisticas_result = $conn->query($sql_estadisticas);
$estadisticas = $estadisticas_result->fetch_assoc();

// Contar productos involucrados
$sql_productos = "SELECT COUNT(DISTINCT id_producto) as total FROM (
    SELECT id_producto FROM entradas
    UNION 
    SELECT id_producto FROM salidas
) as productos";
$productos_result = $conn->query($sql_productos);
$total_productos = $productos_result->fetch_assoc()['total'];
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-left-right"></i> Reporte de Movimientos</h2>
    <p class="text-muted">Consulta detallada de todas las entradas y salidas del almacén.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h6>
            <?php if ($fecha_inicio || $fecha_fin || $tipo_movimiento != 'todos'): ?>
                <a href="<?php echo $current_page; ?>" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x-circle"></i> Borrar Filtros
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio:</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                    <small class="text-muted">Dejar vacío para mostrar todo</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin:</label>
                    <input type="date" class="form-control" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                    <small class="text-muted">Dejar vacío para mostrar todo</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo Movimiento:</label>
                    <select class="form-select" name="tipo">
                        <option value="todos" <?php echo $tipo_movimiento == 'todos' ? 'selected' : ''; ?>>Todos los movimientos</option>
                        <option value="entrada" <?php echo $tipo_movimiento == 'entrada' ? 'selected' : ''; ?>>Solo Entradas</option>
                        <option value="salida" <?php echo $tipo_movimiento == 'salida' ? 'selected' : ''; ?>>Solo Salidas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Aplicar Filtros
                        </button>
                        <a href="<?php echo $current_page; ?>" class="btn btn-outline-secondary" title="Mostrar todo">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen Estadístico -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>TOTAL MOVIMIENTOS</h6>
                    <h4><?php echo $estadisticas['total']; ?></h4>
                    <small class="opacity-75">En todo el historial</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>ENTRADAS</h6>
                    <h4><?php echo $estadisticas['entradas']; ?></h4>
                    <small class="opacity-75">Registros de entrada</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>SALIDAS</h6>
                    <h4><?php echo $estadisticas['salidas']; ?></h4>
                    <small class="opacity-75">Registros de salida</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>PRODUCTOS</h6>
                    <h4><?php echo $total_productos; ?></h4>
                    <small class="opacity-75">Productos involucrados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Filtro Aplicado -->
    <?php if ($fecha_inicio || $fecha_fin || $tipo_movimiento != 'todos'): ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-info-circle"></i> 
                <strong>Filtros aplicados:</strong>
                <?php
                $filtros = [];
                if ($fecha_inicio) $filtros[] = "Desde: " . date('d/m/Y', strtotime($fecha_inicio));
                if ($fecha_fin) $filtros[] = "Hasta: " . date('d/m/Y', strtotime($fecha_fin));
                if ($tipo_movimiento == 'entrada') $filtros[] = "Solo Entradas";
                if ($tipo_movimiento == 'salida') $filtros[] = "Solo Salidas";
                echo implode(' • ', $filtros);
                ?>
                <span class="badge bg-primary ms-2"><?php echo $total_movimientos; ?> resultados</span>
            </div>
            <a href="<?php echo $current_page; ?>" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Movimientos -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Lista de Movimientos</span>
            <div>
                <button class="btn btn-sm btn-danger" onclick="exportarPDF()">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-success" onclick="exportarExcel()">
                    <i class="bi bi-file-excel"></i> Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-secondary">
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Responsable</th>
                                <th>Rol</th>
                                <th>Motivo</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="small">
                                        <?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $row['tipo']; ?>
                                        </span>
                                    </td>
                                    <td class="small fw-bold"><?php echo htmlspecialchars($row['producto_nombre']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $row['tipo'] == 'Entrada' ? '+' : '-'; ?><?php echo $row['cantidad']; ?>
                                        </span>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($row['responsable_nombre']); ?></td>
                                    <td class="small">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($row['rol_responsable']); ?></span>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($row['motivo']); ?></td>
                                    <td class="small text-muted">
                                        <?php echo $row['observaciones'] ? htmlspecialchars($row['observaciones']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> 
                    <?php if ($fecha_inicio || $fecha_fin || $tipo_movimiento != 'todos'): ?>
                        No se encontraron movimientos con los filtros aplicados.
                        <div class="mt-2">
                            <a href="<?php echo $current_page; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-clockwise"></i> Mostrar todos los movimientos
                            </a>
                        </div>
                    <?php else: ?>
                        No hay movimientos registrados en el sistema.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SCRIPT EXPORTAR PDF y EXCEL -->
<script>
function exportarPDF() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    const tipo = document.querySelector('select[name="tipo"]').value;
    
    // Validar fechas si se han especificado
    if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser mayor a la fecha final');
        return;
    }
    
    const url = `funcionalidad_exportar_movimientos/exportar_pdfmovimientos.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_movimiento=${tipo}`;
    abrirExportacion(url, 'PDF');
}

function exportarExcel() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    const tipo = document.querySelector('select[name="tipo"]').value;
    
    // Validar fechas si se han especificado
    if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser mayor a la fecha final');
        return;
    }
    
    const url = `funcionalidad_exportar_movimientos/exportar_excelmovimientos.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_movimiento=${tipo}`;
    abrirExportacion(url, 'Excel');
}

function abrirExportacion(url, tipo) {
    mostrarMensajeCarga(tipo);
    
    const nuevaVentana = window.open(url, '_blank');
    
    if (!nuevaVentana) {
        alert('Por favor, permita ventanas emergentes para descargar el reporte');
        removerMensajeCarga();
        return;
    }
    
    setTimeout(() => {
        removerMensajeCarga();
        mostrarMensajeExito(tipo);
    }, 3000);
}

function mostrarMensajeCarga(tipo) {
    const alerta = document.createElement('div');
    alerta.id = 'alerta-exportacion';
    alerta.className = 'alert alert-info alert-dismissible fade show position-fixed';
    alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alerta.innerHTML = `
        <i class="bi bi-cloud-download"></i> Generando ${tipo}...
        <div class="progress mt-2" style="height: 5px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
    `;
    document.body.appendChild(alerta);
}

function removerMensajeCarga() {
    const alerta = document.getElementById('alerta-exportacion');
    if (alerta) alerta.remove();
}

function mostrarMensajeExito(tipo) {
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alerta.innerHTML = `
        <i class="bi bi-check-circle"></i> ${tipo} generado exitosamente
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alerta);
    
    setTimeout(() => { if (alerta.parentNode) alerta.remove(); }, 5000);
}

// Función para limpiar filtros rápidamente
function limpiarFiltros() {
    window.location.href = '<?php echo $current_page; ?>';
}
</script>

<?php 
// Cerrar conexiones si están abiertas
if (isset($stmt) && $stmt) {
    $stmt->close();
}
if (isset($conn) && $conn) {
    $conn->close();
}
include '../footer.php';
?>