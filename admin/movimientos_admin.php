<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';
include 'header_admin.php';

// Filtros
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$tipo_movimiento = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Consulta de movimientos combinados
$sql_where = "WHERE m.fecha BETWEEN ? AND ?";
if ($tipo_movimiento == 'entrada') {
    $sql_where .= " AND m.tipo = 'Entrada'";
} elseif ($tipo_movimiento == 'salida') {
    $sql_where .= " AND m.tipo = 'Salida'";
}

$sql = "SELECT m.*, p.nombre as producto_nombre, u.nombre as responsable_nombre 
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
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">
    <h2><i class="bi bi-arrow-left-right"></i> Reporte de Movimientos</h2>
    <p class="text-muted">Consulta detallada de todas las entradas y salidas del almacén.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio:</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin:</label>
                    <input type="date" class="form-control" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Aplicar Filtros
                    </button>
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
                    <h4><?php echo $result->num_rows; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>ENTRADAS</h6>
                    <h4>
                        <?php
                        $sql_entradas = "SELECT COUNT(*) as total FROM entradas WHERE fecha BETWEEN ? AND ?";
                        $stmt_entradas = $conn->prepare($sql_entradas);
                        $stmt_entradas->bind_param("ss", $fecha_inicio, $fecha_fin);
                        $stmt_entradas->execute();
                        echo $stmt_entradas->get_result()->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>SALIDAS</h6>
                    <h4>
                        <?php
                        $sql_salidas = "SELECT COUNT(*) as total FROM salidas WHERE fecha BETWEEN ? AND ?";
                        $stmt_salidas = $conn->prepare($sql_salidas);
                        $stmt_salidas->bind_param("ss", $fecha_inicio, $fecha_fin);
                        $stmt_salidas->execute();
                        echo $stmt_salidas->get_result()->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>PRODUCTOS INVOLUCRADOS</h6>
                    <h4>
                        <?php
                        $sql_productos = "SELECT COUNT(DISTINCT id_producto) as total FROM (
                            SELECT id_producto FROM entradas WHERE fecha BETWEEN ? AND ?
                            UNION 
                            SELECT id_producto FROM salidas WHERE fecha BETWEEN ? AND ?
                        ) as productos";
                        $stmt_productos = $conn->prepare($sql_productos);
                        $stmt_productos->bind_param("ssss", $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin);
                        $stmt_productos->execute();
                        echo $stmt_productos->get_result()->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Lista de Movimientos</span>
            <div>
                <!-- Botones básicos -->
                <button class="btn btn-sm btn-danger" onclick="exportarPDF()">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-success" onclick="exportarExcel()">
                    <i class="bi bi-file-excel"></i> Excel
                </button>

                <!-- O botón avanzado con opciones -->
                <!--
                <button class="btn btn-sm btn-primary" onclick="mostrarOpcionesExportacionAvanzadas()">
                    <i class="bi bi-download"></i> Exportar
                </button>
                -->
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
                                <th>Motivo</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['tipo'] == 'Entrada' ? 'bg-success' : 'bg-info'; ?>">
                                            <?php echo $row['tipo']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['producto_nombre']); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $row['cantidad']; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['responsable_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['motivo']); ?></td>
                                    <td><?php echo htmlspecialchars($row['observaciones'] ?? '-'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No se encontraron movimientos en el período seleccionado.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<<!-- SCRIPT EXPORTAR PDF y EXCEL -->
<script>
function exportarPDF() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    const tipo = document.querySelector('select[name="tipo"]').value;
    
    if (!fechaInicio || !fechaFin) {
        alert('Por favor, seleccione un rango de fechas válido');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
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
    
    if (!fechaInicio || !fechaFin) {
        alert('Por favor, seleccione un rango de fechas válido');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser mayor a la fecha final');
        return;
    }
    
    const url = `funcionalidad_exportar_movimientos/exportar_excelmovimientos.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&tipo_movimiento=${tipo}`;
    abrirExportacion(url, 'Excel');
}

function exportarResumen() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    
    if (!fechaInicio || !fechaFin) {
        alert('Por favor, seleccione un rango de fechas válido');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser mayor a la fecha final');
        return;
    }
    
    const url = `funcionalidad_exportar_movimientos/exportar_resumen.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    abrirExportacion(url, 'Excel (Resumen)');
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