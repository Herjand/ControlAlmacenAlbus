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
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : 'todos';
$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'todos';

// Consulta de logs de auditoría
$sql_where = "WHERE l.fecha BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if ($usuario != 'todos') {
    $sql_where .= " AND l.id_usuario = ?";
    $params[] = $usuario;
    $types .= "i";
}

if ($modulo != 'todos') {
    $sql_where .= " AND l.modulo = ?";
    $params[] = $modulo;
    $types .= "s";
}

$sql = "SELECT l.*, u.nombre as usuario_nombre, u.rol as usuario_rol
        FROM logs l
        JOIN usuarios u ON l.id_usuario = u.id_usuario
        $sql_where
        ORDER BY l.fecha DESC
        LIMIT 500";

$stmt = $conn->prepare($sql);
if ($types == "ss") {
    $stmt->bind_param("ss", ...$params);
} elseif ($types == "ssi") {
    $stmt->bind_param("ssi", ...$params);
} elseif ($types == "sss") {
    $stmt->bind_param("sss", ...$params);
} else {
    $stmt->bind_param("ssis", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Obtener usuarios para el filtro
$sql_usuarios = "SELECT id_usuario, nombre, rol FROM usuarios ORDER BY nombre";
$usuarios_result = $conn->query($sql_usuarios);

// Obtener módulos únicos
$sql_modulos = "SELECT DISTINCT modulo FROM logs WHERE modulo IS NOT NULL ORDER BY modulo";
$modulos_result = $conn->query($sql_modulos);
?>

<div class="container-fluid">
    <h2><i class="bi bi-clipboard-check"></i> Auditoría del Sistema</h2>
    <p class="text-muted">Registro completo de todas las actividades realizadas en el sistema.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Auditoría</h6>
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
                <div class="col-md-2">
                    <label class="form-label">Usuario:</label>
                    <select class="form-select" name="usuario">
                        <option value="todos">Todos los usuarios</option>
                        <?php while ($user = $usuarios_result->fetch_assoc()): ?>
                            <option value="<?php echo $user['id_usuario']; ?>" 
                                <?php echo $usuario == $user['id_usuario'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['nombre']); ?> (<?php echo $user['rol']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Módulo:</label>
                    <select class="form-select" name="modulo">
                        <option value="todos">Todos los módulos</option>
                        <?php while ($mod = $modulos_result->fetch_assoc()): ?>
                            <option value="<?php echo $mod['modulo']; ?>" 
                                <?php echo $modulo == $mod['modulo'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mod['modulo']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>REGISTROS ENCONTRADOS</h6>
                    <h4><?php echo $result->num_rows; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>USUARIOS ACTIVOS</h6>
                    <h4>
                        <?php
                        $sql_usuarios_activos = "SELECT COUNT(DISTINCT id_usuario) as total FROM logs WHERE fecha BETWEEN ? AND ?";
                        $stmt_ua = $conn->prepare($sql_usuarios_activos);
                        $stmt_ua->bind_param("ss", $fecha_inicio, $fecha_fin);
                        $stmt_ua->execute();
                        echo $stmt_ua->get_result()->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>MÓDULOS UTILIZADOS</h6>
                    <h4>
                        <?php
                        $sql_modulos_utilizados = "SELECT COUNT(DISTINCT modulo) as total FROM logs WHERE fecha BETWEEN ? AND ? AND modulo IS NOT NULL";
                        $stmt_mu = $conn->prepare($sql_modulos_utilizados);
                        $stmt_mu->bind_param("ss", $fecha_inicio, $fecha_fin);
                        $stmt_mu->execute();
                        echo $stmt_mu->get_result()->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>ACCIONES HOY</h6>
                    <h4>
                        <?php
                        $sql_hoy = "SELECT COUNT(*) as total FROM logs WHERE DATE(fecha) = CURDATE()";
                        echo $conn->query($sql_hoy)->fetch_assoc()['total'];
                        ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Registro de Actividades</span>
            <button class="btn btn-sm btn-light" onclick="exportarAuditoriaExcel()">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
            </button>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-secondary">
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($row['fecha'])); ?>
                                        <br><small class="text-muted"><?php echo date('H:i:s', strtotime($row['fecha'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['usuario_nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['usuario_rol'] == 'Administrador' ? 'bg-primary' : 'bg-secondary'; ?>">
                                            <?php echo $row['usuario_rol']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['modulo']): ?>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($row['modulo']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            if (strpos($row['accion'], 'registro') !== false) echo 'bg-success';
                                            elseif (strpos($row['accion'], 'editó') !== false || strpos($row['accion'], 'actualizó') !== false) echo 'bg-warning text-dark';
                                            elseif (strpos($row['accion'], 'eliminó') !== false) echo 'bg-danger';
                                            else echo 'bg-primary';
                                            ?>">
                                            <?php echo htmlspecialchars($row['accion']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($row['detalles'] ?? 'Sin detalles adicionales'); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Mostrando los últimos <?php echo $result->num_rows; ?> registros
                    </small>
                    <small class="text-muted">
                        Los registros de auditoría se mantienen por 6 meses
                    </small>
                </div>
                
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No se encontraron registros de auditoría con los filtros seleccionados.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información de Auditoría -->
    <div class="card mt-4 border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información de Auditoría</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>¿Qué se registra?</h6>
                    <ul class="small">
                        <li>Inicios y cierres de sesión</li>
                        <li>Altas, bajas y modificaciones de registros</li>
                        <li>Movimientos de inventario</li>
                        <li>Generación de reportes</li>
                        <li>Cambios en la configuración del sistema</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Política de retención</h6>
                    <ul class="small">
                        <li>Los registros se mantienen por 6 meses</li>
                        <li>Máximo 10,000 registros en la base de datos</li>
                        <li>Exportaciones disponibles para archivo permanente</li>
                        <li>Cumplimiento con políticas de seguridad</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT EXPORTAR AUDITORÍA -->
<script>
function exportarAuditoriaExcel() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    const usuario = document.querySelector('select[name="usuario"]').value;
    const modulo = document.querySelector('select[name="modulo"]').value;
    
    if (!fechaInicio || !fechaFin) {
        alert('Por favor, seleccione un rango de fechas válido');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser mayor a la fecha final');
        return;
    }
    
    const url = `funcionalidad_exportar_auditoria/exportar_excelauditoria.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&usuario=${usuario}&modulo=${modulo}`;
    abrirExportacion(url, 'Excel (Auditoría)');
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
$stmt->close();
$conn->close();
include '../footer.php';
?>