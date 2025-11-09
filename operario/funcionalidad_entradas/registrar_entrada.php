<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Operario') {
    header("Location: ../../login.php");
    exit();
}

include '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];
    $motivo = $_POST['motivo'];
    $observaciones = $_POST['observaciones'];
    $usuario_responsable = $_SESSION['usuario_id'];

    // Validar campos obligatorios
    if (empty($id_producto) || empty($cantidad) || empty($motivo)) {
        header("Location: ../entradas_operario.php?error=2");
        exit();
    }

    // Verificar que el producto existe
    $sql_producto = "SELECT nombre FROM productos WHERE id_producto = ?";
    $stmt_producto = $conn->prepare($sql_producto);
    $stmt_producto->bind_param("i", $id_producto);
    $stmt_producto->execute();
    $result_producto = $stmt_producto->get_result();
    
    if ($result_producto->num_rows === 0) {
        header("Location: ../entradas_operario.php?error=1");
        exit();
    }

    $producto = $result_producto->fetch_assoc();

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // 1. Registrar la entrada
        $sql_entrada = "INSERT INTO entradas (id_producto, cantidad, usuario_responsable, motivo, observaciones) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_entrada = $conn->prepare($sql_entrada);
        $stmt_entrada->bind_param("iiiss", $id_producto, $cantidad, $usuario_responsable, $motivo, $observaciones);
        $stmt_entrada->execute();

        // 2. Actualizar stock del producto
        $sql_update_stock = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
        $stmt_update = $conn->prepare($sql_update_stock);
        $stmt_update->bind_param("ii", $cantidad, $id_producto);
        $stmt_update->execute();

        // 3. Registrar en logs
        $accion = "Entrada de producto: " . $producto['nombre'];
        $detalles = "Cantidad: " . $cantidad . ", Motivo: " . $motivo . ", Observaciones: " . $observaciones;
        
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo, detalles) VALUES (?, ?, 'Entradas', ?)";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("iss", $usuario_responsable, $accion, $detalles);
        $stmt_log->execute();

        // Confirmar transacción
        $conn->commit();

        header("Location: ../entradas_operario.php?success=1");
        exit();

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        error_log("Error al registrar entrada: " . $e->getMessage());
        header("Location: ../entradas_operario.php?error=1");
        exit();
    }

} else {
    header("Location: ../entradas_operario.php");
    exit();
}
?>