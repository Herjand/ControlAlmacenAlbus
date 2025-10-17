<?php
session_start();
require_once '../../connect.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_cliente = intval($_GET['id']);
    
    if ($id_cliente <= 0) {
        header("Location: ../clientes_admin.php?error=3");
        exit();
    }

    try {
        // Obtener información del cliente para el log
        $sql_info = "SELECT empresa FROM clientes WHERE id_cliente = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $id_cliente);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        
        if ($result_info->num_rows === 0) {
            header("Location: ../clientes_admin.php?error=3");
            exit();
        }
        
        $cliente = $result_info->fetch_assoc();
        $nombre_empresa = $cliente['empresa'];

        // Verificar si el cliente tiene pedidos relacionados
        $sql_verificar = "SELECT COUNT(*) as total FROM pedidos WHERE id_cliente = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("i", $id_cliente);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->get_result()->fetch_assoc();
        
        if ($resultado['total'] > 0) {
            header("Location: ../clientes_admin.php?error=3");
            exit();
        }
        
        // Eliminar cliente
        $sql = "DELETE FROM clientes WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        
        if ($stmt->execute()) {
            // Registrar en logs
            $accion = "Eliminó cliente: " . $nombre_empresa;
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Clientes')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
            $stmt_log->execute();
            
            header("Location: ../clientes_admin.php?success=3");
        } else {
            header("Location: ../clientes_admin.php?error=1");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error al eliminar cliente: " . $e->getMessage());
        header("Location: ../clientes_admin.php?error=1");
    }
} else {
    header("Location: ../clientes_admin.php?error=1");
}

$conn->close();
exit();
?>