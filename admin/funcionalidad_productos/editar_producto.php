<?php
session_start();
include '../../connect.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id_producto = intval($_POST['id_producto']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $presentacion = trim($_POST['presentacion']);
    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $tamaño_peso = trim($_POST['tamaño_peso']);
    $cantidad_unidad = trim($_POST['cantidad_unidad']);
    $tipo_especifico = trim($_POST['tipo_especifico']);
    
    // Validar campos requeridos
    if (empty($nombre) || empty($presentacion)) {
        header("Location: ../productos_admin.php?error=2");
        exit();
    }
    
    // Actualizar el producto
    $sql = "UPDATE productos SET 
            nombre = ?, 
            descripcion = ?, 
            presentacion = ?, 
            stock = ?, 
            stock_minimo = ?, 
            tamaño_peso = ?, 
            cantidad_unidad = ?, 
            tipo_especifico = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id_producto = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiissssi", $nombre, $descripcion, $presentacion, $stock, $stock_minimo, $tamaño_peso, $cantidad_unidad, $tipo_especifico, $id_producto);
    
    if ($stmt->execute()) {
        // Registrar en logs
        $id_usuario = $_SESSION['usuario_id'];
        $accion = "Actualizó producto ID: " . $id_producto . " - " . $nombre;
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Productos')";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("is", $id_usuario, $accion);
        $stmt_log->execute();
        $stmt_log->close();
        
        $stmt->close();
        header("Location: ../productos_admin.php?success=2");
        exit();
    } else {
        $stmt->close();
        header("Location: ../productos_admin.php?error=1");
        exit();
    }
} else {
    header("Location: ../productos_admin.php");
    exit();
}

$conn->close();
?>