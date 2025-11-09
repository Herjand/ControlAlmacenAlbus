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
    die("Error: No se pudo encontrar connect.php");
}

// Obtener parámetros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$usuario = $_GET['usuario'] ?? 'todos';
$modulo = $_GET['modulo'] ?? 'todos';

// Headers para Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="auditoria_sistema_' . date('Ymd_His') . '.xls"');
header('Cache-Control: max-age=0');

// BOM para UTF-8
echo "\xEF\xBB\xBF";

// Consulta de auditoría
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
        LIMIT 1000";

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

// Obtener nombre de usuario para el reporte
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Cabecera del reporte
echo "<table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr><td colspan='7' style='background-color: #2c3e50; color: white; font-size: 16px; font-weight: bold; text-align: center; padding: 10px;'>SISTEMA DE GESTIÓN DE ALMACÉN - ALBUS</td></tr>";
echo "<tr><td colspan='7' style='background-color: #3498db; color: white; font-size: 14px; font-weight: bold; text-align: center; padding: 8px;'>REPORTE DE AUDITORÍA DEL SISTEMA</td></tr>";
echo "<tr><td colspan='7' style='padding: 6px;'><strong>Generado el:</strong> " . date('d/m/Y H:i:s') . " | <strong>Usuario:</strong> " . $usuario_nombre . "</td></tr>";
echo "<tr><td colspan='7' style='padding: 6px;'><strong>Periodo:</strong> " . date('d/m/Y', strtotime($fecha_inicio)) . " - " . date('d/m/Y', strtotime($fecha_fin)) . "</td></tr>";

if ($usuario != 'todos' || $modulo != 'todos') {
    echo "<tr><td colspan='7' style='padding: 6px;'>";
    if ($usuario != 'todos') echo "<strong>Usuario filtrado</strong> | ";
    if ($modulo != 'todos') echo "<strong>Módulo filtrado: " . htmlspecialchars($modulo) . "</strong>";
    echo "</td></tr>";
}

echo "<tr><td colspan='7' style='padding: 10px;'>&nbsp;</td></tr>";

// Estadísticas
$total_registros = $result->num_rows;
echo "<tr style='background-color: #ecf0f1;'>";
echo "<td colspan='3' style='border: 1px solid #ddd; padding: 6px; font-weight: bold;'>Total de Registros:</td>";
echo "<td colspan='4' style='border: 1px solid #ddd; padding: 6px; font-weight: bold;'>" . $total_registros . "</td>";
echo "</tr>";
echo "<tr><td colspan='7' style='padding: 10px;'>&nbsp;</td></tr>";

if ($total_registros > 0) {
    // Cabecera de la tabla
    echo "<tr style='background-color: #2980b9; color: white; font-weight: bold;'>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Fecha</td>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Hora</td>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Usuario</td>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Rol</td>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Módulo</td>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Acción</td>";
    echo "<td style='border: 1px solid #2980b9; padding: 8px;'>Detalles</td>";
    echo "</tr>";
    
    // Datos
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . date('H:i:s', strtotime($row['fecha'])) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . $row['usuario_rol'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . ($row['modulo'] ? htmlspecialchars($row['modulo']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . htmlspecialchars($row['accion']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 6px;'>" . htmlspecialchars($row['detalles'] ?? 'Sin detalles') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr>";
    echo "<td colspan='7' style='text-align: center; background-color: #f39c12; color: white; font-weight: bold; padding: 12px; border: 1px solid #f39c12;'>NO SE ENCONTRARON REGISTROS DE AUDITORÍA CON LOS FILTROS SELECCIONADOS</td>";
    echo "</tr>";
}

echo "</table>";

$stmt->close();
$conn->close();
?>