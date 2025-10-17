<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

if (isset($_GET['id'])) {
    $id_envase = intval($_GET['id']);

    if ($id_envase <= 0) {
        header("Location: ../envases_admin.php?error=3");
        exit();
    }

    try {
        // Obtener información del envase para el log
        $sql_info = "SELECT nombre FROM envases WHERE id_envase = ?";
        $stmt_info = $conn->prepare($sql_info);
        $stmt_info->bind_param("i", $id_envase);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        
        if ($result_info->num_rows === 0) {
            header("Location: ../envases_admin.php?error=3");
            exit();
        }
        
        $envase = $result_info->fetch_assoc();
        $nombre_envase = $envase['nombre'];

        // Eliminar envase
        $sql = "DELETE FROM envases WHERE id_envase = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_envase);

        if ($stmt->execute()) {
            // Registrar en logs
            $accion = "Eliminó envase: " . $nombre_envase;
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Envases')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
            $stmt_log->execute();
            
            header("Location: ../envases_admin.php?success=3");
        } else {
            header("Location: ../envases_admin.php?error=3");
        }
    } catch (Exception $e) {
        error_log("Error al eliminar envase: " . $e->getMessage());
        header("Location: ../envases_admin.php?error=3");
    }
} else {
    header("Location: ../envases_admin.php");
}
?>