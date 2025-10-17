<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y validar datos
    $id_quimico = intval($_POST['id_quimico']);
    $nombre = trim($_POST['nombre']);
    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);

    // Validaciones
    if (empty($nombre) || $stock < 0 || $stock_minimo <= 0 || $id_quimico <= 0) {
        header("Location: ../quimicos_admin.php?error=2");
        exit();
    }

    try {
        // Verificar si ya existe otro producto químico con el mismo nombre
        $sql_verificar = "SELECT id_quimico FROM productos_quimicos WHERE nombre = ? AND id_quimico != ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("si", $nombre, $id_quimico);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows > 0) {
            header("Location: ../quimicos_admin.php?error=5");
            exit();
        }

        // Obtener datos antiguos para el log
        $sql_old = "SELECT nombre, stock, stock_minimo FROM productos_quimicos WHERE id_quimico = ?";
        $stmt_old = $conn->prepare($sql_old);
        $stmt_old->bind_param("i", $id_quimico);
        $stmt_old->execute();
        $old_data = $stmt_old->get_result()->fetch_assoc();

        // Actualizar producto químico
        $sql = "UPDATE productos_quimicos SET nombre = ?, stock = ?, stock_minimo = ?, updated_at = CURRENT_TIMESTAMP WHERE id_quimico = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $nombre, $stock, $stock_minimo, $id_quimico);

        if ($stmt->execute()) {
            // Registrar en logs
            $cambios = [];
            if ($old_data['nombre'] != $nombre) {
                $cambios[] = "Nombre: {$old_data['nombre']} → {$nombre}";
            }
            if ($old_data['stock'] != $stock) {
                $cambios[] = "Stock: {$old_data['stock']} unidades → {$stock} unidades";
            }
            if ($old_data['stock_minimo'] != $stock_minimo) {
                $cambios[] = "Stock mínimo: {$old_data['stock_minimo']} unidades → {$stock_minimo} unidades";
            }
            
            $accion = "Editó producto químico: " . $nombre . " (" . implode(", ", $cambios) . ")";
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Químicos')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
            $stmt_log->execute();
            
            header("Location: ../quimicos_admin.php?success=2");
        } else {
            header("Location: ../quimicos_admin.php?error=1");
        }
    } catch (Exception $e) {
        error_log("Error al editar producto químico: " . $e->getMessage());
        header("Location: ../quimicos_admin.php?error=1");
    }
} else {
    header("Location: ../quimicos_admin.php");
}
?>