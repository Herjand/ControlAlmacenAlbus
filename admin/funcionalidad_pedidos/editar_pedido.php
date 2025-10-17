<?php
session_start();
require_once '../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pedidos_admin.php?error=1");
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'Administrador') {
    header("Location: ../../login.php");
    exit();
}

// Obtener datos del formulario
$id_pedido = intval($_POST['id_pedido'] ?? '');
$id_cliente = intval($_POST['id_cliente'] ?? '');
$empresa_cliente = trim($_POST['empresa_cliente'] ?? '');
$persona_contacto = trim($_POST['persona_contacto'] ?? '');
$fecha_entrega = trim($_POST['fecha_entrega'] ?? '');
$nota_remision = trim($_POST['nota_remision'] ?? '');
$lugar_entrega = trim($_POST['lugar_entrega'] ?? '');
$estado = trim($_POST['estado'] ?? '');

// Validaciones
if (empty($empresa_cliente) || empty($persona_contacto) || empty($fecha_entrega) || empty($estado)) {
    header("Location: ../pedidos_admin.php?error=2");
    exit();
}

try {
    // Obtener datos antiguos para el log
    $sql_old = "SELECT empresa_cliente, persona_contacto, fecha_entrega, nota_remision, lugar_entrega, estado FROM pedidos WHERE id_pedido = ?";
    $stmt_old = $conn->prepare($sql_old);
    $stmt_old->bind_param("i", $id_pedido);
    $stmt_old->execute();
    $old_data = $stmt_old->get_result()->fetch_assoc();

    // Actualizar pedido
    $sql = "UPDATE pedidos SET 
            id_cliente = ?, 
            empresa_cliente = ?, 
            persona_contacto = ?, 
            fecha_entrega = ?, 
            nota_remision = ?, 
            lugar_entrega = ?, 
            estado = ?, 
            updated_at = CURRENT_TIMESTAMP 
            WHERE id_pedido = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssi", $id_cliente, $empresa_cliente, $persona_contacto, $fecha_entrega, $nota_remision, $lugar_entrega, $estado, $id_pedido);
    
    if ($stmt->execute()) {
        // Registrar en logs
        $cambios = [];
        if ($old_data['empresa_cliente'] != $empresa_cliente) {
            $cambios[] = "Cliente: {$old_data['empresa_cliente']} → {$empresa_cliente}";
        }
        if ($old_data['estado'] != $estado) {
            $cambios[] = "Estado: {$old_data['estado']} → {$estado}";
        }
        if ($old_data['fecha_entrega'] != $fecha_entrega) {
            $cambios[] = "Fecha entrega: {$old_data['fecha_entrega']} → {$fecha_entrega}";
        }
        
        $accion = "Editó pedido #" . $id_pedido . " (" . implode(", ", $cambios) . ")";
        $sql_log = "INSERT INTO logs (id_usuario, accion, modulo) VALUES (?, ?, 'Pedidos')";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param("is", $_SESSION['usuario_id'], $accion);
        $stmt_log->execute();
        
        header("Location: ../pedidos_admin.php?success=2");
    } else {
        throw new Exception("Error al ejecutar consulta");
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al editar pedido: " . $e->getMessage());
    header("Location: ../pedidos_admin.php?error=1");
}

$conn->close();
exit();
?>