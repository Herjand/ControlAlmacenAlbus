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

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Verificar que el pedido existe y está en preparación
        $sql_verificar = "SELECT estado FROM pedidos WHERE id_pedido = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("i", $id_pedido);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();

        if ($result_verificar->num_rows === 0) {
            throw new Exception("Pedido no encontrado");
        }

        $pedido = $result_verificar->fetch_assoc();
        
        if ($pedido['estado'] != 'En Preparación') {
            throw new Exception("El pedido no está en preparación");
        }

        // Verificar stock disponible para todos los productos del pedido
        $sql_detalles = "SELECT dp.id_producto, dp.cantidad, p.nombre, p.stock 
                        FROM detalle_pedidos dp 
                        JOIN productos p ON dp.id_producto = p.id_producto 
                        WHERE dp.id_pedido = ?";
        $stmt_detalles = $conn->prepare($sql_detalles);
        $stmt_detalles->bind_param("i", $id_pedido);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();

        $productos_sin_stock = [];
        while ($detalle = $result_detalles->fetch_assoc()) {
            if ($detalle['stock'] < $detalle['cantidad']) {
                $productos_sin_stock[] = $detalle['nombre'] . " (Stock: " . $detalle['stock'] . ", Necesario: " . $detalle['cantidad'] . ")";
            }
        }

        if (!empty($productos_sin_stock)) {
            throw new Exception("Stock insuficiente: " . implode(", ", $productos_sin_stock));
        }

        // Actualizar stock y registrar salidas
        $result_detalles->data_seek(0); // Reiniciar el puntero del resultado
        while ($detalle = $result_detalles->fetch_assoc()) {
            // Actualizar stock
            $sql_update_stock = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
            $stmt_update = $conn->prepare($sql_update_stock);
            $stmt_update->bind_param("ii", $detalle['cantidad'], $detalle['id_producto']);
            $stmt_update->execute();

            // Registrar salida
            $sql_salida = "INSERT INTO salidas (id_producto, cantidad, usuario_responsable, motivo, observaciones) 
                          VALUES (?, ?, ?, 'Pedido', 'Pedido #" . $id_pedido . "')";
            $stmt_salida = $conn->prepare($sql_salida);
            $stmt_salida->bind_param("iii", $detalle['id_producto'], $detalle['cantidad'], $usuario_id);
            $stmt_salida->execute();
        }

        // Actualizar estado del pedido a "Completado"
        $sql_actualizar = "UPDATE pedidos SET estado = 'Completado', updated_at = CURRENT_TIMESTAMP WHERE id_pedido = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->bind_param("i", $id_pedido);
        $stmt_actualizar->execute();

        // Registrar en logs
        $accion = "Pedido completado";
        $detalles = "ID Pedido: " . $id_pedido;
        
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo, detalles) VALUES (?, ?, 'Pedidos', ?)";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("iss", $usuario_id, $accion, $detalles);
        $stmt_log->execute();

        // Confirmar transacción
        $conn->commit();

        header("Location: ../preparar_pedidos.php?success=2");
        exit();

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        if (strpos($e->getMessage(), 'Stock insuficiente') !== false) {
            header("Location: ../preparar_pedidos.php?error=2&message=" . urlencode($e->getMessage()));
        } else {
            header("Location: ../preparar_pedidos.php?error=1");
        }
        exit();
    }
} else {
    header("Location: ../preparar_pedidos.php");
    exit();
}
?>