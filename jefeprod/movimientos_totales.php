<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir consulta base
$sql = "SELECT 
        'Entrada' as tipo, 
        e.fecha, 
        p.nombre as producto, 
        e.cantidad,
        u.nombre as responsable,
        e.motivo,
        e.observaciones
        FROM entradas e 
        JOIN productos p ON e.id_producto = p.id_producto 
        JOIN usuarios u ON e.usuario_responsable = u.id_usuario
        WHERE 1=1";
        
$sql_union = "
        UNION ALL
        SELECT 
        'Salida' as tipo, 
        s.fecha, 
        p.nombre as producto, 
        s.cantidad,
        u.nombre as responsable,
        s.motivo,
        s.observaciones
        FROM salidas s 
        JOIN productos p ON s.id_producto = p.id_producto 
        JOIN usuarios u ON s.usuario_responsable = u.id_usuario
        WHERE 1=1";

// Aplicar filtros
$where_entradas = "";
$where_salidas = "";
$params = [];
$param_types = "";

if ($filtro_tipo) {
    if ($filtro_tipo == 'Entrada') {
        $sql_union = ""; // Solo entradas
    } elseif ($filtro_tipo == 'Salida') {
        $sql = "SELECT 
                'Salida' as tipo, 
                s.fecha, 
                p.nombre as producto, 
                s.cantidad,
                u.nombre as responsable,
                s.motivo,
                s.observaciones
                FROM salidas s 
                JOIN productos p ON s.id_producto = p.id_producto 
                JOIN usuarios u ON s.usuario_responsable = u.id_usuario
                WHERE 1=1";
        $sql_union = ""; // Solo salidas
    }
}

if ($filtro_fecha_desde) {
    $sql .= " AND DATE(e.fecha) >= ?";
    if ($sql_union) {
        $where_salidas .= " AND DATE(s.fecha) >= ?";
    }
    $params[] = $filtro_fecha_desde;
    $param_types .= "s";
}

if ($filtro_fecha_hasta) {
    $sql .= " AND DATE(e.fecha) <= ?";
    if ($sql_union) {
        $where_salidas .= " AND DATE(s.fecha) <= ?";
    }
    $params[] = $filtro_fecha_hasta;
    $param_types .= "s";
}

// Aplicar where a salidas si hay unión
if ($sql_union && ($where_salidas)) {
    $sql_union = str_replace("WHERE 1=1", "WHERE 1=1" . $where_salidas, $sql_union);
}

$sql_final = $sql . $sql_union . " ORDER BY fecha DESC";

// Preparar consulta para exportaciones
$stmt = $conn->prepare($sql_final);
if ($params) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$movimientos_data = $stmt->get_result();

// Estadísticas
$sql_stats = "SELECT 
              COUNT(*) as total_movimientos,
              SUM(CASE WHEN tipo = 'Entrada' THEN 1 ELSE 0 END) as total_entradas,
              SUM(CASE WHEN tipo = 'Salida' THEN 1 ELSE 0 END) as total_salidas
              FROM ($sql_final) as movimientos_stats";
              
$stmt_stats = $conn->prepare($sql_stats);
if ($params) {
    $stmt_stats->bind_param($param_types, ...$params);
}
$stmt_stats->execute();
$stats_result = $stmt_stats->get_result();
$stats = $stats_result->fetch_assoc();

