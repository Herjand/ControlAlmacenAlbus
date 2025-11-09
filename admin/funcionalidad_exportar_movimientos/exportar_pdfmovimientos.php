<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

// SISTEMA DE BÚSQUEDA AUTOMÁTICA DE ARCHIVOS
function findFile($filename, $startDir = __DIR__, $maxDepth = 3) {
    $currentDir = $startDir;
    
    // Buscar hacia arriba
    for ($i = 0; $i < $maxDepth; $i++) {
        $path = $currentDir . '/' . $filename;
        if (file_exists($path)) {
            return $path;
        }
        $currentDir = dirname($currentDir);
    }
    
    // Buscar recursivamente hacia abajo
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($startDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }
    
    return null;
}

// Buscar connect.php
$connect_path = findFile('connect.php');
if ($connect_path) {
    require_once $connect_path;
} else {
    // Mostrar posibles ubicaciones
    die("Error: No se pudo encontrar connect.php. Buscando en:<br>" .
        "- " . __DIR__ . "/../connect.php<br>" .
        "- " . __DIR__ . "/../../connect.php<br>" .
        "- " . __DIR__ . "/../../../connect.php<br>" .
        "Verifique la ubicación del archivo.");
}

// Buscar TCPDF
$tcpdf_paths = [
    '../tcpdf/TCPDF-main/tcpdf.php',
    '../../tcpdf/TCPDF-main/tcpdf.php',
    '../../../tcpdf/TCPDF-main/tcpdf.php',
    'tcpdf/TCPDF-main/tcpdf.php',
];

$tcpdf_loaded = false;
foreach ($tcpdf_paths as $path) {
    $full_path = __DIR__ . '/' . $path;
    if (file_exists($full_path)) {
        require_once $full_path;
        $tcpdf_loaded = true;
        break;
    }
}

if (!$tcpdf_loaded) {
    $tcpdf_path = findFile('tcpdf.php');
    if ($tcpdf_path) {
        require_once $tcpdf_path;
        $tcpdf_loaded = true;
    } else {
        die("Error: No se pudo encontrar TCPDF. Verifique que esté en tcpdf/TCPDF-main/tcpdf.php");
    }
}

// Obtener parámetros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo_movimiento = $_GET['tipo_movimiento'] ?? 'todos';

// Crear PDF
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema de Almacén Albus');
$pdf->SetAuthor('Sistema Albus');
$pdf->SetTitle('Reporte de Movimientos');
$pdf->SetSubject('Reporte de Movimientos de Inventario');

// Configuración de márgenes
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar página
$pdf->AddPage();

// Cabecera del reporte
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Sistema de Gestión de Almacén - ALBUS', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Reporte de Movimientos de Inventario', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Cell(0, 5, 'Usuario: ' . $_SESSION['usuario_nombre'], 0, 1, 'C');
$pdf->Cell(0, 5, 'Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'C');

if ($tipo_movimiento != 'todos') {
    $pdf->Cell(0, 5, 'Tipo: ' . ucfirst($tipo_movimiento) . 's', 0, 1, 'C');
}

$pdf->Ln(10);

// Consulta de movimientos - SIN unidad_medida
$sql_where = "WHERE m.fecha BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];

if ($tipo_movimiento == 'entrada') {
    $sql_where .= " AND m.tipo = 'Entrada'";
} elseif ($tipo_movimiento == 'salida') {
    $sql_where .= " AND m.tipo = 'Salida'";
}

// CONSULTA CORREGIDA - SIN p.unidad_medida
$sql = "SELECT m.*, p.nombre as producto_nombre, u.nombre as responsable_nombre 
        FROM (
            SELECT 'Entrada' as tipo, id_entrada as id, id_producto, cantidad, fecha, usuario_responsable, motivo, observaciones
            FROM entradas
            UNION ALL
            SELECT 'Salida' as tipo, id_salida as id, id_producto, cantidad, fecha, usuario_responsable, motivo, observaciones
            FROM salidas
        ) as m
        JOIN productos p ON m.id_producto = p.id_producto
        JOIN usuarios u ON m.usuario_responsable = u.id_usuario
        $sql_where
        ORDER BY m.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Estadísticas
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Resumen Estadístico', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$total_movimientos = $result->num_rows;
$total_entradas = 0;
$total_salidas = 0;
$total_cantidad_entradas = 0;
$total_cantidad_salidas = 0;

while($row = $result->fetch_assoc()) {
    if($row['tipo'] == 'Entrada') {
        $total_entradas++;
        $total_cantidad_entradas += $row['cantidad'];
    } else {
        $total_salidas++;
        $total_cantidad_salidas += $row['cantidad'];
    }
}

$pdf->Cell(0, 6, 'Total Movimientos: ' . $total_movimientos, 0, 1);
$pdf->Cell(0, 6, 'Entradas: ' . $total_entradas . ' (' . $total_cantidad_entradas . ' items)', 0, 1);
$pdf->Cell(0, 6, 'Salidas: ' . $total_salidas . ' (' . $total_cantidad_salidas . ' items)', 0, 1);
$pdf->Ln(10);

if ($total_movimientos > 0) {
    // Tabla de movimientos - SIN columna "Unidad"
    $pdf->SetFont('helvetica', 'B', 9);
    $header = array('Fecha/Hora', 'Tipo', 'Producto', 'Cantidad', 'Responsable', 'Motivo');
    
    // Ajustar anchos de columnas (sin la columna Unidad)
    $w = array(25, 15, 60, 15, 35, 50);
    
    // Cabecera de la tabla
    for($i = 0; $i < count($header); $i++) {
        $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
    }
    $pdf->Ln();
    
    // Datos de la tabla
    $pdf->SetFont('helvetica', '', 8);
    $result->data_seek(0);
    
    while($row = $result->fetch_assoc()) {
        if($pdf->GetY() > 180) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 9);
            for($i = 0; $i < count($header); $i++) {
                $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('helvetica', '', 8);
        }
        
        $pdf->Cell($w[0], 6, date('d/m/Y H:i', strtotime($row['fecha'])), 1);
        $pdf->Cell($w[1], 6, $row['tipo'], 1);
        $pdf->Cell($w[2], 6, substr($row['producto_nombre'], 0, 30), 1);
        $pdf->Cell($w[3], 6, $row['cantidad'], 1, 0, 'C');
        $pdf->Cell($w[4], 6, substr($row['responsable_nombre'], 0, 20), 1);
        $pdf->Cell($w[5], 6, substr($row['motivo'], 0, 30), 1);
        $pdf->Ln();
    }
} else {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 10, 'No se encontraron movimientos en el período seleccionado.', 0, 1, 'C');
}

// Salida del PDF
$pdf->Output('reporte_movimientos_' . date('Ymd_His') . '.pdf', 'D');

$stmt->close();
$conn->close();
?>