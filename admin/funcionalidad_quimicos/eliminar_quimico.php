<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

if (isset($_GET['id'])) {
    $id_quimico = intval($_GET['id']);

    if ($id_quimico <= 0) {
        header("Location: ../quimicos_admin.php?error=3");
        exit();
    }

    try {
        // Obtener información del producto para el log
        $sql_info = "SELECT nombre FROM productos_quimicos WHERE id_quimico = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $id_quimico);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        
        if ($result_info->num_rows === 0) {
            header("Location: ../quimicos_admin.php?error=3");
            exit();
        }
        
        $quimico = $result_info->fetch_assoc();
        $nombre_quimico = $quimico['nombre'];

        // Eliminar producto químico
        $sql = "DELETE FROM productos_quimicos WHERE id_quimico = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_quimico);

        if ($stmt->execute()) {
            // Registrar en logs
            $accion = "Eliminó producto químico: " . $nombre_quimico;
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Químicos')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
            $stmt_log->execute();
            
            header("Location: ../quimicos_admin.php?success=3");
        } else {
            header("Location: ../quimicos_admin.php?error=3");
        }
    } catch (Exception $e) {
        error_log("Error al eliminar producto químico: " . $e->getMessage());
        header("Location: ../quimicos_admin.php?error=3");
    }
} else {
    header("Location: ../quimicos_admin.php");
}
?>