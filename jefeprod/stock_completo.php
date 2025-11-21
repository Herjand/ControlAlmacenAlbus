<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Jefe de Producción') {
    header("Location: ../login.php");
    exit();
}

include '../connect.php';

// Obtener datos para exportación
$sql_productos = "SELECT * FROM productos ORDER BY nombre";
$productos_data = $conn->query($sql_productos);

// Exportar a Excel
if (isset($_GET['exportar_excel'])) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="stock_completo_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Stock Completo - Albus S.A.</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; background: #2c3e50; color: white; padding: 20px; }
            .header h1 { margin: 0; font-size: 24px; }
            .header p { margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #34495e; color: white; font-weight: bold; padding: 12px; border: 1px solid #2c3e50; }
            td { padding: 10px; border: 1px solid #ddd; }
            .agotado { background-color: #ffebee; color: #c62828; }
            .critico { background-color: #ffebee; color: #c62828; }
            .bajo { background-color: #fff3e0; color: #ef6c00; }
            .optimo { background-color: #e8f5e8; color: #2e7d32; }
            .numero { text-align: center; font-weight: bold; }
            .estado { text-align: center; font-weight: bold; padding: 8px; border-radius: 4px; }
            .resumen { background: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #3498db; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>📦 STOCK COMPLETO - ALBUS S.A.</h1>
            <p><strong>Generado:</strong> " . date('d/m/Y H:i') . "</p>
            <p><strong>Usuario:</strong> " . $_SESSION['usuario_nombre'] . " (Jefe de Producción)</p>
        </div>";
    
    // Estadísticas rápidas
    $total_productos = $productos_data->num_rows;
    $stock_bajo = 0;
    $stock_critico = 0;
    $stock_optimo = 0;
    
    $productos_data->data_seek(0);
    while($producto = $productos_data->fetch_assoc()) {
        $diferencia = $producto['stock'] - $producto['stock_minimo'];
        if ($producto['stock'] == 0) {
            $stock_critico++;
        } elseif ($diferencia < 0) {
            $stock_critico++;
        } elseif ($diferencia < 5) {
            $stock_bajo++;
        } else {
            $stock_optimo++;
        }
    }
    
    echo "<div class='resumen'>
            <h3>📊 RESUMEN GENERAL</h3>
            <p><strong>Total Productos:</strong> {$total_productos} | 
               <strong>Óptimos:</strong> {$stock_optimo} | 
               <strong>Bajos:</strong> {$stock_bajo} | 
               <strong>Críticos:</strong> {$stock_critico}</p>
          </div>";
    
    echo "<table>
            <thead>
                <tr>
                    <th width='25%'>PRODUCTO</th>
                    <th width='30%'>DESCRIPCIÓN</th>
                    <th width='12%'>STOCK ACTUAL</th>
                    <th width='12%'>STOCK MÍNIMO</th>
                    <th width='11%'>ESTADO</th>
                    <th width='10%'>DIFERENCIA</th>
                </tr>
            </thead>
            <tbody>";
    
    $productos_data->data_seek(0);
    while($producto = $productos_data->fetch_assoc()) {
        $diferencia = $producto['stock'] - $producto['stock_minimo'];
        
        if ($producto['stock'] == 0) {
            $estado = 'AGOTADO';
            $clase_fila = 'agotado';
            $clase_estado = 'agotado';
        } elseif ($diferencia < 0) {
            $estado = 'CRÍTICO';
            $clase_fila = 'critico';
            $clase_estado = 'critico';
        } elseif ($diferencia < 5) {
            $estado = 'BAJO';
            $clase_fila = 'bajo';
            $clase_estado = 'bajo';
        } else {
            $estado = 'ÓPTIMO';
            $clase_fila = 'optimo';
            $clase_estado = 'optimo';
        }
        
        echo "<tr class='{$clase_fila}'>
                <td><strong>" . htmlspecialchars($producto['nombre']) . "</strong></td>
                <td>" . htmlspecialchars($producto['descripcion']) . "</td>
                <td class='numero'>" . $producto['stock'] . "</td>
                <td class='numero'>" . $producto['stock_minimo'] . "</td>
                <td class='estado {$clase_estado}'>" . $estado . "</td>
                <td class='numero " . ($diferencia >= 0 ? 'optimo' : 'critico') . "'>" . ($diferencia >= 0 ? '+' : '') . $diferencia . "</td>
              </tr>";
    }
    
    echo "</tbody>
        </table>
        
        <div style='margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>
            <p><span style='background: #e8f5e8; padding: 5px 10px; border-radius: 3px; margin-right: 10px;'>ÓPTIMO</span> 
               <span style='background: #fff3e0; padding: 5px 10px; border-radius: 3px; margin-right: 10px;'>BAJO</span>
               <span style='background: #ffebee; padding: 5px 10px; border-radius: 3px; margin-right: 10px;'>CRÍTICO</span>
               <span style='background: #ffebee; padding: 5px 10px; border-radius: 3px;'>AGOTADO</span></p>
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
    header('Content-Disposition: attachment; filename="stock_completo_' . date('Y-m-d') . '.html"');
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Stock Completo - Albus S.A.</title>
        <style>
            @media print {
                body { margin: 0; padding: 15px; font-size: 12px; }
                .no-print { display: none; }
                .header { background: #2c3e50 !important; -webkit-print-color-adjust: exact; }
                th { background: #34495e !important; -webkit-print-color-adjust: exact; }
                .agotado, .critico { background: #ffebee !important; -webkit-print-color-adjust: exact; }
                .bajo { background: #fff3e0 !important; -webkit-print-color-adjust: exact; }
                .optimo { background: #e8f5e8 !important; -webkit-print-color-adjust: exact; }
            }
            body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; background: white; }
            .header { text-align: center; margin-bottom: 25px; background: #2c3e50; color: white; padding: 25px; border-radius: 8px; }
            .header h1 { margin: 0 0 10px 0; font-size: 28px; }
            .header-info { font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            th { background: #34495e; color: white; font-weight: 600; padding: 12px 8px; border: 1px solid #2c3e50; text-align: left; }
            td { padding: 10px 8px; border: 1px solid #e0e0e0; }
            .agotado { background: #ffebee; color: #c62828; }
            .critico { background: #ffebee; color: #c62828; }
            .bajo { background: #fff3e0; color: #ef6c00; }
            .optimo { background: #e8f5e8; color: #2e7d32; }
            .numero { text-align: center; font-weight: 600; }
            .estado { text-align: center; font-weight: 600; padding: 6px 10px; border-radius: 4px; }
            .resumen { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; margin: 25px 0; border-radius: 8px; }
            .instrucciones { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>📦 INVENTARIO COMPLETO - ALBUS S.A.</h1>
            <div class='header-info'>
                <p><strong>Fecha de generación:</strong> " . date('d/m/Y H:i') . " | 
                   <strong>Usuario:</strong> " . $_SESSION['usuario_nombre'] . "</p>
                <p><strong>Departamento:</strong> Producción | <strong>Rol:</strong> Jefe de Producción</p>
            </div>
        </div>
        
        <div class='instrucciones no-print'>
            <h4>💡 INSTRUCCIONES PARA PDF</h4>
            <p>Para guardar como PDF: <strong>Ctrl+P → Seleccionar 'Guardar como PDF' → Imprimir</strong></p>
        </div>";
    
    // Estadísticas
    $total_productos = $productos_data->num_rows;
    $stock_bajo = 0;
    $stock_critico = 0;
    $stock_optimo = 0;
    
    $productos_data->data_seek(0);
    while($producto = $productos_data->fetch_assoc()) {
        $diferencia = $producto['stock'] - $producto['stock_minimo'];
        if ($producto['stock'] == 0) {
            $stock_critico++;
        } elseif ($diferencia < 0) {
            $stock_critico++;
        } elseif ($diferencia < 5) {
            $stock_bajo++;
        } else {
            $stock_optimo++;
        }
    }
    
    echo "<div class='resumen'>
            <h3 style='margin: 0 0 15px 0; text-align: center;'>📊 RESUMEN EJECUTIVO</h3>
            <div style='display: flex; justify-content: space-around; text-align: center;'>
                <div>
                    <div style='font-size: 24px; font-weight: bold;'>{$total_productos}</div>
                    <div>Total Productos</div>
                </div>
                <div>
                    <div style='font-size: 24px; font-weight: bold; color: #2ecc71;'>{$stock_optimo}</div>
                    <div>Óptimos</div>
                </div>
                <div>
                    <div style='font-size: 24px; font-weight: bold; color: #f39c12;'>{$stock_bajo}</div>
                    <div>Bajos</div>
                </div>
                <div>
                    <div style='font-size: 24px; font-weight: bold; color: #e74c3c;'>{$stock_critico}</div>
                    <div>Críticos</div>
                </div>
            </div>
          </div>";
    
    echo "<table>
            <thead>
                <tr>
                    <th width='25%'>PRODUCTO</th>
                    <th width='30%'>DESCRIPCIÓN</th>
                    <th width='12%'>STOCK ACTUAL</th>
                    <th width='12%'>STOCK MÍNIMO</th>
                    <th width='11%'>ESTADO</th>
                    <th width='10%'>DIFERENCIA</th>
                </tr>
            </thead>
            <tbody>";
    
    $productos_data->data_seek(0);
    while($producto = $productos_data->fetch_assoc()) {
        $diferencia = $producto['stock'] - $producto['stock_minimo'];
        
        if ($producto['stock'] == 0) {
            $estado = 'AGOTADO';
            $clase_fila = 'agotado';
            $clase_estado = 'agotado';
        } elseif ($diferencia < 0) {
            $estado = 'CRÍTICO';
            $clase_fila = 'critico';
            $clase_estado = 'critico';
        } elseif ($diferencia < 5) {
            $estado = 'BAJO';
            $clase_fila = 'bajo';
            $clase_estado = 'bajo';
        } else {
            $estado = 'ÓPTIMO';
            $clase_fila = 'optimo';
            $clase_estado = 'optimo';
        }
        
        echo "<tr class='{$clase_fila}'>
                <td><strong>" . htmlspecialchars($producto['nombre']) . "</strong></td>
                <td>" . htmlspecialchars($producto['descripcion']) . "</td>
                <td class='numero'>" . $producto['stock'] . "</td>
                <td class='numero'>" . $producto['stock_minimo'] . "</td>
                <td class='estado {$clase_estado}'>" . $estado . "</td>
                <td class='numero " . ($diferencia >= 0 ? 'optimo' : 'critico') . "'><strong>" . ($diferencia >= 0 ? '+' : '') . $diferencia . "</strong></td>
              </tr>";
    }
    
    echo "</tbody>
        </table>
        
        <div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;'>
            <h4 style='margin-bottom: 15px;'>🎯 INTERPRETACIÓN DE RESULTADOS</h4>
            <div style='display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;'>
                <div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>
                    <strong>✅ ÓPTIMO:</strong> Stock suficiente para operación normal
                </div>
                <div style='background: #fff3e0; padding: 15px; border-radius: 5px;'>
                    <strong>⚠️ BAJO:</strong> Stock cercano al mínimo, considerar reposición
                </div>
                <div style='background: #ffebee; padding: 15px; border-radius: 5px;'>
                    <strong>🚨 CRÍTICO:</strong> Stock por debajo del mínimo, requiere acción inmediata
                </div>
                <div style='background: #ffebee; padding: 15px; border-radius: 5px;'>
                    <strong>❌ AGOTADO:</strong> Sin stock disponible, detiene producción
                </div>
            </div>
        </div>
        
        <div style='margin-top: 25px; text-align: center; color: #7f8c8d; font-size: 11px; border-top: 1px solid #ecf0f1; padding-top: 15px;'>
            <p>📄 Documento generado automáticamente por el Sistema de Gestión de Inventario Albus S.A.</p>
            <p>📍 Fecha de validez: " . date('d/m/Y H:i') . "</p>
        </div>
    </body>
    </html>";
    exit();
}

// Reset para la vista web
$productos_data->data_seek(0);

// Obtener productos químicos para la vista web
$sql_quimicos = "SELECT * FROM productos_quimicos ORDER BY nombre";
$quimicos = $conn->query($sql_quimicos);

// Obtener envases para la vista web
$sql_envases = "SELECT * FROM envases ORDER BY nombre";
$envases = $conn->query($sql_envases);

// Ahora incluimos el header después de procesar todo
include 'header_jefe_produccion.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-boxes"></i> Stock Completo</h2>
            <p class="text-muted mb-0">Consulta de inventario completo</p>
        </div>
        <div class="text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="?exportar_excel=1">
                            <i class="bi bi-file-earmark-excel text-success"></i> Excel
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?exportar_pdf=1">
                            <i class="bi bi-file-earmark-pdf text-danger"></i> PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Pestañas -->
    <ul class="nav nav-tabs mb-4" id="stockTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="productos-tab" data-bs-toggle="tab" 
                    data-bs-target="#productos" type="button" role="tab">
                <i class="bi bi-box-seam"></i> Productos (<?php echo $productos_data->num_rows; ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="quimicos-tab" data-bs-toggle="tab" 
                    data-bs-target="#quimicos" type="button" role="tab">
                <i class="bi bi-droplet"></i> Químicos (<?php echo $quimicos->num_rows; ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="envases-tab" data-bs-toggle="tab" 
                    data-bs-target="#envases" type="button" role="tab">
                <i class="bi bi-tags"></i> Envases (<?php echo $envases->num_rows; ?>)
            </button>
        </li>
    </ul>

    <!-- Contenido de Pestañas -->
    <div class="tab-content" id="stockTabsContent">
        <!-- Pestaña Productos -->
        <div class="tab-pane fade show active" id="productos" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-box-seam"></i> Productos de Producción
                    </h5>
                    <span class="badge bg-light text-dark"><?php echo $productos_data->num_rows; ?> productos</span>
                </div>
                <div class="card-body">
                    <?php if ($productos_data->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Descripción</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                        <th>Estado</th>
                                        <th>Diferencia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($producto = $productos_data->fetch_assoc()): 
                                        $diferencia = $producto['stock'] - $producto['stock_minimo'];
                                        $estado = '';
                                        $badge_class = '';
                                        
                                        if ($producto['stock'] == 0) {
                                            $estado = 'Agotado';
                                            $badge_class = 'bg-danger';
                                        } elseif ($diferencia < 0) {
                                            $estado = 'Crítico';
                                            $badge_class = 'bg-danger';
                                        } elseif ($diferencia < 5) {
                                            $estado = 'Bajo';
                                            $badge_class = 'bg-warning';
                                        } else {
                                            $estado = 'Óptimo';
                                            $badge_class = 'bg-success';
                                        }
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                            <td class="fw-bold <?php echo $producto['stock'] == 0 ? 'text-danger' : ''; ?>">
                                                <?php echo $producto['stock']; ?>
                                            </td>
                                            <td class="text-info fw-bold"><?php echo $producto['stock_minimo']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $estado; ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold <?php echo $diferencia < 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $diferencia >= 0 ? "+$diferencia" : $diferencia; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="gestion_entradas.php" class="btn btn-outline-success" 
                                                       title="Agregar entrada">
                                                        <i class="bi bi-plus"></i>
                                                    </a>
                                                    <a href="gestion_salidas.php" class="btn btn-outline-warning" 
                                                       title="Registrar salida">
                                                        <i class="bi bi-dash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay productos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pestaña Químicos -->
        <div class="tab-pane fade" id="quimicos" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-droplet"></i> Productos Químicos
                    </h5>
                    <span class="badge bg-light text-dark"><?php echo $quimicos->num_rows; ?> químicos</span>
                </div>
                <div class="card-body">
                    <?php if ($quimicos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto Químico</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                        <th>Estado</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($quimico = $quimicos->fetch_assoc()): 
                                        $diferencia = $quimico['stock'] - $quimico['stock_minimo'];
                                        $estado = $diferencia >= 0 ? 'Óptimo' : 'Crítico';
                                        $badge_class = $diferencia >= 0 ? 'bg-success' : 'bg-danger';
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($quimico['nombre']); ?></td>
                                            <td class="fw-bold <?php echo $quimico['stock'] == 0 ? 'text-danger' : ''; ?>">
                                                <?php echo $quimico['stock']; ?>
                                            </td>
                                            <td class="text-info fw-bold"><?php echo $quimico['stock_minimo']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $estado; ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold <?php echo $diferencia < 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $diferencia >= 0 ? "+$diferencia" : $diferencia; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay productos químicos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pestaña Envases -->
        <div class="tab-pane fade" id="envases" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tags"></i> Envases y Empaques
                    </h5>
                    <span class="badge bg-light text-dark"><?php echo $envases->num_rows; ?> envases</span>
                </div>
                <div class="card-body">
                    <?php if ($envases->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Envase/Embalaje</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                        <th>Estado</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($envase = $envases->fetch_assoc()): 
                                        $diferencia = $envase['stock'] - $envase['stock_minimo'];
                                        $estado = $diferencia >= 0 ? 'Óptimo' : 'Crítico';
                                        $badge_class = $diferencia >= 0 ? 'bg-success' : 'bg-danger';
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($envase['nombre']); ?></td>
                                            <td class="fw-bold <?php echo $envase['stock'] == 0 ? 'text-danger' : ''; ?>">
                                                <?php echo $envase['stock']; ?>
                                            </td>
                                            <td class="text-info fw-bold"><?php echo $envase['stock_minimo']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $estado; ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold <?php echo $diferencia < 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $diferencia >= 0 ? "+$diferencia" : $diferencia; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay envases registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php';
$conn->close();
?>