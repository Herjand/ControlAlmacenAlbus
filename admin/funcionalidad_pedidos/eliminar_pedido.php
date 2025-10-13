<?php
session_start();
require_once '../../connect.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_pedido = $_GET['id'];
    
    try {
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
            header("Location: ../pedidos_admin.php?success=3");
        } else {
            throw new Exception("Error al eliminar pedido");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../pedidos_admin.php?error=3");
    }
    
    $stmt_detalle->close();
    $stmt_pedido->close();
} else {
    header("Location: ../pedidos_admin.php?error=1");
}

$conn->close();
exit();
?>