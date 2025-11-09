<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

if (isset($_GET['id_pedido'])) {
    $id_pedido = $_GET['id_pedido'];
    $usuario_id = $_SESSION['usuario_id'];

    // Verificar que el pedido existe y está pendiente
    $sql_verificar = "SELECT estado FROM pedidos WHERE id_pedido = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("i", $id_pedido);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();

    if ($result_verificar->num_rows === 0) {
        header("Location: ../preparar_pedidos.php?error=3");
        exit();
    }

    $pedido = $result_verificar->fetch_assoc();
    
    if ($pedido['estado'] != 'Pendiente') {
        header("Location: ../preparar_pedidos.php?error=1");
        exit();
    }

    // Actualizar estado del pedido a "En Preparación"
    $sql_actualizar = "UPDATE pedidos SET estado = 'En Preparación', updated_at = CURRENT_TIMESTAMP WHERE id_pedido = ?";
    $stmt_actualizar = $conn->prepare($sql_actualizar);
    $stmt_actualizar->bind_param("i", $id_pedido);

    if ($stmt_actualizar->execute()) {
        // Registrar en logs
        $accion = "Pedido marcado como 'En Preparación'";
        $detalles = "ID Pedido: " . $id_pedido;
        
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo, detalles) VALUES (?, ?, 'Pedidos', ?)";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("iss", $usuario_id, $accion, $detalles);
        $stmt_log->execute();

        header("Location: ../preparar_pedidos.php?success=1");
        exit();
    } else {
        header("Location: ../preparar_pedidos.php?error=1");
        exit();
    }
} else {
    header("Location: ../preparar_pedidos.php");
    exit();
}
?>