// Exportar a Excel
if (isset($_GET['exportar_excel'])) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="movimientos_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Movimientos de Inventario - Albus S.A.</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; background: #2c3e50; color: white; padding: 20px; border-radius: 8px; }
            .header h1 { margin: 0; font-size: 24px; }
            .filtros { background: #ecf0f1; padding: 15px; margin: 15px 0; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th { background-color: #34495e; color: white; font-weight: bold; padding: 12px; border: 1px solid #2c3e50; }
            td { padding: 10px; border: 1px solid #ddd; }
            .entrada { background-color: #d4edda; }
            .salida { background-color: #fff3cd; }
            .numero { text-align: center; font-weight: bold; }
            .estadisticas { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
            .estadistica-item { background: white; padding: 15px; border-radius: 5px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .resumen { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>📊 MOVIMIENTOS DE INVENTARIO - ALBUS S.A.</h1>
            <p><strong>Generado:</strong> " . date('d/m/Y H:i') . " | <strong>Usuario:</strong> " . $_SESSION['usuario_nombre'] . "</p>
        </div>
        
        <div class='filtros'>
            <h3>🔍 FILTROS APLICADOS</h3>
            <p><strong>Tipo:</strong> " . ($filtro_tipo ? $filtro_tipo : 'Todos') . " | 
               <strong>Fecha desde:</strong> " . ($filtro_fecha_desde ? $filtro_fecha_desde : 'Inicio') . " | 
               <strong>Fecha hasta:</strong> " . ($filtro_fecha_hasta ? $filtro_fecha_hasta : date('Y-m-d')) . "</p>
        </div>
        
        <div class='estadisticas'>
            <div class='estadistica-item'>
                <div style='font-size: 28px; color: #3498db; font-weight: bold;'>" . $stats['total_movimientos'] . "</div>
                <div>TOTAL MOVIMIENTOS</div>
            </div>
            <div class='estadistica-item'>
                <div style='font-size: 28px; color: #27ae60; font-weight: bold;'>" . $stats['total_entradas'] . "</div>
                <div>ENTRADAS</div>
            </div>
            <div class='estadistica-item'>
                <div style='font-size: 28px; color: #e67e22; font-weight: bold;'>" . $stats['total_salidas'] . "</div>
                <div>SALIDAS</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th width='12%'>FECHA</th>
                    <th width='8%'>HORA</th>
                    <th width='10%'>TIPO</th>
                    <th width='25%'>PRODUCTO</th>
                    <th width='10%'>CANTIDAD</th>
                    <th width='15%'>MOTIVO</th>
                    <th width='15%'>RESPONSABLE</th>
                    <th width='15%'>OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>";
    
    if ($movimientos_data->num_rows > 0) {
        $movimientos_data->data_seek(0);
        while($movimiento = $movimientos_data->fetch_assoc()) {
            $clase_fila = $movimiento['tipo'] == 'Entrada' ? 'entrada' : 'salida';
            $signo = $movimiento['tipo'] == 'Entrada' ? '+' : '-';
            
            echo "<tr class='{$clase_fila}'>
                    <td>" . date('d/m/Y', strtotime($movimiento['fecha'])) . "</td>
                    <td class='numero'>" . date('H:i', strtotime($movimiento['fecha'])) . "</td>
                    <td class='numero'><strong>" . $movimiento['tipo'] . "</strong></td>
                    <td><strong>" . htmlspecialchars($movimiento['producto']) . "</strong></td>
                    <td class='numero'><strong>{$signo}" . $movimiento['cantidad'] . "</strong></td>
                    <td>" . htmlspecialchars($movimiento['motivo']) . "</td>
                    <td>" . htmlspecialchars($movimiento['responsable']) . "</td>
                    <td>" . ($movimiento['observaciones'] ? htmlspecialchars($movimiento['observaciones']) : '-') . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No hay movimientos que coincidan con los filtros aplicados</td></tr>";
    }
    
    echo "</tbody>
        </table>
        
        <div class='resumen'>
            <p><span style='background: #d4edda; padding: 8px 15px; border-radius: 4px; margin-right: 10px;'>📥 ENTRADA</span> 
               <span style='background: #fff3cd; padding: 8px 15px; border-radius: 4px;'>📤 SALIDA</span></p>
        </div>
        
        <div style='margin-top: 20px; text-align: center; color: #7f8c8d; font-size: 12px;'>
            <p>Documento generado automáticamente por el Sistema de Gestión Albus S.A.</p>
        </div>
    </body>
    </html>";
    exit();
}

// Exportar a PDF (HTML para imprimir como PDF)
if (isset($_GET['exportar_pdf'])) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="movimientos_' . date('Y-m-d') . '.html"');
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Movimientos de Inventario - Albus S.A.</title>
        <style>
            @media print {
                body { margin: 0; padding: 15px; font-size: 10px; }
                .no-print { display: none; }
                .header { background: #2c3e50 !important; -webkit-print-color-adjust: exact; }
                th { background: #34495e !important; -webkit-print-color-adjust: exact; }
                .entrada { background: #d4edda !important; -webkit-print-color-adjust: exact; }
                .salida { background: #fff3cd !important; -webkit-print-color-adjust: exact; }
            }
            body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; background: white; }
            .header { text-align: center; margin-bottom: 25px; background: #2c3e50; color: white; padding: 25px; border-radius: 8px; }
            .header h1 { margin: 0 0 10px 0; font-size: 26px; }
            .filtros { background: #ecf0f1; padding: 15px; margin: 15px 0; border-radius: 5px; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }
            th { background: #34495e; color: white; font-weight: 600; padding: 10px 6px; border: 1px solid #2c3e50; }
            td { padding: 8px 6px; border: 1px solid #e0e0e0; }
            .entrada { background: #d4edda; }
            .salida { background: #fff3cd; }
            .numero { text-align: center; font-weight: 600; }
            .estadisticas { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 15px 0; }
            .estadistica-item { background: white; padding: 12px; border-radius: 5px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e0e0e0; }
            .instrucciones { background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 5px; margin: 15px 0; font-size: 11px; }
            .resumen { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; font-size: 11px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>📊 HISTORIAL DE MOVIMIENTOS - ALBUS S.A.</h1>
            <div style='font-size: 13px;'>
                <p><strong>Generado:</strong> " . date('d/m/Y H:i') . " | 
                   <strong>Usuario:</strong> " . $_SESSION['usuario_nombre'] . " | 
                   <strong>Rol:</strong> Jefe de Producción</p>
            </div>
        </div>
        
        <div class='instrucciones no-print'>
            <h4>💡 INSTRUCCIONES PARA PDF</h4>
            <p>Para guardar como PDF: <strong>Ctrl+P → Seleccionar 'Guardar como PDF' → Imprimir</strong></p>
        </div>
        
        <div class='filtros'>
            <h4 style='margin: 0 0 8px 0;'>🔍 FILTROS APLICADOS</h4>
            <p style='margin: 0;'><strong>Periodo:</strong> " . ($filtro_fecha_desde ? $filtro_fecha_desde : 'Inicio') . " al " . ($filtro_fecha_hasta ? $filtro_fecha_hasta : date('Y-m-d')) . " | 
               <strong>Tipo:</strong> " . ($filtro_tipo ? $filtro_tipo : 'Todos los movimientos') . "</p>
        </div>
        
        <div class='estadisticas'>
            <div class='estadistica-item'>
                <div style='font-size: 22px; color: #3498db; font-weight: bold;'>" . $stats['total_movimientos'] . "</div>
                <div style='font-size: 11px;'>TOTAL MOVIMIENTOS</div>
            </div>
            <div class='estadistica-item'>
                <div style='font-size: 22px; color: #27ae60; font-weight: bold;'>" . $stats['total_entradas'] . "</div>
                <div style='font-size: 11px;'>ENTRADAS REGISTRADAS</div>
            </div>
            <div class='estadistica-item'>
                <div style='font-size: 22px; color: #e67e22; font-weight: bold;'>" . $stats['total_salidas'] . "</div>
                <div style='font-size: 11px;'>SALIDAS REGISTRADAS</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th width='11%'>FECHA</th>
                    <th width='7%'>HORA</th>
                    <th width='8%'>TIPO</th>
                    <th width='22%'>PRODUCTO</th>
                    <th width='8%'>CANTIDAD</th>
                    <th width='14%'>MOTIVO</th>
                    <th width='15%'>RESPONSABLE</th>
                    <th width='15%'>OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>";
    
    if ($movimientos_data->num_rows > 0) {
        $movimientos_data->data_seek(0);
        while($movimiento = $movimientos_data->fetch_assoc()) {
            $clase_fila = $movimiento['tipo'] == 'Entrada' ? 'entrada' : 'salida';
            $signo = $movimiento['tipo'] == 'Entrada' ? '+' : '-';
            
            echo "<tr class='{$clase_fila}'>
                    <td>" . date('d/m/Y', strtotime($movimiento['fecha'])) . "</td>
                    <td class='numero'>" . date('H:i', strtotime($movimiento['fecha'])) . "</td>
                    <td class='numero'><strong>" . $movimiento['tipo'] . "</strong></td>
                    <td><strong>" . htmlspecialchars($movimiento['producto']) . "</strong></td>
                    <td class='numero'><strong>{$signo}" . $movimiento['cantidad'] . "</strong></td>
                    <td>" . htmlspecialchars($movimiento['motivo']) . "</td>
                    <td>" . htmlspecialchars($movimiento['responsable']) . "</td>
                    <td>" . ($movimiento['observaciones'] ? htmlspecialchars($movimiento['observaciones']) : '-') . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='8' style='text-align: center; padding: 20px; color: #7f8c8d;'>No se encontraron movimientos con los filtros aplicados</td></tr>";
    }
    
    echo "</tbody>
        </table>
        
        <div class='resumen'>
            <h4 style='margin-bottom: 10px;'>📋 RESUMEN EJECUTIVO</h4>
            <p><strong>Total de registros:</strong> " . $stats['total_movimientos'] . " movimientos</p>
            <p><strong>Entradas:</strong> " . $stats['total_entradas'] . " | <strong>Salidas:</strong> " . $stats['total_salidas'] . "</p>
        </div>
        
        <div style='margin-top: 20px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ecf0f1; padding-top: 12px;'>
            <p>📄 Sistema de Gestión de Inventario Albus S.A. - " . date('d/m/Y H:i') . "</p>
        </div>
    </body>
    </html>";
    exit();
}

// Reset para la vista web
$movimientos_data->data_seek(0);

// Ahora incluimos el header después de procesar todo
include 'header_jefe_produccion.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-list-check"></i> Movimientos Totales</h2>
            <p class="text-muted mb-0">Historial completo de entradas y salidas</p>
        </div>
        <div class="text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="?<?php echo http_build_query($_GET); ?>&exportar_excel=1">
                            <i class="bi bi-file-earmark-excel text-success"></i> Excel
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?<?php echo http_build_query($_GET); ?>&exportar_pdf=1">
                            <i class="bi bi-file-earmark-pdf text-danger"></i> PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0"><?php echo $stats['total_movimientos']; ?></h4>
                    <small>Total Movimientos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0"><?php echo $stats['total_entradas']; ?></h4>
                    <small>Entradas</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h4 class="mb-0"><?php echo $stats['total_salidas']; ?></h4>
                    <small>Salidas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Movimiento</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos los movimientos</option>
                        <option value="Entrada" <?php echo $filtro_tipo == 'Entrada' ? 'selected' : ''; ?>>Solo Entradas</option>
                        <option value="Salida" <?php echo $filtro_tipo == 'Salida' ? 'selected' : ''; ?>>Solo Salidas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_desde" class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                           value="<?php echo $filtro_fecha_desde; ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                           value="<?php echo $filtro_fecha_hasta; ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Filtrar
                        </button>
                        <a href="movimientos_totales.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-table"></i> Historial de Movimientos
            </h5>
            <span class="badge bg-light text-dark">
                <?php echo $movimientos_data->num_rows; ?> registros
            </span>
        </div>
        <div class="card-body">
            <?php if ($movimientos_data->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Responsable</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($movimiento = $movimientos_data->fetch_assoc()): ?>
                                <tr>
                                    <td class="small">
                                        <?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $movimiento['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $movimiento['tipo']; ?>
                                        </span>
                                    </td>
                                    <td class="small fw-bold"><?php echo htmlspecialchars($movimiento['producto']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $movimiento['tipo'] == 'Entrada' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $movimiento['tipo'] == 'Entrada' ? '+' : '-'; ?><?php echo $movimiento['cantidad']; ?>
                                        </span>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($movimiento['motivo']); ?></td>
                                    <td class="small"><?php echo htmlspecialchars($movimiento['responsable']); ?></td>
                                    <td class="small text-muted">
                                        <?php echo $movimiento['observaciones'] ? htmlspecialchars($movimiento['observaciones']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No hay movimientos que coincidan con los filtros.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
$conn->close();
?>