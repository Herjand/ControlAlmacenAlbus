<?php
session_start();
include '../../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);
    $motivo = trim($_POST['motivo']);
    $observaciones = trim($_POST['observaciones']);
    $usuario_responsable = $_SESSION['usuario_id'];
    
    // Validar campos
    if ($id_producto > 0 && $cantidad > 0 && !empty($motivo)) {
        
        // Verificar stock disponible
        $sql_check_stock = "SELECT stock, nombre FROM productos WHERE id_producto = ?";
        $stmt_check = $conn->prepare($sql_check_stock);
        $stmt_check->bind_param("i", $id_producto);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $producto = $result_check->fetch_assoc();
        $stmt_check->close();
        
        if ($producto['stock'] >= $cantidad) {
            // Stock suficiente, proceder con la salida
            
            // Iniciar transacción
            $conn->begin_transaction();
            
            try {
                // 1. Insertar registro en salidas
                $sql_salida = "INSERT INTO salidas (id_producto, cantidad, usuario_responsable, motivo, observaciones) 
                              VALUES (?, ?, ?, ?, ?)";
                $stmt_salida = $conn->prepare($sql_salida);
                $stmt_salida->bind_param("iiiss", $id_producto, $cantidad, $usuario_responsable, $motivo, $observaciones);
                $stmt_salida->execute();
                $stmt_salida->close();
                
                // 2. Actualizar stock del producto (RESTAR)
                $sql_stock = "UPDATE productos SET stock = stock - ?, updated_at = CURRENT_TIMESTAMP 
                             WHERE id_producto = ?";
                $stmt_stock = $conn->prepare($sql_stock);
                $stmt_stock->bind_param("ii", $cantidad, $id_producto);
                $stmt_stock->execute();
                $stmt_stock->close();
                
                // 3. Registrar en logs
                $sql_log = "INSERT INTO logs (id_usuario, accion, modulo, detalles) 
                           VALUES (?, ?, 'Salidas', ?)";
                $stmt_log = $conn->prepare($sql_log);
                
                $accion_detalles = "Salida: " . $producto['nombre'] . " - Cantidad: -" . $cantidad . " - Motivo: " . $motivo;
                $stmt_log->bind_param("iss", $usuario_responsable, $accion_detalles, $accion_detalles);
                $stmt_log->execute();
                $stmt_log->close();
                
                // Confirmar transacción
                $conn->commit();
                
                header("Location: ../salidas_admin.php?success=1");
                exit();
                
            } catch (Exception $e) {
                // Revertir en caso de error
                $conn->rollback();
                header("Location: ../salidas_admin.php?error=1");
                exit();
            }
            
        } else {
            // Stock insuficiente
            header("Location: ../salidas_admin.php?error=3");
            exit();
        }
        
    } else {
        header("Location: ../salidas_admin.php?error=2");
        exit();
    }
} else {
    header("Location: ../salidas_admin.php");
    exit();
}

$conn->close();
?>