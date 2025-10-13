<?php
session_start();
include '../../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['id_producto'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $categoria = trim($_POST['categoria']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $ancho = floatval($_POST['ancho']);
    $largo = floatval($_POST['largo']);
    $tipo_especifico = trim($_POST['tipo_especifico']);
    $presentacion = trim($_POST['presentacion']);
    $cantidad_unidad = floatval($_POST['cantidad_unidad']);
    
    // Validar campos obligatorios
    if (!empty($nombre) && !empty($categoria) && !empty($unidad_medida)) {
        
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoria = ?, 
                unidad_medida = ?, stock = ?, stock_minimo = ?, ancho = ?, largo = ?, 
                tipo_especifico = ?, presentacion = ?, cantidad_unidad = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id_producto = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssiiddssdi", $nombre, $descripcion, $categoria, $unidad_medida, $stock, $stock_minimo, $ancho, $largo, $tipo_especifico, $presentacion, $cantidad_unidad, $id_producto);
            
            if ($stmt->execute()) {
                // Verificar si el usuario existe antes de insertar en logs
                $id_usuario = $_SESSION['usuario_id'];
                $sql_verificar_usuario = "SELECT id_usuario FROM usuarios WHERE id_usuario = ?";
                $stmt_verificar = $conn->prepare($sql_verificar_usuario);
                $stmt_verificar->bind_param("i", $id_usuario);
                $stmt_verificar->execute();
                $result_verificar = $stmt_verificar->get_result();
                
                if ($result_verificar->num_rows > 0) {
                    // El usuario existe, podemos insertar en logs
                    $accion = "Actualizó producto: " . $nombre;
                    $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Productos')";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->bind_param("is", $id_usuario, $accion);
                    $stmt_log->execute();
                    $stmt_log->close();
                }
                $stmt_verificar->close();
                
                $stmt->close();
                header("Location: ../productos_admin.php?success=2");
                exit();
            } else {
                $stmt->close();
                header("Location: ../productos_admin.php?error=1");
                exit();
            }
        } else {
            header("Location: ../productos_admin.php?error=1");
            exit();
        }
    } else {
        header("Location: ../productos_admin.php?error=2");
        exit();
    }
} else {
    header("Location: ../productos_admin.php");
    exit();
}

$conn->close();
?>