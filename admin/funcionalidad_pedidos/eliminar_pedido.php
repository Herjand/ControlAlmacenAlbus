<?php
session_start();
require_once '../../connect.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_pedido = intval($_GET['id']);
    
    if ($id_pedido <= 0) {
        header("Location: ../pedidos_admin.php?error=3");
        exit();
    }

    try {
        // Obtener información del pedido para el log
        $sql_info = "SELECT id_pedido, empresa_cliente FROM pedidos WHERE id_pedido = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $id_pedido);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        
        if ($result_info->num_rows === 0) {
            header("Location: ../pedidos_admin.php?error=3");
            exit();
        }
        
        $pedido = $result_info->fetch_assoc();
        $info_pedido = "Pedido #" . $pedido['id_pedido'] . " - " . $pedido['empresa_cliente'];

        // Iniciar transacción
        $conn->begin_transaction();

        // 1. Eliminar los productos del pedido
        $sql_detalle = "DELETE FROM detalle_pedidos WHERE id_pedido = ?";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param("i", $id_pedido);
        $stmt_detalle->execute();

        // 2. Eliminar el pedido
        $sql_pedido = "DELETE FROM pedidos WHERE id_pedido = ?";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("i", $id_pedido);
        
        if ($stmt_pedido->execute()) {
            $conn->commit();
            
            // Registrar en logs
            $accion = "Eliminó " . $info_pedido;
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Pedidos')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
            $stmt_log->execute();
            
            header("Location: ../pedidos_admin.php?success=3");
        } else {
            throw new Exception("Error al eliminar pedido");
        }
        
        $stmt_detalle->close();
        $stmt_pedido->close();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error al eliminar pedido: " . $e->getMessage());
        header("Location: ../pedidos_admin.php?error=3");
    }
} else {
    header("Location: ../pedidos_admin.php?error=1");
}

$conn->close();
exit();
?>