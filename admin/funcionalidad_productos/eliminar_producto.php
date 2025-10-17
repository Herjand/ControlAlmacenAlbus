<?php
session_start();
include '../../connect.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_producto = intval($_GET['id']);
    
    // Verificar si el producto existe
    $sql_verificar = "SELECT nombre FROM productos WHERE id_producto = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("i", $id_producto);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        $stmt_verificar->close();
        header("Location: ../productos_admin.php?error=3");
        exit();
    }
    
    $producto = $result_verificar->fetch_assoc();
    $stmt_verificar->close();
    
    // Verificar si hay movimientos relacionados
    $sql_movimientos = "SELECT 
        (SELECT COUNT(*) FROM entradas WHERE id_producto = ?) as total_entradas,
        (SELECT COUNT(*) FROM salidas WHERE id_producto = ?) as total_salidas,
        (SELECT COUNT(*) FROM detalle_pedidos WHERE id_producto = ?) as total_pedidos";
    
    $stmt_movimientos = $conn->prepare($sql_movimientos);
    $stmt_movimientos->bind_param("iii", $id_producto, $id_producto, $id_producto);
    $stmt_movimientos->execute();
    $result_movimientos = $stmt_movimientos->get_result();
    $movimientos = $result_movimientos->fetch_assoc();
    $stmt_movimientos->close();
    
    $total_movimientos = $movimientos['total_entradas'] + $movimientos['total_salidas'] + $movimientos['total_pedidos'];
    
    if ($total_movimientos > 0) {
        header("Location: ../productos_admin.php?error=4");
        exit();
    }
    
    // Eliminar el producto
    $sql_eliminar = "DELETE FROM productos WHERE id_producto = ?";
    $stmt_eliminar = $conn->prepare($sql_eliminar);
    $stmt_eliminar->bind_param("i", $id_producto);
    
    if ($stmt_eliminar->execute()) {
        // Registrar en logs
        $id_usuario = $_SESSION['usuario_id'];
        $accion = "Eliminó producto: " . $producto['nombre'];
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Productos')";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("is", $id_usuario, $accion);
        $stmt_log->execute();
        $stmt_log->close();
        
        $stmt_eliminar->close();
        header("Location: ../productos_admin.php?success=3");
        exit();
    } else {
        $stmt_eliminar->close();
        header("Location: ../productos_admin.php?error=3");
        exit();
    }
} else {
    header("Location: ../productos_admin.php");
    exit();
}

$conn->close();
?>