<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y validar datos
    $nombre = trim($_POST['nombre']);
    
    // Si se seleccionó "otros", usar el nombre personalizado
    if ($nombre === 'otros' && isset($_POST['nombre_personalizado'])) {
        $nombre = trim($_POST['nombre_personalizado']);
    }
    
    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);

    // Validaciones
    if (empty($nombre) || $stock < 0 || $stock_minimo <= 0) {
        header("Location: ../quimicos_admin.php?error=2");
        exit();
    }

    try {
        // Verificar si ya existe un producto químico con el mismo nombre
        $sql_verificar = "SELECT id_quimico FROM productos_quimicos WHERE nombre = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("s", $nombre);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows > 0) {
            header("Location: ../quimicos_admin.php?error=5");
            exit();
        }

        // Insertar nuevo producto químico
        $sql = "INSERT INTO productos_quimicos (nombre, stock, stock_minimo) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $nombre, $stock, $stock_minimo);

        if ($stmt->execute()) {
            // Registrar en logs
            $accion = "Registró nuevo producto químico: " . $nombre;
            $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Químicos')";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
            $stmt_log->execute();
            
            header("Location: ../quimicos_admin.php?success=1");
        } else {
            header("Location: ../quimicos_admin.php?error=1");
        }
    } catch (Exception $e) {
        error_log("Error al registrar producto químico: " . $e->getMessage());
        header("Location: ../quimicos_admin.php?error=1");
    }
} else {
    header("Location: ../quimicos_admin.php");
}
?>