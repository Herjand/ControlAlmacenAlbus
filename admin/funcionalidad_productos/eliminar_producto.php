<?php
session_start();
include '../../connect.php';

if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    // Primero obtenemos el nombre del producto para el log
    $sql_info = "SELECT nombre FROM productos WHERE id_producto = ?";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $id_producto);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();
    
    if ($result_info->num_rows === 0) {
        $stmt_info->close();
        header("Location: ../productos_admin.php?error=3");
        exit();
    }
    
    $producto = $result_info->fetch_assoc();
    $stmt_info->close();
    
    // ELIMINAR MOVIMIENTOS RELACIONADOS
    try {
        // 1. Eliminar de detalle_pedidos (NO pedidos_detalle)
        $sql_delete_pedidos = "DELETE FROM detalle_pedidos WHERE id_producto = ?";
        $stmt_pedidos = $conn->prepare($sql_delete_pedidos);
        $stmt_pedidos->bind_param("i", $id_producto);
        $stmt_pedidos->execute();
        $stmt_pedidos->close();
        
        // 2. Eliminar de entradas
        $sql_delete_entradas = "DELETE FROM entradas WHERE id_producto = ?";
        $stmt_entradas = $conn->prepare($sql_delete_entradas);
        $stmt_entradas->bind_param("i", $id_producto);
        $stmt_entradas->execute();
        $stmt_entradas->close();
        
        // 3. Eliminar de salidas 
        $sql_delete_salidas = "DELETE FROM salidas WHERE id_producto = ?";
        $stmt_salidas = $conn->prepare($sql_delete_salidas);
        $stmt_salidas->bind_param("i", $id_producto);
        $stmt_salidas->execute();
        $stmt_salidas->close();
        
        // 4. Finalmente eliminar el producto
        $sql_delete_producto = "DELETE FROM productos WHERE id_producto = ?";
        $stmt_producto = $conn->prepare($sql_delete_producto);
        $stmt_producto->bind_param("i", $id_producto);
        
        if ($stmt_producto->execute()) {
            // Registrar en logs
            $id_usuario = $_SESSION['usuario_id'];
            $accion = "Eliminó producto y todos sus movimientos: " . $producto['nombre'];
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Productos')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $id_usuario, $accion);
            $stmt_log->execute();
            $stmt_log->close();
            
            $stmt_producto->close();
            header("Location: ../productos_admin.php?success=3");
            exit();
        } else {
            $stmt_producto->close();
            header("Location: ../productos_admin.php?error=3");
            exit();
        }
        
    } catch (Exception $e) {
        // Si hay error, intentar eliminar solo el producto (sin movimientos)
        try {
            $sql_delete_directo = "DELETE FROM productos WHERE id_producto = ?";
            $stmt_directo = $conn->prepare($sql_delete_directo);
            $stmt_directo->bind_param("i", $id_producto);
            
            if ($stmt_directo->execute()) {
                $stmt_directo->close();
                header("Location: ../productos_admin.php?success=3");
                exit();
            } else {
                $stmt_directo->close();
                header("Location: ../productos_admin.php?error=3");
                exit();
            }
        } catch (Exception $e2) {
            header("Location: ../productos_admin.php?error=3");
            exit();
        }
    }
} else {
    header("Location: ../productos_admin.php");
    exit();
}

$conn->close();
?>