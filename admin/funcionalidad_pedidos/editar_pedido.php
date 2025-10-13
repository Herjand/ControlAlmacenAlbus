<?php
// Archivo en admin/funcionalidad_pedidos/
session_start();
include '../../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $empresa_cliente = trim($_POST['empresa_cliente']);
    $persona_contacto = trim($_POST['persona_contacto']);
    $estado = trim($_POST['estado']);
    
    // Validar que los campos no estén vacíos
    if (!empty($empresa_cliente) && !empty($persona_contacto) && !empty($estado)) {
        
        $sql = "UPDATE pedidos SET empresa_cliente = ?, persona_contacto = ?, estado = ?, updated_at = CURRENT_TIMESTAMP WHERE id_pedido = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssi", $empresa_cliente, $persona_contacto, $estado, $id_pedido);
            
            if ($stmt->execute()) {
                $stmt->close();
                $_SESSION['mensaje'] = "Pedido actualizado correctamente";
                header("Location: ../pedidos_admin.php?success=2");
                exit();
            } else {
                $stmt->close();
                $_SESSION['mensaje'] = "Error al actualizar el pedido: " . $conn->error;
                header("Location: ../pedidos_admin.php?error=1");
                exit();
            }
        } else {
            $_SESSION['mensaje'] = "Error en la consulta: " . $conn->error;
            header("Location: ../pedidos_admin.php?error=1");
            exit();
        }
    } else {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios";
        header("Location: ../pedidos_admin.php?error=2");
        exit();
    }
} else {
    header("Location: ../pedidos_admin.php");
    exit();
}

$conn->close();
?>