<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../connect.php';

// Obtener parámetros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Headers para Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="resumen_movimientos_' . date('Ymd_His') . '.xls"');
header('Cache-Control: max-age=0');

echo "\xEF\xBB\xBF";

// Consulta para resumen por producto
$sql = "SELECT 
            p.nombre as producto,
            p.categoria,
            p.unidad_medida,
            COALESCE(entradas.cantidad, 0) as total_entradas,
            COALESCE(salidas.cantidad, 0) as total_salidas,
            (COALESCE(entradas.cantidad, 0) - COALESCE(salidas.cantidad, 0)) as neto,
            p.stock as stock_actual
        FROM productos p
        LEFT JOIN (
            SELECT id_producto, SUM(cantidad) as cantidad
            FROM entradas
            WHERE fecha BETWEEN ? AND ?
            GROUP BY id_producto
        ) entradas ON p.id_producto = entradas.id_producto
        LEFT JOIN (
            SELECT id_producto, SUM(cantidad) as cantidad
            FROM salidas
            WHERE fecha BETWEEN ? AND ?
            GROUP BY id_producto
        ) salidas ON p.id_producto = salidas.id_producto
        WHERE COALESCE(entradas.cantidad, 0) > 0 OR COALESCE(salidas.cantidad, 0) > 0
        ORDER BY p.categoria, p.nombre";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin);
$stmt->execute();
$result = $stmt->get_result();

// Cabecera del reporte
echo "<table border='1'>";
echo "<tr><td colspan='8' style='background-color: #2c3e50; color: white; font-size: 16px; font-weight: bold; text-align: center; height: 40px;'>SISTEMA DE GESTIÓN DE ALMACÉN - ALBUS</td></tr>";
echo "<tr><td colspan='8' style='background-color: #27ae60; color: white; font-size: 14px; font-weight: bold; text-align: center; height: 35px;'>RESUMEN ESTADÍSTICO DE MOVIMIENTOS</td></tr>";
echo "<tr><td colspan='8'><strong>Generado el:</strong> " . date('d/m/Y H:i:s') . " | <strong>Usuario:</strong> " . $_SESSION['usuario_nombre'] . "</td></tr>";
echo "<tr><td colspan='8'><strong>Periodo:</strong> " . date('d/m/Y', strtotime($fecha_inicio)) . " - " . date('d/m/Y', strtotime($fecha_fin)) . "</td></tr>";
echo "<tr><td colspan='8'></td></tr>";

// Cabecera de la tabla
echo "<tr style='background-color: #27ae60; color: white; font-weight: bold; height: 30px;'>";
echo "<td>Producto</td>";
echo "<td>Categoría</td>";
echo "<td>Unidad</td>";
echo "<td>Entradas</td>";
echo "<td>Salidas</td>";
echo "<td>Neto</td>";
echo "<td>Stock Actual</td>";
echo "<td>Variación</td>";
echo "</tr>";

// Datos
$total_entradas = 0;
$total_salidas = 0;

while($row = $result->fetch_assoc()) {
    $total_entradas += $row['total_entradas'];
    $total_salidas += $row['total_salidas'];
    $variacion = $row['neto'] > 0 ? 'POSITIVA' : ($row['neto'] < 0 ? 'NEGATIVA' : 'NEUTRA');
    $color = $row['neto'] > 0 ? '#d4edda' : ($row['neto'] < 0 ? '#f8d7da' : '#fff3cd');
    
    echo "<tr style='background-color: " . $color . ";'>";
    echo "<td>" . $row['producto'] . "</td>";
    echo "<td>" . $row['categoria'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['unidad_medida'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['total_entradas'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['total_salidas'] . "</td>";
    echo "<td style='text-align: center; font-weight: bold;'>" . $row['neto'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['stock_actual'] . "</td>";
    echo "<td style='text-align: center;'>" . $variacion . "</td>";
    echo "</tr>";
}

// Totales
echo "<tr style='background-color: #34495e; color: white; font-weight: bold;'>";
echo "<td colspan='3' style='text-align: center;'>TOTALES</td>";
echo "<td style='text-align: center;'>" . $total_entradas . "</td>";
echo "<td style='text-align: center;'>" . $total_salidas . "</td>";
echo "<td style='text-align: center;'>" . ($total_entradas - $total_salidas) . "</td>";
echo "<td colspan='2'></td>";
echo "</tr>";

echo "</table>";

$stmt->close();
$conn->close();
?>