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
$empresa_cliente = trim($_POST['empresa_cliente'] ?? '');
$persona_contacto = trim($_POST['persona_contacto'] ?? '');
$fecha_entrega = $_POST['fecha_entrega'] ?? '';
$productos = $_POST['productos'] ?? [];

// Validaciones básicas
if (empty($empresa_cliente) || empty($persona_contacto) || empty($fecha_entrega)) {
    header("Location: ../pedidos_admin.php?error=2");
    exit();
}

if (empty($productos)) {
    header("Location: ../pedidos_admin.php?error=2&message=Debe agregar al menos un producto");
    exit();
}

try {
    // Iniciar transacción
    $conn->begin_transaction();

    // 1. Insertar el pedido
    $sql_pedido = "INSERT INTO pedidos (empresa_cliente, persona_contacto, fecha_entrega) VALUES (?, ?, ?)";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("sss", $empresa_cliente, $persona_contacto, $fecha_entrega);
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al registrar pedido");
    }
    
    $id_pedido = $stmt_pedido->insert_id;

    // 2. Insertar los productos del pedido (SIN validar stock)
    $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad) VALUES (?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);
    
    foreach ($productos as $producto) {
        $id_producto = $producto['id_producto'];
        $cantidad = intval($producto['cantidad']);
        
        $stmt_detalle->bind_param("iii", $id_pedido, $id_producto, $cantidad);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al registrar producto del pedido");
        }
    }

    // Confirmar transacción
    $conn->commit();
    header("Location: ../pedidos_admin.php?success=1");

} catch (Exception $e) {
    // Revertir transacción
    $conn->rollback();
    header("Location: ../pedidos_admin.php?error=1");
}

$stmt_pedido->close();
$stmt_detalle->close();
$conn->close();
exit();
?>