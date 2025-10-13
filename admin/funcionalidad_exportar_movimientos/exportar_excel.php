<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../../connect.php';

// Obtener parámetros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo_movimiento = $_GET['tipo_movimiento'] ?? 'todos';

// Headers para Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporte_movimientos_' . date('Ymd_His') . '.xls"');
header('Cache-Control: max-age=0');

// BOM para UTF-8
echo "\xEF\xBB\xBF";

// Consulta de movimientos
$sql_where = "WHERE m.fecha BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];

if ($tipo_movimiento == 'entrada') {
    $sql_where .= " AND m.tipo = 'Entrada'";
} elseif ($tipo_movimiento == 'salida') {
    $sql_where .= " AND m.tipo = 'Salida'";
}

$sql = "SELECT m.*, p.nombre as producto_nombre, p.unidad_medida, p.categoria, u.nombre as responsable_nombre 
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

// Cabecera del reporte
echo "<table border='1'>";
echo "<tr><td colspan='9' style='background-color: #2c3e50; color: white; font-size: 16px; font-weight: bold; text-align: center; height: 40px;'>SISTEMA DE GESTIÓN DE ALMACÉN - ALBUS</td></tr>";
echo "<tr><td colspan='9' style='background-color: #3498db; color: white; font-size: 14px; font-weight: bold; text-align: center; height: 35px;'>REPORTE DE MOVIMIENTOS DE INVENTARIO</td></tr>";
echo "<tr><td colspan='9'><strong>Generado el:</strong> " . date('d/m/Y H:i:s') . " | <strong>Usuario:</strong> " . $_SESSION['usuario_nombre'] . "</td></tr>";
echo "<tr><td colspan='9'><strong>Periodo:</strong> " . date('d/m/Y', strtotime($fecha_inicio)) . " - " . date('d/m/Y', strtotime($fecha_fin)) . "</td></tr>";

if ($tipo_movimiento != 'todos') {
    echo "<tr><td colspan='9'><strong>Tipo:</strong> " . ucfirst($tipo_movimiento) . "s</td></tr>";
}

echo "<tr><td colspan='9'></td></tr>";

// Estadísticas
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

echo "<tr style='background-color: #ecf0f1; font-weight: bold;'>";
echo "<td colspan='3'>Total Movimientos:</td><td>" . $total_movimientos . "</td>";
echo "<td colspan='2'>Total Entradas:</td><td colspan='3'>" . $total_entradas . " (" . $total_cantidad_entradas . " items)</td>";
echo "</tr>";
echo "<tr style='background-color: #ecf0f1; font-weight: bold;'>";
echo "<td colspan='3'>Total Salidas:</td><td>" . $total_salidas . "</td>";
echo "<td colspan='2'>Items Salidas:</td><td colspan='3'>" . $total_cantidad_salidas . "</td>";
echo "</tr>";
echo "<tr><td colspan='9'></td></tr>";

if ($total_movimientos > 0) {
    // Cabecera de la tabla
    echo "<tr style='background-color: #2980b9; color: white; font-weight: bold; height: 30px;'>";
    echo "<td>Fecha</td>";
    echo "<td>Hora</td>";
    echo "<td>Tipo</td>";
    echo "<td>Producto</td>";
    echo "<td>Categoría</td>";
    echo "<td>Cantidad</td>";
    echo "<td>Unidad</td>";
    echo "<td>Responsable</td>";
    echo "<td>Motivo</td>";
    echo "</tr>";
    
    // Datos
    $result->data_seek(0);
    while($row = $result->fetch_assoc()) {
        $color = $row['tipo'] == 'Entrada' ? '#d4edda' : '#f8d7da';
        echo "<tr style='background-color: " . $color . ";'>";
        echo "<td>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>";
        echo "<td>" . date('H:i:s', strtotime($row['fecha'])) . "</td>";
        echo "<td>" . $row['tipo'] . "</td>";
        echo "<td>" . $row['producto_nombre'] . "</td>";
        echo "<td>" . $row['categoria'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['cantidad'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['unidad_medida'] . "</td>";
        echo "<td>" . $row['responsable_nombre'] . "</td>";
        echo "<td>" . $row['motivo'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' style='text-align: center; background-color: #f39c12; color: white; font-weight: bold; height: 40px;'>NO SE ENCONTRARON MOVIMIENTOS EN EL PERÍODO SELECCIONADO</td></tr>";
}

echo "</table>";

$stmt->close();
$conn->close();
?>