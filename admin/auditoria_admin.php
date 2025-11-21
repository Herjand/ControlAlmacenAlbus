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

// Filtros - SIN VALORES PREDEFINIDOS (comienzan vacíos)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : 'todos';
$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'todos';

// Consulta de logs de auditoría
$sql_where = "WHERE 1=1";
$params = [];
$types = "";

// Aplicar filtros solo si se han especificado
if ($fecha_inicio) {
    $sql_where .= " AND DATE(l.fecha) >= ?";
    $params[] = $fecha_inicio;
    $types .= "s";
}

if ($fecha_fin) {
    $sql_where .= " AND DATE(l.fecha) <= ?";
    $params[] = $fecha_fin;
    $types .= "s";
}

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

// Vincular parámetros solo si existen
if (!empty($params)) {
    switch ($types) {
        case "ss":
            $stmt->bind_param("ss", ...$params);
            break;
        case "ssi":
            $stmt->bind_param("ssi", ...$params);
            break;
        case "sss":
            $stmt->bind_param("sss", ...$params);
            break;
        case "ssis":
            $stmt->bind_param("ssis", ...$params);
            break;
        default:
            // Para otros casos de combinaciones de parámetros
            $stmt->bind_param($types, ...$params);
    }
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener usuarios para el filtro
$sql_usuarios = "SELECT id_usuario, nombre, rol FROM usuarios ORDER BY nombre";
$usuarios_result = $conn->query($sql_usuarios);

// Obtener módulos únicos
$sql_modulos = "SELECT DISTINCT modulo FROM logs WHERE modulo IS NOT NULL ORDER BY modulo";
$modulos_result = $conn->query($sql_modulos);

// Obtener estadísticas generales (sin filtros)
$sql_total_registros = "SELECT COUNT(*) as total FROM logs";
$total_registros = $conn->query($sql_total_registros)->fetch_assoc()['total'];

$sql_usuarios_totales = "SELECT COUNT(DISTINCT id_usuario) as total FROM logs";
$usuarios_totales = $conn->query($sql_usuarios_totales)->fetch_assoc()['total'];

$sql_modulos_totales = "SELECT COUNT(DISTINCT modulo) as total FROM logs WHERE modulo IS NOT NULL";
$modulos_totales = $conn->query($sql_modulos_totales)->fetch_assoc()['total'];

$sql_hoy = "SELECT COUNT(*) as total FROM logs WHERE DATE(fecha) = CURDATE()";
$acciones_hoy = $conn->query($sql_hoy)->fetch_assoc()['total'];
?>

<div class="container-fluid">
    <h2><i class="bi bi-clipboard-check"></i> Auditoría del Sistema</h2>
    <p class="text-muted">Registro completo de todas las actividades realizadas en el sistema.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Auditoría</h6>
            <?php if ($fecha_inicio || $fecha_fin || $usuario != 'todos' || $modulo != 'todos'): ?>
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
                <div class="col-md-2">
                    <label class="form-label">Usuario:</label>
                    <select class="form-select" name="usuario">
                        <option value="todos">Todos los usuarios</option>
                        <?php 
                        // Reset pointer para volver a leer los resultados
                        $usuarios_result->data_seek(0);
                        while ($user = $usuarios_result->fetch_assoc()): ?>
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
                        <?php 
                        // Reset pointer para volver a leer los resultados
                        $modulos_result->data_seek(0);
                        while ($mod = $modulos_result->fetch_assoc()): ?>
                            <option value="<?php echo $mod['modulo']; ?>" 
                                <?php echo $modulo == $mod['modulo'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mod['modulo']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="<?php echo $current_page; ?>" class="btn btn-outline-secondary" title="Mostrar todo">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Información del Filtro Aplicado -->
    <?php if ($fecha_inicio || $fecha_fin || $usuario != 'todos' || $modulo != 'todos'): ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-info-circle"></i> 
                <strong>Filtros aplicados:</strong>
                <?php
                $filtros = [];
                if ($fecha_inicio) $filtros[] = "Desde: " . date('d/m/Y', strtotime($fecha_inicio));
                if ($fecha_fin) $filtros[] = "Hasta: " . date('d/m/Y', strtotime($fecha_fin));
                if ($usuario != 'todos') {
                    $usuarios_result->data_seek(0);
                    while ($user = $usuarios_result->fetch_assoc()) {
                        if ($user['id_usuario'] == $usuario) {
                            $filtros[] = "Usuario: " . $user['nombre'];
                            break;
                        }
                    }
                }
                if ($modulo != 'todos') $filtros[] = "Módulo: " . $modulo;
                echo implode(' • ', $filtros);
                ?>
                <span class="badge bg-primary ms-2"><?php echo $result->num_rows; ?> resultados</span>
            </div>
            <a href="<?php echo $current_page; ?>" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>REGISTROS <?php echo ($fecha_inicio || $fecha_fin || $usuario != 'todos' || $modulo != 'todos') ? 'ENCONTRADOS' : 'TOTALES'; ?></h6>
                    <h4><?php echo ($fecha_inicio || $fecha_fin || $usuario != 'todos' || $modulo != 'todos') ? $result->num_rows : $total_registros; ?></h4>
                    <small class="opacity-75">
                        <?php if ($fecha_inicio || $fecha_fin || $usuario != 'todos' || $modulo != 'todos'): ?>
                            Con filtros aplicados
                        <?php else: ?>
                            En todo el historial
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>USUARIOS <?php echo ($fecha_inicio || $fecha_fin || $modulo != 'todos') ? 'ACTIVOS' : 'TOTALES'; ?></h6>
                    <h4>
                        <?php
                        if ($fecha_inicio || $fecha_fin || $modulo != 'todos') {
                            $sql_usuarios_filtro = "SELECT COUNT(DISTINCT id_usuario) as total FROM logs WHERE 1=1";
                            $params_ua = [];
                            if ($fecha_inicio) $sql_usuarios_filtro .= " AND DATE(fecha) >= ?";
                            if ($fecha_fin) $sql_usuarios_filtro .= " AND DATE(fecha) <= ?";
                            if ($modulo != 'todos') $sql_usuarios_filtro .= " AND modulo = ?";
                            
                            $stmt_ua = $conn->prepare($sql_usuarios_filtro);
                            if ($fecha_inicio) $params_ua[] = $fecha_inicio;
                            if ($fecha_fin) $params_ua[] = $fecha_fin;
                            if ($modulo != 'todos') $params_ua[] = $modulo;
                            
                            if (!empty($params_ua)) {
                                $types_ua = str_repeat("s", count($params_ua));
                                $stmt_ua->bind_param($types_ua, ...$params_ua);
                            }
                            $stmt_ua->execute();
                            echo $stmt_ua->get_result()->fetch_assoc()['total'];
                        } else {
                            echo $usuarios_totales;
                        }
                        ?>
                    </h4>
                    <small class="opacity-75">
                        <?php if ($fecha_inicio || $fecha_fin || $modulo != 'todos'): ?>
                            Con filtros aplicados
                        <?php else: ?>
                            Únicos en historial
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>MÓDULOS <?php echo ($fecha_inicio || $fecha_fin || $usuario != 'todos') ? 'UTILIZADOS' : 'TOTALES'; ?></h6>
                    <h4>
                        <?php
                        if ($fecha_inicio || $fecha_fin || $usuario != 'todos') {
                            $sql_modulos_filtro = "SELECT COUNT(DISTINCT modulo) as total FROM logs WHERE modulo IS NOT NULL";
                            $params_mu = [];
                            if ($fecha_inicio) {
                                $sql_modulos_filtro .= " AND DATE(fecha) >= ?";
                                $params_mu[] = $fecha_inicio;
                            }
                            if ($fecha_fin) {
                                $sql_modulos_filtro .= " AND DATE(fecha) <= ?";
                                $params_mu[] = $fecha_fin;
                            }
                            if ($usuario != 'todos') {
                                $sql_modulos_filtro .= " AND id_usuario = ?";
                                $params_mu[] = $usuario;
                            }
                            
                            $stmt_mu = $conn->prepare($sql_modulos_filtro);
                            if (!empty($params_mu)) {
                                $types_mu = str_repeat("s", count($params_mu));
                                $stmt_mu->bind_param($types_mu, ...$params_mu);
                            }
                            $stmt_mu->execute();
                            echo $stmt_mu->get_result()->fetch_assoc()['total'];
                        } else {
                            echo $modulos_totales;
                        }
                        ?>
                    </h4>
                    <small class="opacity-75">
                        <?php if ($fecha_inicio || $fecha_fin || $usuario != 'todos'): ?>
                            Con filtros aplicados
                        <?php else: ?>
                            Únicos en sistema
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6>ACCIONES HOY</h6>
                    <h4><?php echo $acciones_hoy; ?></h4>
                    <small class="opacity-75">Registros del día de hoy</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Registro de Actividades</span>
            <div>
                <button class="btn btn-sm btn-danger" onclick="exportarAuditoriaPDF()">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-success" onclick="exportarAuditoriaExcel()">
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
                    <i class="bi bi-info-circle"></i> 
                    <?php if ($fecha_inicio || $fecha_fin || $usuario != 'todos' || $modulo != 'todos'): ?>
                        No se encontraron registros de auditoría con los filtros aplicados.
                        <div class="mt-2">
                            <a href="<?php echo $current_page; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-clockwise"></i> Mostrar todos los registros
                            </a>
                        </div>
                    <?php else: ?>
                        No hay registros de auditoría en el sistema.
                    <?php endif; ?>
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
function exportarAuditoriaPDF() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    const usuario = document.querySelector('select[name="usuario"]').value;
    const modulo = document.querySelector('select[name="modulo"]').value;
    
    // Validar fechas si se han especificado
    if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser mayor a la fecha final');
        return;
    }
    
    const url = `funcionalidad_exportar_auditoria/exportar_pdfauditoria.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&usuario=${usuario}&modulo=${modulo}`;
    abrirExportacion(url, 'PDF (Auditoría)');
}

function exportarAuditoriaExcel() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    const usuario = document.querySelector('select[name="usuario"]').value;
    const modulo = document.querySelector('select[name="modulo"]').value;
    
    // Validar fechas si se han especificado
    if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
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