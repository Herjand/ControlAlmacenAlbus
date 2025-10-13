<?php
session_start();
require_once '../../connect.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];
    
    // Verificar si el cliente tiene pedidos relacionados
    $sql_verificar = "SELECT COUNT(*) as total FROM pedidos WHERE empresa_cliente IN (SELECT empresa FROM clientes WHERE id_cliente = ?)";
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
        header("Location: ../clientes_admin.php?success=3");
    } else {
        header("Location: ../clientes_admin.php?error=1");
    }
    
    $stmt->close();
} else {
    header("Location: ../clientes_admin.php?error=1");
}

$conn->close();
exit();
?>