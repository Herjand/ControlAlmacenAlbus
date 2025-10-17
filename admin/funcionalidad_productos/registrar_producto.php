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
    $nombre = trim($_POST['nombre']);
    
    // Si se seleccionó "otros", usar el nombre personalizado
    if ($nombre === 'otros' && isset($_POST['nombre_personalizado'])) {
        $nombre = trim($_POST['nombre_personalizado']);
    }
    
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
    
    // Insertar el producto
    $sql = "INSERT INTO productos (nombre, descripcion, presentacion, stock, stock_minimo, tamaño_peso, cantidad_unidad, tipo_especifico) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiisss", $nombre, $descripcion, $presentacion, $stock, $stock_minimo, $tamaño_peso, $cantidad_unidad, $tipo_especifico);
    
    if ($stmt->execute()) {
        // Verificar si el usuario existe antes de insertar en logs
        $id_usuario = $_SESSION['usuario_id'];
        $sql_verificar_usuario = "SELECT id_usuario FROM usuarios WHERE id_usuario = ?";
        $stmt_verificar = $conn->prepare($sql_verificar_usuario);
        $stmt_verificar->bind_param("i", $id_usuario);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows > 0) {
            // El usuario existe, podemos insertar en logs
            $accion = "Registró nuevo producto: " . $nombre;
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Productos')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $id_usuario, $accion);
            $stmt_log->execute();
            $stmt_log->close();
        }
        
        $stmt_verificar->close();
        $stmt->close();
        header("Location: ../productos_admin.php?success=1");
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